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
        'total_quantity'
    ];

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

    
}
