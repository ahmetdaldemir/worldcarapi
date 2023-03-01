<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{

    public static function getViewLocationMeta($id,$language)
    {
        $location_values =    LocationValue::where('id_location',$id)->where('id_lang',$language)->first()->meta_title;
        return $location_values;
    }

    public static function getViewCenterId($id)
    {
        $langId = Language::where("url", app()->getLocale())->first()->id;

        $repsonse = \DB::table('locations')
            ->join('location_values', 'locations.id', '=', 'location_values.id_location')
            ->where('location_values.id_lang',$langId)
            ->where('locations.id', $id)
            ->where('locations.status', 1)
            ->select('locations.*', 'location_values.title')
            ->get();
        return $repsonse;
    }

}
