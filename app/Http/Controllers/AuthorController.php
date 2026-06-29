<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(Request $request) {
        $search = trim((string) $request->string('search'));
        $authorsQuery = Author::query();

        if ($search !== '') {
            $words = preg_split('/\s+/', $search);
            foreach ($words as $word) {
                $authorsQuery->where(function ($query) use ($word) {$query
                    ->where('name', 'like', '%'.$word.'%')
                        ->orWhere('last_name', 'like', '%'.$word.'%');
                });
            }
        }

        $authors = $authorsQuery->orderBy('name')->orderBy('last_name')->limit(10)->get();

        return response()->json([
            'authors' => $authors,
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required | string | max:50',
            'last_name' => 'required | string | max:50',
        ]);

        $author = Author::create($validated);

        return response()->json([
            'author' => $author,
            'message' => 'autore creato con successo',
        ], 201);
    }
}
