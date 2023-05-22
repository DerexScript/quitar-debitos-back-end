<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'value',
        'due_date',
        'status',
        'voucher'
    ];

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }

    public function confirmPayments()
    {
        return $this->hasMany(ConfirmPayment::class);
    }
}
