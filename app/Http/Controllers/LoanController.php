<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    private const DAILY_BOOK_PRICE = 3;

    public function CreateLoan(Request $request) {
        $validated = $request->validate([
            'status_id' => 'required | integer | exists:loan_statuses,id',
            'client_id' => 'required | integer | exists:clients,id',
            'document_type_id' => 'required | integer | exists:document_types,id',
            'document_number' => 'required | string | max:255',
            'started_at' => 'required | date',
            'expiring_at' => 'required | date | after:started_at',

            // libri
            'books' => 'required | array | min:1',
            'books.*.book_id' => 'required | integer | distinct | exists:books,id',
            'books.*.quantity' => 'required | integer | min:1',
        ]);

        // transazione per evitare danni se qualcosa va storto
        $loan = DB::transaction(function () use ($validated) {
            // calcolo giorni del prestito
            $loanDays = Carbon::parse($validated['started_at'])->diffInDays(Carbon::parse($validated['expiring_at']));

            $basePrice = 0;
            // definisco variabile prezzo base

            $loan = Loan::create([
                'status_id' => $validated['status_id'],
                'client_id' => $validated['client_id'],
                'document_type_id' => $validated['document_type_id'],
                'document_number' => $validated['document_number'],
                'started_at' => $validated['started_at'],
                'expiring_at' => $validated['expiring_at'],
                'returned_at' => null,
                'base_price' => 0,
                'total_price' => 0,
            ]);

            foreach ($validated['books'] as $bookData) {
                $book = Book::findOrFail($bookData['book_id']);
                // disponibiltà libri
                $availableQuantity = $this->booksAvailability($book);

                if ($bookData['quantity'] > $availableQuantity) {
                    throw ValidationException::withMessages([
                        'books' => 'Non ci sono abbastanza copie disponibili del libro '.$book->title,
                    ]);
                }

                BookLoan::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book->id,
                    'unit_price' => self::DAILY_BOOK_PRICE,
                    'quantity' => $bookData['quantity'],
                ]);

                $bookPrice = self::DAILY_BOOK_PRICE * $loanDays;
                $booksPrice = $bookPrice * $bookData['quantity'];

                $basePrice = $basePrice + $booksPrice;
            }

            $loan->update([
                'base_price' => $basePrice,
                'total_price' => $basePrice,
            ]);

            return $loan->load(['client','status','documentType','books']);
        });

        return response()->json([
            'loan' => $loan,
            'message' => 'Loan created successfully',
        ], 201);
    }

    // funzione per calcolare le copie disponibili 
    private function booksAvailability(Book $book) {
        $loanedQuantity = BookLoan::where('book_id', $book->id)->whereHas('loan', function ($query) {
                $query->whereNull('returned_at');
            })->sum('quantity');

        return max(0, $book->total_quantity - $loanedQuantity);
    }

    public function returnLoan(Request $request ) {
        $validated = $request->validate([
            'id_loan' => 'required | integer | exists:loans,id',
            'returned_at' => 'required | date | before_or_equal:today',
        ]);

        $loan = Loan::findOrFail($validated['id_loan']);

        $loan->update(["returned_at" => $validated['returned_at']]);

        return response()->json([
            'loan' => $loan,
            'message' => 'Prestito restituito correttamente'
            ], 201);
    }
}
