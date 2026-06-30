<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request) {
        $search = trim((string) $request->string('search'));
        $genreId = $request->integer('genre_id');

        $booksQuery = Book::with(['authors', 'editor', 'genres', 'bookLoans.returns']);

        if ($search !== '') {
            $words = preg_split('/\s+/', $search);

            foreach ($words as $word) {
                $booksQuery->where(function ($query) use ($word) {
                    $query
                        ->where('title', 'like', '%'.$word.'%')
                        ->orWhereHas('authors', function ($authorQuery) use ($word) {
                            $authorQuery
                                ->where('name', 'like', '%'.$word.'%')
                                ->orWhere('last_name', 'like', '%'.$word.'%');
                        });
                });
            }
        }

        if ($genreId > 0) {
            $booksQuery->whereHas('genres', function ($query) use ($genreId) {
                $query->where('genres.id', $genreId);
            });
        }

        $books = $booksQuery
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'books' => $books,
        ]);
    }

    public function storeOrUpdate(Request $request) {
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
            'genres' => 'required | array | min:1',
            'genres.*' => 'required | integer | distinct | exists:genres,id',
        ]);

        $authors = $validated['authors'];
        $genres = $validated['genres'];
        unset($validated['authors'], $validated['genres'], $validated['id']);

        if ($bookId !== null) {
            $book = Book::findOrFail($bookId);
            $book->update($validated);
            $book->authors()->sync($authors);
            $book->genres()->sync($genres);

            return response()->json([
                'book' => $book->load(['authors', 'editor', 'genres', 'bookLoans.returns']),
                'message' => 'Book updated successfully',
            ]);
        }

        $book = Book::create($validated);
        $book->authors()->sync($authors);
        $book->genres()->sync($genres);

        return response()->json([
            'book' => $book->load(['authors', 'editor', 'genres', 'bookLoans.returns']),
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

    public function show($id) {
        $book = Book::with(['authors', 'editor', 'genres', 'bookLoans.returns'])->findOrFail($id);

        return response()->json([
            'book' => $book,
        ]);
    }
}
