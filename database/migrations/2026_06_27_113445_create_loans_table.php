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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->default(1)->constrained('loan_statuses'); // stato associato al prestito
            $table->foreignId('client_id')->constrained('clients'); //id del cliente
            $table->foreignId('document_type_id')->constrained('document_types'); // tipo documento
            $table->string('document_number'); // numero documento fornito dal cliente per il prestito
            $table->date('started_at'); // inizio prestito
            $table->date('expiring_at'); //scadenza prestito (per tutti i libri)
            $table->date('closed_at')->nullable(); //chiusura effettiva del prestito
            $table->decimal('final_price', 10, 2)->default(0); // prezo finale del prestito - incluse more
            $table->softDeletes();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
