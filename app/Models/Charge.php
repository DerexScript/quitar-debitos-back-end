<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'total_value',
        'number_of_installments',
        'payment_day'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('status');
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function collectionInvitations()
    {
        return $this->hasMany(CollectionInvitation::class);
    }
}
