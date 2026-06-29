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
        Schema::create('book_loan_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_loan_id')->constrained('book_loans');
            $table->integer('returned_quantity'); // quantità rientrata in questo specifico evento di restituzione
            $table->date('returned_at'); // data del singolo rientro
            $table->decimal('total_at_return', 10, 2)->default(0); // totale maturato al momento di questo rientro
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_loan_returns');
    }
};
