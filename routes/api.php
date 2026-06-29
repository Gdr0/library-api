<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

// rotte NON PROTETTE
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

// rotte PROTETTE
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

// rotte LIBRI
    Route::prefix('books')->group(function () {
        Route::get('', [BookController::class, 'index']);
        Route::get('{id}', [BookController::class, 'show']);
        Route::post('', [BookController::class, 'storeOrUpdate']);
        Route::delete('{id}', [BookController::class, 'softDelete']);
    });

// rotte CLIENTI
    Route::prefix('clients')->group(function() {
        Route::post('', [ClientController::class, 'storeOrUpdate']);
        Route::get('', [ClientController::class, 'index']);
        Route::get('{id}', [ClientController::class, 'show']);
    });

    Route::prefix('document-types')->group(function () {
        Route::get('', [DocumentTypeController::class, 'index']);
    });

// rotte PRESTITI
    Route::prefix('loans')->group(function () {
        Route::post('', [LoanController::class, 'store']);
        Route::patch('return', [LoanController::class, 'returnBookOrLoan']);
        Route::get('', [LoanController::class, 'index']);
        Route::get('{id}', [LoanController::class, 'show']);
    });

    // rotte AUTORI
    Route::prefix('authors')->group(function () {
        Route::get('', [AuthorController::class, 'index']);
        Route::post('', [AuthorController::class, 'store']);
    });

// rotte EDITORI
    Route::prefix('editors')->group(function () {
        Route::get('', [EditorController::class, 'index']);
    });


});
