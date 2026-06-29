<?php

namespace App\Http\Controllers;

use App\Models\Editor;
use Illuminate\Http\Request;

class EditorController extends Controller
{
    public function index(Request $request) {
        $search = trim((string) $request->string('search'));
        $editorsQuery = Editor::query();

        if ($search !== '') {
            $editorsQuery->where('name', 'like', '%'.$search.'%');
        }
        $editors = $editorsQuery->orderBy('name')->limit(10)->get();

        return response()->json([
            'editors' => $editors,
        ]);
    }
}
