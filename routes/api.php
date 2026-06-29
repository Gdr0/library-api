<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ClientController;
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
        Route::get('bookIndex', [BookController::class, 'bookIndex']);
        Route::get('getBookById/{id}', [BookController::class, 'getBookById']);
        Route::post('createOrUpdateBooks', [BookController::class, 'createOrUpdateBooks']);
        Route::delete('{id}', [BookController::class, 'softDelete']);
    });

// rotte CLIENTI
    Route::prefix('client')->group(function() {
        Route::post('CreateOrUpdateClient', [ClientController::class, 'CreateOrUpdateClient']);
        Route::get('clientIndex', [ClientController::class, 'clientIndex']);
        Route::get('getClientById/{id}', [ClientController::class, 'getClientById']);
    });

// rotte PRESTITI
    Route::prefix('loans')->group(function () {
        Route::post('CreateLoan', [LoanController::class, 'CreateLoan']);
        Route::patch('returnBookOrLoan', [LoanController::class, 'returnBookOrLoan']);
        Route::get('loanIndex', [LoanController::class, 'loanIndex']);
        Route::get('loanDetail/{id}', [LoanController::class, 'loanDetail']);
    });

    // rotte AUTORI
    Route::prefix('authors')->group(function () {
        Route::get('getAuthors', [AuthorController::class, 'getAuthors']);
    });

// rotte EDITORI
    Route::prefix('editors')->group(function () {
        Route::get('getEditors', [EditorController::class, 'getEditors']);
    });


});
