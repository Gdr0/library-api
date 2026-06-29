<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'loan_id',
        'unit_price',
        'quantity',
    ];

    protected function casts(): array {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

        public function book () {
            return $this->belongsTo(Book::class);
        }
        public function loan () {
            return $this->belongsTo(Loan::class);
        }
        public function returns () {
            return $this->hasMany(BookLoanReturn::class);
        }
}
