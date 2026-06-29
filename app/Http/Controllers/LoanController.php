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
    public function index() {
        $loans = Loan::with([
            'client',
            'status',
            'documentType',
            'bookLoans.book.authors',
            'bookLoans.returns',
            'fine',
        ])->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'loans' => $loans,
        ], 200);
    }

    // dettaglio singolo prestito
    public function show($id) {
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

    public function store(Request $request) {
        $clientId = $request->integer('client_id');
        $startedAt = Carbon::today()->toDateString();

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
            'expiring_at' => 'required | date | after:today',
            // libri del prestito
            'books' => 'required | array | min:1',
            'books.*.book_id' => 'required | integer | distinct | exists:books,id',
            'books.*.quantity' => 'required | integer | min:1',
        ]);

        // tutto questo deve andare insieme, se salta un pezzo non devo ritrovarmi un prestito mezzo salvato
        $loan = DB::transaction(function () use ($startedAt, $validated) {
            // aggiorno/creo cliente
            $clientId = $this->loanService->resolveClientId($validated);

            $loan = Loan::create([
                'client_id' => $clientId,
                'document_type_id' => $validated['document_type_id'],
                'document_number' => $validated['document_number'],
                'started_at' => $startedAt,
                'expiring_at' => $validated['expiring_at'],
                'closed_at' => null,
            ]);

            // Ogni record in book_loans rappresenta un libro incluso nel prestito, con quantità e prezzo storico registrati al momento della creazione
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

            'books' => 'required|array|min:1',
            'books.*.book_id' => 'required|integer|distinct|exists:books,id',
            'books.*.returned_quantity' => 'required|integer|min:1',
        ]);

        // transazione necessatia per coerenza, non può essere parziale il salvataggio
        $loan = DB::transaction(function () use ($validated) {
            $loan = Loan::with(['bookLoans.returns', 'fine'])->lockForUpdate()->findOrFail($validated['id_loan']);

            $returnedAt = Carbon::today();
            $lastReturn = null;

            // La restituzione viene registrata sul record book_loan specifico, perché lo stesso libro può comparire in prestiti diversi
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

                // Il totale maturato alla restituzione dipende dai giorni trascorsi dall'inizio del prestito, con un minimo di 1 giorno.
                $loanDays = Carbon::parse($loan->started_at)->diffInDays($returnedAt);

                if ($loanDays < 1) {
                    $loanDays = 1;
                }

                $totalAtReturn = $bookLoan->unit_price * $returnedBook['returned_quantity'] * $loanDays;

                $lastReturn = BookLoanReturn::create([
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
        // vedo se ci sono more
        $fineAmount = $loan->fine?->amount ?? 0;

        // Ad ogni rientro salviamo subito il costo maturato di quel libro in total_at_return
        // La mora del prestito, invece, resta separata in loan_fines e viene sommata solo all'ultima restituzione, quando il backend consolida il totale finale. Nel frontend
        // mostriamo comunque totale dovuto a oggi calcolato live
        if ($allReturned && $fineAmount > 0 && $lastReturn) {
            $lastReturn->update([
                'total_at_return' => $lastReturn->total_at_return + $fineAmount,
            ]);

            $loan->load('bookLoans.returns');
        }

        // calcola il totale incassato, comprensivo della mora in caso di chiusura
        $totalAtReturn = $this->loanService->totalAtReturn($loan);
        // calcolo lo stato
        $statusId = $this->loanService->loanStatusId($loan, $returnedAt, $allReturned);

        $loan->update([
            'status_id' => $statusId,
            'closed_at' => $allReturned ? $returnedAt : null,
            'final_price' => $allReturned ? $totalAtReturn : 0,
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
