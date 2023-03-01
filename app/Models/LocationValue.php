<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationValue extends Model
{

    public static function getViewLocationMeta($id)
    {
        $langId = Language::where("url", app()->getLocale())->first()->id;
        $location_values =    LocationValue::where('id_location',$id)->where('id_lang',1)->first()->meta_title;
        return $location_values;
    }
}
