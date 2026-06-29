<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'editor_id',
        'title',
        'isbn',
        'synopsis',
        'daily_price',
        'total_quantity'
    ];

    protected function casts(): array
    {
        return [
            'daily_price' => 'decimal:2',
        ];
    }

    public function editor() {
        return $this->belongsTo(Editor::class);
    }

    public function authors() {
        return $this->belongsToMany(Author::class);
    }

    public function genres() {
        return $this->belongsToMany(Genre::class);
    }

    public function loans() {
        return $this->belongsToMany(Loan::class, 'book_loans')->withPivot('unit_price', 'quantity')->withTimestamps();
    }

    public function bookLoans(){
        return $this->hasMany(BookLoan::class);
    }

    public function bookLoanReturns() {
        return $this->hasManyThrough(
            BookLoanReturn::class,
            BookLoan::class,
            'book_id',
            'book_loan_id',
            'id',
            'id',
        );
    }


}
