<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Loan;
use Carbon\Carbon;

class LoanService
{
    public function __construct(
        private ClientService $clientService,
    ) {}

    public function resolveClientId($validated): int {
        return $this->clientService->createOrUpdate(
            $validated['client'],
            $validated['client_id'] ?? null,
        )->id;
    }

    // La disponibilità viene calcolata sottraendo dalle copie totali solo quelle ancora non restituite.
    public function booksAvailability(Book $book) {
        $bookStillOut = $book->bookLoans()
            ->withSum('returns', 'returned_quantity')
            ->get();
        // numero copie ancora fuori
        $loanedQuantity = $bookStillOut->sum(function (BookLoan $item) {
            $returnedQuantity = $item->returns_sum_returned_quantity ?? 0;

            return max(0, $item->quantity - $returnedQuantity);
        });
        // ritorno un valore sempre positivo
        return max(0, $book->total_quantity - $loanedQuantity);
    }

    // per vedere se sono tutti restituiti
    public function allReturned(Loan $loan): bool {

        foreach ($loan->bookLoans as $bookLoan) {
            $returnedQuantity = $bookLoan->returns->sum('returned_quantity');

            if ($returnedQuantity < $bookLoan->quantity) {
                return false;
            }
        }

        return true;
    }

    // totale alla restituzione
    public function totalAtReturn(Loan $loan): float {

        $totalAtReturn = 0;

        foreach ($loan->bookLoans as $bookLoan) {
            $totalAtReturn += $bookLoan->returns->sum('total_at_return');
        }

        return $totalAtReturn;
    }

    // lo stato finale dipende da scadenza e da quanto è stato restituito
    public function loanStatusId(Loan $loan, Carbon $returnedAt, bool $allReturned): int {

        if ($allReturned && $returnedAt->gt(Carbon::parse($loan->expiring_at))) {
            return 4; // closed_late
        }

        if ($allReturned && $returnedAt->lte(Carbon::parse($loan->expiring_at))) {
            return 2; // closed
        }

        if ($returnedAt->gt(Carbon::parse($loan->expiring_at))) {
            return 3; // overdue
        }

        return 1; // open
    }
}
