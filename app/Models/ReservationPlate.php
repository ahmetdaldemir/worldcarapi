<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationPlate extends Model
{
    use HasFactory;

    public function getPlate()
    {
        return $this->belongsTo(Plate::class, 'id_plate', 'id');
    }


 }
