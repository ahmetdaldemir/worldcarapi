<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{

    public const TRANSMISSION_SORT = [
        'Manuel' => 'Man.',
        'Otomatik' => 'Oto.'
    ];
    public const FUEL_SORT = [
        'Benzin' => 'B',
        'Dizel' => 'D',
        'Elektrik' => 'E',
        'Elektrikli' => 'E'
    ];


    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function brandfunction()
    {
        return $this->belongsTo(Brand::class,'brand','id');
    }

    public function modelfunction()
    {
        return $this->belongsTo(CarModel::class,'model','id');
    }

}
