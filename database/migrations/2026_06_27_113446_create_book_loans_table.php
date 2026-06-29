<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books');
            $table->foreignId('loan_id')->constrained('loans');
            $table->decimal('unit_price', 10, 2); // prezzo giornaliero del libro al momento del prestito
            $table->integer('quantity'); // quantità dello specifico libro nel prestito
            // così lo stesso libro può comparire una sola volta nel prestito
            $table->unique(['loan_id', 'book_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_loans');
    }
};
