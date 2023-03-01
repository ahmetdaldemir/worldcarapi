<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationSurvey extends Model
{
    use HasFactory;

    public function reservation()
    {
        return $this->hasOne(Reservation::class, 'id', 'id_reservation');
    }


    public static function question($id)
    {
        return SurveyLanguage::where('survey_id',$id)->first();
    }
    public static function answer($id)
    {
        return AnswerLanguage::find($id);
    }

}
