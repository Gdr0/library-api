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
            $table->foreignId('status_id')->constrained('loan_statuses');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('document_type_id')->constrained('document_types');
            $table->string('document_number');
            $table->date('started_at');
            $table->date('expiring_at');
            $table->date('returned_at')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
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
