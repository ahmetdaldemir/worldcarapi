<?php


namespace App\Services;


use App\Models\Setting;
use Carbon\Carbon;
use \DateTime;

class CustomCalculate
{

    protected $reservation_time_diff;

    public function __construct()
    {
        $this->reservation_time_diff = Setting::where('key', "reservation_time")->first()->value;
    }

    public function customDifference($up_date, $down_date): int
    {
        $start = Carbon::parse($up_date);
        $end = Carbon::parse($down_date);
        $length = $end->diffInDays($start);

        return $length;
    }

    public function timeDifference($up_time, $down_time): int
    {
        $baslangic = new DateTime($up_time);
        $fark = $baslangic->diff(new DateTime($down_time));
        $diff = $fark->m;
        if ($diff > $this->reservation_time_diff) {
            return 1;
        } else {
            return 0;
        }
    }


}
