<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Relations\BelongsTo;

class ReservationNote extends Model
{
    use HasFactory;

    public function  getuser()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

}
