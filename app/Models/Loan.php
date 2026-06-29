<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{

    use HasFactory, SoftDeletes;

    protected $appends = [
        'loan_books_quantity',
        'returned_books_quantity',
        'total_at_return',
    ];

    protected $fillable = [
        'status_id',
        'client_id',
        'document_type_id',
        'document_number',
        'started_at',
        'expiring_at',
        'closed_at',
        'final_price',
        ];

        protected function casts(): array {
            return [
                'started_at' => 'date',
                'expiring_at' => 'date',
                'closed_at' => 'date',
                'final_price' => 'decimal:2',
            ];
        }

        public function client() {
            return $this->belongsTo(Client::class);
        }
        public function status() {
            return $this->belongsTo(LoanStatus::class);
        }
        public function documentType() {
            return $this->belongsTo(DocumentType::class);
        }
        public function books(){

            return $this->belongsToMany(Book::class, 'book_loans')->withPivot('unit_price', 'quantity')->withTimestamps();
        }
        public function bookLoans() {
            return $this->hasMany(BookLoan::class);
        }
        public function bookLoanReturns() {
            return $this->hasManyThrough(
                BookLoanReturn::class,
                BookLoan::class,
                'loan_id',
                'book_loan_id',
                'id',
                'id',
            );
        }
        public function fine () {
            return $this->hasOne(LoanFine::class);
        }

        public function getLoanBooksQuantityAttribute(): int
        {
            return $this->bookLoans->sum('quantity');
        }

        public function getReturnedBooksQuantityAttribute(): int
        {
            return $this->bookLoans->sum('returned_books_quantity');
        }

        public function getTotalAtReturnAttribute(): float
        {
            return (float) $this->bookLoans->sum('total_at_return');
        }

}
