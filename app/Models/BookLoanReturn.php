<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLoanReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_loan_id',
        'returned_quantity',
        'returned_at',
        'total_at_return',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'date',
            'total_at_return' => 'decimal:2',
        ];
    }

    public function bookLoan()
    {
        return $this->belongsTo(BookLoan::class);
    }
}
