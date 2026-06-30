<?php

namespace App\Http\Controllers;

use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::query()
            ->orderBy('label')
            ->get();

        return response()->json([
            'genres' => $genres,
        ]);
    }
}
