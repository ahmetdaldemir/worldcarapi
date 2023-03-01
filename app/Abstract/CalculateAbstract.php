<?php

namespace App\Abstract;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CalculateAbstract
{

    public function mount_diff()
    {
       return Carbon::parse($this->pickup_date)->diffInMonths(Carbon::parse($this->dropoff_date)) == 0 ? '2' : Carbon::parse($this->pickup_date)->diffInMonths(Carbon::parse($this->dropoff_date));
    }

    public function year_diff()
    {
        return Carbon::parse($this->pickup_date)->diffInYears(Carbon::parse($this->dropoff_date));
    }

    public function mount_difference(): array
    {
        $month = [];
        $date1 = $this->pickup_date;
        $date2 = $this->dropoff_date;
        $time = strtotime($date1);
        $start = date("m", strtotime("+0 month", $time));
        // $start = date("m",strtotime($date1));
        $d1 = explode('-', $date1);
        $d2 = explode('-', $date2);


        if($start > $d2[1])
        {
            for ($m = $start; $m < $start + $d2[1] + 1; $m++) {
                $month[] = date('m', mktime(0, 0, 0, $m, 1, date('Y')));
            }
        }else{
            for ($m = $start; $m < $d2[1]; $m++) {
                $month[] = date('m', mktime(0, 0, 0, $m, 1, date('Y')));
            }
            array_shift($month);
        }

        return $month;
    }

    public function date_difference(): int
    {
        $start = Carbon::parse($this->pickup_date);
        $end = Carbon::parse($this->dropoff_date);
        return $end->diffInDays($start);
    }

    public function time_difference(): int
    {
        if($this->pickup_time < $this->dropoff_time)
        {
            $startTime = Carbon::parse($this->pickup_time);
            $finishTime = Carbon::parse($this->dropoff_time);

            $diff = $finishTime->diffInMinutes($startTime);

            if ($diff > $this->reservation_time_diff) {
                return 1;
            } else {
                return 0;
            }
        }else{
            return 0;
        }
    }
}
