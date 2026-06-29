<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function bookIndex() {
        $books = Book::with(['authors', 'editor', 'bookLoans.returns'])->paginate(10);

        return response()->json([
            'books' => $books,
        ]);
    }

    public function createOrUpdateBooks(Request $request) {
        $bookId = $request->integer('id') ?: null;
        $uniqueIsbn = 'unique:books,isbn'.($bookId ? ','.$bookId : '');

        $validated = $request->validate([
            'id' => 'nullable | integer | exists:books,id',
            'editor_id' => 'required | integer | exists:editors,id',
            'title' => 'required | string | max:255',
            'isbn' => 'required | string | max:255 | '.$uniqueIsbn,
            'synopsis' => 'required | string | max:255',
            'daily_price' => 'required | numeric | min:0',
            'total_quantity' => 'required | integer | min:0',
            'authors' => 'required | array | min:1',
            'authors.*' => 'required | integer | distinct | exists:authors,id',
        ]);

        $authors = $validated['authors'];
        unset($validated['authors'], $validated['id']);

        if ($bookId !== null) {
            $book = Book::findOrFail($bookId);
            $book->update($validated);
            $book->authors()->sync($authors);

            return response()->json([
                'book' => $book->load(['authors', 'editor', 'bookLoans.returns']),
                'message' => 'Book updated successfully',
            ]);
        }

        $book = Book::create($validated);
        $book->authors()->sync($authors);

        return response()->json([
            'book' => $book->load(['authors', 'editor', 'bookLoans.returns']),
            'message' => 'Book created successfully',
        ], 201);
    }

    public function softDelete(int $id) {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json([
            'book' => $book,
            'message' => 'Book deleted successfully',
        ]);
    }

    public function getBookById($id) {
        $book = Book::with(['authors', 'editor', 'bookLoans.returns'])->findOrFail($id);

        return response()->json([
            'book' => $book,
        ]);
    }
}
