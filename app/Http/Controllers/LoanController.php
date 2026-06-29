<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\BookLoanReturn;
use App\Models\Loan;
use App\Services\LoanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{

    public function __construct(
        private LoanService $loanService,
    ) {}

    // elenco prestiti
    public function loanIndex() {
        $loans = Loan::with([
            'client',
            'status',
            'documentType',
            'bookLoans.book.authors',
            'bookLoans.returns',
        ])->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'loans' => $loans,
        ], 200);
    }

    // dettaglio singolo prestito
    public function loanDetail($id) {
        $loan = Loan::with([
            'client',
            'status',
            'documentType',
            'bookLoans.book.authors',
            'bookLoans.returns',
            'fine',
        ])->findOrFail($id);

        return response()->json([
            'loan' => $loan,
        ]);
    }

    public function CreateLoan(Request $request) {
        $clientId = $request->integer('client_id');

        $validated = $request->validate([
            // clienti
            'client_id' => 'nullable | integer | exists:clients,id',
            'client' => 'required | array',
            'client.name' => 'required | string | max:25',
            'client.last_name' => 'required | string | max:25',
            'client.phone_number' => 'required | string | max:25 | unique:clients,phone_number,'.($clientId ?: 'NULL').',id',
            'client.email' => 'required | string | email | max:255 | unique:clients,email,'.($clientId ?: 'NULL').',id',

            // prestito
            'document_type_id' => 'required | integer | exists:document_types,id',
            'document_number' => 'required | string | max:255',
            'started_at' => 'required | date',
            'expiring_at' => 'required | date | after:started_at',
            // libri del prestito
            'books' => 'required | array | min:1',
            'books.*.book_id' => 'required | integer | distinct | exists:books,id',
            'books.*.quantity' => 'required | integer | min:1',
        ]);

        // transazione per evitare danni se qualcosa va storto
        $loan = DB::transaction(function () use ($validated) {
            // aggiorno/creo cliente
            $clientId = $this->loanService->resolveClientId($validated);

            // creo il prestito
            $loan = Loan::create([
                'client_id' => $clientId,
                'document_type_id' => $validated['document_type_id'],
                'document_number' => $validated['document_number'],
                'started_at' => $validated['started_at'],
                'expiring_at' => $validated['expiring_at'],
                'closed_at' => null,
            ]);
            // ciclo i libri per vedere disponibilità e creare il record del singolo libro sul prestito, con il prezzo storico del giorno in cui è stato effetuato il prestito
            foreach ($validated['books'] as $bookData) {
                $book = Book::findOrFail($bookData['book_id']);
                // disponibiltà libri
                $availableQuantity = $this->loanService->booksAvailability($book);

                if ($bookData['quantity'] > $availableQuantity) {
                    throw ValidationException::withMessages([
                        'books' => 'Non ci sono abbastanza copie disponibili del libro '.$book->title,
                    ]);
                }

                BookLoan::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book->id,
                    'unit_price' => $book->daily_price,
                    'quantity' => $bookData['quantity'],
                ]);
            }
            // esce $loan completo dalla closure
            return $loan->load(['client', 'status', 'documentType', 'bookLoans.book.authors', 'bookLoans.returns']);
        });

        return response()->json([
            'loan' => $loan,
            'message' => 'Loan created successfully',
        ], 201);
    }

    public function returnBookOrLoan(Request $request){

        $validated = $request->validate([
            'id_loan' => 'required|integer|exists:loans,id',
            'returned_at' => 'required|date|before_or_equal:today',

            'books' => 'required|array|min:1',
            'books.*.book_id' => 'required|integer|distinct|exists:books,id',
            'books.*.returned_quantity' => 'required|integer|min:1',
        ]);

        $loan = DB::transaction(function () use ($validated) {
            $loan = Loan::with(['bookLoans.returns', 'fine'])->lockForUpdate()->findOrFail($validated['id_loan']);

            $returnedAt = Carbon::parse($validated['returned_at']);

            foreach ($validated['books'] as $returnedBook) {
                $bookLoan = BookLoan::with('returns')->lockForUpdate()->where('loan_id', $loan->id)->where('book_id', $returnedBook['book_id'])->first();

                if (! $bookLoan) {
                    throw ValidationException::withMessages([
                        'books' => 'Questo libro non appartiene al prestito selezionato.',
                    ]);
                }

                $alreadyReturned = $bookLoan->returns->sum('returned_quantity');
                $remaningQty = $bookLoan->quantity - $alreadyReturned;

                if ($returnedBook['returned_quantity'] > $remaningQty) {
                    throw ValidationException::withMessages([
                        'books' => 'Più libri di quelli da consegnare',
                    ]);
                }

                $loanDays = Carbon::parse($loan->started_at)->diffInDays($returnedAt);

                if ($loanDays < 1) {
                    $loanDays = 1;
                }

                $totalAtReturn = $bookLoan->unit_price * $returnedBook['returned_quantity'] * $loanDays;

                BookLoanReturn::create([
                    'book_loan_id' => $bookLoan->id,
                    'returned_quantity' => $returnedBook['returned_quantity'],
                    'returned_at' => $returnedAt,
                    'total_at_return' => $totalAtReturn,
                ]);
            }

        //  ricarico il prestito e controllo se è stato tutto riconsegnato
        $loan->load(['bookLoans.returns', 'fine']);

        // controllo se sono stati restituiti tutti i libri (ritorna booleano)
        $allReturned = $this->loanService->allReturned($loan);
        // calcola il consto totale alla restituzione per ogni libro e mi restituisce la somma
        $totalAtReturn = $this->loanService->totalAtReturn($loan);
        // vedo se ci sono more
        $fineAmount = $loan->fine?->amount ?? 0;
        // calcolo lo stato
        $statusId = $this->loanService->loanStatusId($loan, $returnedAt, $allReturned);

        $loan->update([
            'status_id' => $statusId,
            'closed_at' => $allReturned ? $returnedAt : null,
            'final_price' => $allReturned ? $totalAtReturn + $fineAmount : 0,
        ]);

        return $loan->fresh([
            'client',
            'status',
            'documentType',
            'bookLoans.book.authors',
            'bookLoans.returns',
            'fine',
        ]);
    });

        return response()->json([
            'loan' => $loan,
            'message' => 'Prestito restituito correttamente',
        ], 200);
    }

}
