<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionInvitation extends Model
{
    use HasFactory;

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }
}
