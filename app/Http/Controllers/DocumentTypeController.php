<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;

class DocumentTypeController extends Controller
{
    public function getDocumentTypes() {
        $documentTypes = DocumentType::orderBy('name')->get();

        return response()->json([
            'documentTypes' => $documentTypes,
        ]);
    }
}
