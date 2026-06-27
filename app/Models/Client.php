<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'last_name',
        'phone_number',
        'email',
        'cf',
    ];

    public function loans() {
        return $this->hasMany(Loan::class);
    }

}
