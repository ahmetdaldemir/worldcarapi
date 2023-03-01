<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstra extends Model
{

    public function getLanguage($language)
    {
      $x =  \DB::table('ekstra_languages')->where('id_lang',$language)->where('id_ekstra',$this->id)->first();
      return $x->title;
    }

}
