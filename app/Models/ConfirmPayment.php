<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfirmPayment extends Model
{
    use HasFactory;

    protected $hidden = [
        'laravel_through_key'
     ];

    public function installment()
    {
        return $this->belongsTo(installment::class);
    }
}
