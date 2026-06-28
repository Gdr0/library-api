<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function getAuthors(Request $request) {
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
}
