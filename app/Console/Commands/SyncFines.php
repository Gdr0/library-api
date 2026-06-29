<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Loan;
use App\Models\LoanFine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class SyncFines extends Command
{
    protected $signature = 'loans:sync-fines';
    protected $description = 'Ricalcola le more giornaliere per i prestiti scaduti e non ancora chiusi';

    public function handle() {

        // Il comando ricalcola la mora solo per i prestiti scaduti che non risultano ancora chiusi
        $today = Carbon::today();
       // whereNull('closed_at') sarebbe stato abbastanza senza gli stati, ma più sicurezza
        $expiredLoans = Loan::with('bookLoans.returns')->whereIn('status_id', [1, 3])->whereNull('closed_at')->whereDate('expiring_at', '<', $today)->get();
        foreach($expiredLoans as $loan) {

            $daysOverdue = Carbon::parse($loan->expiring_at)->diffInDays($today);
            $dailyFine = 0;
            $bookLoans = $loan->bookLoans;

            foreach($bookLoans as $bookLoan){

                $returnedQty = $bookLoan->returns->sum('returned_quantity');
                $toReturn = max(0, $bookLoan->quantity - $returnedQty);
                $dailyFine +=  $toReturn * ($bookLoan->unit_price * 2);
            }
                $totalFine = round($daysOverdue * $dailyFine, 2);
                    LoanFine::updateOrCreate(
                        ['loan_id' => $loan->id],
                        ['amount' => $totalFine],
                    );
                    $this->line("Prestito {$loan->id} aggiornato con mora: {$totalFine}");
                    if ($loan->status_id !== 3) {
                        $loan->update([
                            'status_id' => 3,
                        ]);
                    }
            }
            return self::SUCCESS;
    }
}
