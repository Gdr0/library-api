<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanStatus extends Model
{
        protected $fillable = [
        'status',
        'label',
    ];  


    public function loans() {
        return $this->HasMany(Loan::class);
    }

}
