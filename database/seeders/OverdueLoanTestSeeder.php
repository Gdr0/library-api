<?php

namespace Database\Seeders;

use App\Models\BookLoan;
use App\Models\BookLoanReturn;
use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanFine;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OverdueLoanTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $documentNumbers = [
                'CRONTEST-001',
                'CRONTEST-002',
                'CRONTEST-003',
            ];

            $loanIds = Loan::withTrashed()
                ->whereIn('document_number', $documentNumbers)
                ->pluck('id');

            if ($loanIds->isNotEmpty()) {
                $bookLoanIds = BookLoan::withTrashed()
                    ->whereIn('loan_id', $loanIds)
                    ->pluck('id');

                if ($bookLoanIds->isNotEmpty()) {
                    BookLoanReturn::whereIn('book_loan_id', $bookLoanIds)->delete();
                }

                LoanFine::withTrashed()->whereIn('loan_id', $loanIds)->forceDelete();
                BookLoan::withTrashed()->whereIn('loan_id', $loanIds)->forceDelete();
                Loan::withTrashed()->whereIn('id', $loanIds)->forceDelete();
            }

            $clients = [
                Client::updateOrCreate(
                    ['email' => 'scheduler.test1@example.com'],
                    [
                        'name' => 'Scheduler',
                        'last_name' => 'Test Uno',
                        'phone_number' => '3339900001',
                    ],
                ),
                Client::updateOrCreate(
                    ['email' => 'scheduler.test2@example.com'],
                    [
                        'name' => 'Scheduler',
                        'last_name' => 'Test Due',
                        'phone_number' => '3339900002',
                    ],
                ),
                Client::updateOrCreate(
                    ['email' => 'scheduler.test3@example.com'],
                    [
                        'name' => 'Scheduler',
                        'last_name' => 'Test Tre',
                        'phone_number' => '3339900003',
                    ],
                ),
            ];

            $loans = [
                [
                    'client_id' => $clients[0]->id,
                    'document_type_id' => 1,
                    'document_number' => 'CRONTEST-001',
                    'status_id' => 1,
                    'started_at' => '2026-06-10',
                    'expiring_at' => '2026-06-20',
                    'book_loans' => [
                        ['book_id' => 1, 'unit_price' => 0.50, 'quantity' => 2],
                    ],
                ],
                [
                    'client_id' => $clients[1]->id,
                    'document_type_id' => 2,
                    'document_number' => 'CRONTEST-002',
                    'status_id' => 1,
                    'started_at' => '2026-06-08',
                    'expiring_at' => '2026-06-18',
                    'book_loans' => [
                        [
                            'book_id' => 7,
                            'unit_price' => 0.50,
                            'quantity' => 3,
                            'returns' => [
                                [
                                    'returned_quantity' => 1,
                                    'returned_at' => '2026-06-21',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'client_id' => $clients[2]->id,
                    'document_type_id' => 3,
                    'document_number' => 'CRONTEST-003',
                    'status_id' => 1,
                    'started_at' => '2026-06-01',
                    'expiring_at' => '2026-06-15',
                    'book_loans' => [
                        ['book_id' => 9, 'unit_price' => 0.50, 'quantity' => 2],
                        ['book_id' => 10, 'unit_price' => 0.50, 'quantity' => 1],
                    ],
                ],
            ];

            foreach ($loans as $loanData) {
                $loan = Loan::create([
                    'client_id' => $loanData['client_id'],
                    'document_type_id' => $loanData['document_type_id'],
                    'document_number' => $loanData['document_number'],
                    'status_id' => $loanData['status_id'],
                    'started_at' => $loanData['started_at'],
                    'expiring_at' => $loanData['expiring_at'],
                    'closed_at' => null,
                    'final_price' => 0,
                ]);

                foreach ($loanData['book_loans'] as $bookLoanData) {
                    $bookLoan = BookLoan::create([
                        'loan_id' => $loan->id,
                        'book_id' => $bookLoanData['book_id'],
                        'unit_price' => $bookLoanData['unit_price'],
                        'quantity' => $bookLoanData['quantity'],
                    ]);

                    foreach ($bookLoanData['returns'] ?? [] as $returnData) {
                        $loanDays = Carbon::parse($loan->started_at)
                            ->diffInDays(Carbon::parse($returnData['returned_at']));

                        if ($loanDays < 1) {
                            $loanDays = 1;
                        }

                        BookLoanReturn::create([
                            'book_loan_id' => $bookLoan->id,
                            'returned_quantity' => $returnData['returned_quantity'],
                            'returned_at' => $returnData['returned_at'],
                            'total_at_return' => $bookLoan->unit_price * $returnData['returned_quantity'] * $loanDays,
                        ]);
                    }
                }
            }
        });
    }
}
