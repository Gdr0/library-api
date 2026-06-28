<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status_id',
        'client_id',
        'document_type_id',
        'document_number',
        'started_at',
        'expiring_at',
        'returned_at',
        'base_price',
        'total_price',
        ];

        protected function casts(): array {
            return [
                'started_at' => 'date',
                'expiring_at' => 'date',
                'returned_at' => 'date',
                'base_price' => 'decimal:2',
                'total_price' => 'decimal:2',
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
        public function fine () {
            return $this->hasOne(LoanFine::class);
        }

}
