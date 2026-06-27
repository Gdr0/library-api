<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanFine extends Model
{
        use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_id',
        'amount',
    ];

    protected function casts(): array {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function loan(){
        return $this->belongsTo(Loan::class);
    }

}
