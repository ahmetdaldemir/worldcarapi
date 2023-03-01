<?php namespace App\Service;

use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Ekstra;
use App\Models\EkstraLanguage;
use App\Models\Image;
use App\Models\Language;
use App\Models\Location;
use App\Models\LocationValue;
use App\Models\PeriodPrice;
use App\Models\Setting;
use App\Models\StopSell;
use App\Models\TransferZoneFee;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;


class Search
{
    protected $up_time;
    protected $down_time;
    protected $up_date;
    protected $down_date;
    protected $up_location;
    protected $drop_location;
    protected $mainLocation;
    protected $reservation_time_diff;
    protected $dropLocation;
    private array $mounth_diffarence;
    private int $mountdiff;
    private int $yeardiff;
    private float $diffarence_period_price;
    protected $drop_price;
    protected $up_price;
    protected $up_drop_price;
    protected $outside_discount;
    protected $peridprocess;
    protected $test;


    public function __construct($responseData, $currencyData,$outside_discount)
    {
        session(['currency' => $currencyData]);

        $this->up_time = $responseData["cikis_saati_submit"];
        $this->down_time = $responseData["donus_saati_submit"];
        $this->drop_price = 0;
        $this->up_date = $responseData["cikis_tarihi_submit"];
        $this->down_date = $responseData["donus_tarihi_submit"];
        $this->up_location = $responseData["pick_up_location"];
        $this->currency_Data = $currencyData;

        if (isset($responseData["end_point"])) {
            $this->drop_location = $responseData["end_point"];
        } else {
            $this->drop_location = $this->up_location;
        }
        $this->reservation_time_diff = Setting::where('key', "reservation_time")->first()->value;
        $this->mainLocation = Location::find($this->up_location);
        $this->dropLocation = Location::find($this->drop_location);
        $this->date_diffarence = $this->date_Difference();
        $this->time_diffarence = $this->time_Difference();

        $this->mountdiff = Carbon::parse($this->up_date)->diffInMonths(Carbon::parse($this->down_date)) == 0 ? '2' : Carbon::parse($this->up_date)->diffInMonths(Carbon::parse($this->down_date));
        $this->yeardiff = Carbon::parse($this->up_date)->diffInYears(Carbon::parse($this->down_date));
        $this->mounth_diffarence = $this->mount_difference();
        if(count($this->mounth_diffarence) > 2)
        {
            array_shift($this->mounth_diffarence);
            array_pop($this->mounth_diffarence);
        }

        $diffarence_period_price = 0;


        $this->full_date = $this->date_diffarence + $this->time_diffarence;
        $this->period = $this->rent_Period();
        $this->cars_all = Car::where("is_active", 1)->get();
        $this->date_mounth_range = $this->date_Month_Range();
        $this->calculate = CAL_GREGORIAN;
        if ($this->mainLocation->id_parent != 0) {
            $this->queryLocation = $this->mainLocation->id_parent;
        } else {
            $this->queryLocation = $this->mainLocation->id;
        }
        $this->settings = new Setting();
        $this->price = 0;
        $this->day_price = 0;
        $this->discount = 0;
        $this->drop_Location_Price = 0;
        $cars = json_decode($this->cars_all, TRUE);
        $this->car_remove($cars);
        $this->up_price = $this->mainLocation->price;
        $this->up_drop_price = $this->dropLocation->drop_price;
        $this->outside_discount = $outside_discount;
        $this->peridprocess = false;
    }

    public function index()
    {
        $response = [];
        foreach ($this->cars as $car) {
            $response[] = array(
                'id_car' => $car['id'],
                'price' => $this->price_Calculate($car['id']),
                'car' => static::getBrandModel($car['brand'], $car['model']) . " " . $car['car_name'],
                'days' => $this->full_date,
                'time_diff' => $this->time_diffarence,
                'period' => $this->period,
                'checkin_location' => $this->mainLocation,
                'checkout_location' => $this->dropLocation,
                'checkin' => $this->up_date,
                'checkout' => $this->down_date,
                'checkout_time' => $this->up_time,
                'checkin_time' => $this->down_time,
                'location_value' => Location::getViewLocationMeta($this->up_location),
                'fuel' => $car['fuel'],
                'type' => $car['type'],
                'category' => $car['category'],
                'doors' => $car['doors'],
                'passenger' => $car['passenger'],
                'big_luggage' => $car['big_luggage'],
                'small_luggage' => $car['small_luggage'],
                'sun_roof' => $car['sun_roof'],
                'air_conditioner' => $car['air_conditioner'],
                'image' => $car['default_images'],
                'transmission' => $car['transmission'],
                'imageLists' => $this->getImageList($car['id']),
                'car_km' => $this->settings->where('key', "car_km")->first()->value,
                'car_km_day' => $this->settings->where('key', "car_km_day")->first()->value,
                'license_age' => $this->settings->where('key', "license_age")->first()->value,
                'driver_age' => $this->settings->where('key', "driver_age")->first()->value,
                'test' => $this->mountdiff,
            );
        }
        return $response;
    }

    public function date_Difference(): int
    {
        $start = Carbon::parse($this->up_date);
        $end = Carbon::parse($this->down_date);
        return $end->diffInDays($start);
    }

    public function time_Difference(): int
    {
        if($this->up_time < $this->down_time)
        {
            $startTime = Carbon::parse($this->up_time);
            $finishTime = Carbon::parse($this->down_time);

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

    public function mount_difference(): array
    {
        $month = [];
        $date1 = $this->up_date;
        $date2 = $this->down_date;
        $time = strtotime($date1);
        $start = date("m", strtotime("+0 month", $time));
        // $start = date("m",strtotime($date1));
        $d1 = explode('-', $date1);
        $d2 = explode('-', $date2);


        Log::info($d2[1]);
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

    public function rent_Period()
    {

        if ($this->full_date <= 3) {
            return "period1";
        } else if ($this->full_date > 3 && $this->full_date <= 6) {
            return "period2";
        } else if ($this->full_date > 6 && $this->full_date <= 10) {
            return "period3";
        } else if ($this->full_date > 10 && $this->full_date <= 13) {
            return "period4";
        } else if ($this->full_date > 13 && $this->full_date <= 20) {
            return "period5";
        } else if ($this->full_date > 20 && $this->full_date <= 29) {
            return "period6";
        } else if ($this->full_date >= 30) {
            return "period7";
        }
    }

    public function date_Month_Range()
    {
        $start_mounth = date("m", strtotime($this->up_date));
        $finish_mounth = date("m", strtotime($this->down_date));
        if ($start_mounth != $finish_mounth) {
            return TRUE;
        }
    }

    public function rent_Mounth($date): string
    {
        return date("m", strtotime($date));
    }

    public function price_Calculate($id_car)
    {
        $rent_price = 0;
        $diffarence_period_price = 0;

        $mainlocationparent = Location::find($this->mainLocation->id_parent);
        $droplocationparent = Location::find($this->dropLocation->id_parent);


        $transferPrice = TransferZoneFee::where('id_location', $mainlocationparent->id)->where('id_location_transfer_zone', $droplocationparent->id)->first();
        if ($transferPrice) {
            $this->drop_Location_Price = $transferPrice->price;
        }

        if ($this->up_location != $this->drop_location) {
            if ($this->up_drop_price > 0) {
                $this->drop_price = $this->up_drop_price;
            } else {
                $this->drop_price = $this->drop_Location_Price;
            }
        }


        if (is_null($this->date_mounth_range) && $this->date_mounth_range == FALSE) {
            // if ($mainlocationparent->min_day <= $this->full_date) {
            $period_data = PeriodPrice::where("id_car", $id_car)->where("mounth", $this->rent_Mounth($this->up_date))->where("id_location", $this->queryLocation)->where("status", TRUE)->first();
            if ($period_data) {
                if($this->full_date >= $period_data->min_day)
                {
                    $deneme = "girdi";
                    $this->peridprocess = true;
                    $this->price = $period_data->{$this->period} * $this->full_date;
                    $this->day_price = $period_data->{$this->period};
                    $this->discount = $period_data->discount;
                }else{
                    $deneme = "Girmedi";
                    $this->peridprocess = false;
                }
            }
            //}
        }
        if ($this->date_mounth_range == TRUE) {

            $yil_up = date('Y', strtotime($this->up_date));
            $mounth = date('m', strtotime($this->up_date));
            $start_date_days_count = cal_days_in_month($this->calculate, $mounth, $yil_up);
            $start_date_day = $yil_up . "-" . $mounth . "-" . $start_date_days_count;
            $up_date_count = $this->customDifference($this->up_date, $start_date_day);

            $yil_down = date('Y', strtotime($this->down_date));
            $mounth_down = date('m', strtotime($this->down_date));
            $finish_date_days_count = cal_days_in_month($this->calculate, $mounth_down, $yil_down);
            $finish_date_day = $yil_down . "-" . $mounth_down . "-" . 1;

            $down_date_count = $this->customDifference($this->down_date, $finish_date_day);

            $last_down_date_count = $this->time_diffarence + $down_date_count;

            $first_period = PeriodPrice::where("id_car", $id_car)->where("min_day", '<=', $this->full_date)->where("mounth", $this->rent_Mounth($this->up_date))->where("id_location", $this->mainLocation->id_parent)->where("status", TRUE)->first();
            $second_period = PeriodPrice::where("id_car", $id_car)->where("mounth", $this->rent_Mounth($this->down_date))->where("id_location", $this->mainLocation->id_parent)->where("status", TRUE)->first();
            $diffarence_period_days_count = 0;
            $price = 0;
            if ($mainlocationparent->min_day <= $this->full_date) {

                if (isset($first_period) && isset($second_period) ) {



                    $first_period_price = $first_period->{$this->period} * $up_date_count;
                    $second_period_price = $second_period->{$this->period} * ($last_down_date_count + 1);

                    $this->day_price = $first_period->{$this->period};


                    $this->test = $this->mounth_diffarence;

                    if ($this->mountdiff >= 2) {
                        foreach ($this->mounth_diffarence as $difference) {
                            $diffarence_period_days_count = cal_days_in_month($this->calculate, $difference, date("Y"));
                            $diffarence_period = PeriodPrice::where("id_car", $id_car)->where("mounth", $difference)->where("id_location", $this->mainLocation->id_parent)->where("status", TRUE)->first();
                            if ($diffarence_period) {
                                $diffarence_period_price += $diffarence_period->{$this->period} * $diffarence_period_days_count;
                            }
                        }
                    }
                    if($first_period->min_day <= $this->full_date) {
                        $this->peridprocess = true;
                        if ($first_period->{$this->period} != 0 && $second_period->{$this->period} != 0) {
                            $this->price = $first_period_price + $second_period_price + $diffarence_period_price;
                        } else {
                            $this->price = 0;
                        }
                    }
                }
            }
            $this->discount = $first_period->discount ?? 0;
        }
        $total_sub_price = $this->price + $this->up_price + $this->drop_price - $this->outside_discount;
        $rent_price = $this->price;

        $ekstra_including_price = $total_sub_price + $this->mandatoryInContract();
        $total_price = $ekstra_including_price - (($ekstra_including_price * $this->discount) / 100);

        if (($mainlocationparent->min_day > $this->full_date) ||  $this->peridprocess == false) {

            $priceArray = array(
                'drop_price' => 0,
                'min_day' => $this->mainLocation->min_day,
                'up_price' => 0,
                'up_drop_price' => 0,
                'main_price' => 0,
                'total_day' => $this->full_date,
                'day_price' => 0,
                'rent_price' => 0,
                'discount' => 0,
                'discount_price' => 0,
                'currency_data' => (float)$this->currency_Data,
                'mandatoryInContract' => $this->mandatoryInContract(),
                'ekstra_including_price' => 0,
                'this_price' => $this->price,
                'period_process' => $this->peridprocess,
                'test'=>$mainlocationparent->min_day,
            );
        } else {
            $priceArray = array(
                'drop_price' => number_format(($this->drop_price * (float)$this->currency_Data),2),
                'min_day' => $this->mainLocation->min_day,
                'up_price' => number_format(($this->up_price * (float)$this->currency_Data),2),
                'up_drop_price' => number_format(($this->up_drop_price * (float)$this->currency_Data),2),
                'main_price' => number_format(($this->price + $this->up_price + $this->drop_price) * (float)$this->currency_Data,2),
                'total_day' => $this->full_date,
                'day_price' => number_format((($rent_price / $this->full_date) * (float)$this->currency_Data),2),
                'rent_price' => $rent_price * (float)$this->currency_Data,
                'discount' => $this->discount,
                'discount_price' => $total_price * (float)$this->currency_Data,
                'currency_data' => (float)$this->currency_Data,
                'mandatoryInContract' => $this->mandatoryInContract(),
                'ekstra_including_price' => $ekstra_including_price,
                'this_price' => $this->drop_price,
                'period_process' => $this->peridprocess,
                'test'=>$this->test,
            );
        }
        return $priceArray;
    }

    public function mandatoryInContract()
    {
        $mandatoryprice = 0;
        $mandatoryEkstra = Ekstra::where("mandatoryInContract", "yes")->get();
        foreach ($mandatoryEkstra as $item) {
            if($item->sellType == "daily")
            {
                $mandatoryprice += $item->price * $this->full_date;
            }else{
                $mandatoryprice += $item->price;
            }
        }
        return $mandatoryprice;
    }

    public static function customDifference($up_date, $down_date): int
    {
        $start = Carbon::parse($up_date);
        $end = Carbon::parse($down_date);
        $length = $end->diffInDays($start);

        return $length;
    }

    public function custom_Difference($up_time, $down_time): int
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

    public static function getBrandModel($brand = null, $model = null)
    {
        $brand = Brand::find($brand);
        $brand = $brand->brandname ?? "Bulunamadı";
        $model = CarModel::find($model);
        $model = $model->modelname ?? "Bulunamadı";
        return $brand . " " . $model;
    }

    public static function getDefaultImage($id)
    {
        return Image::where("default", "default")->where("id_module", $id)->where("module", "cars")->first();
    }

    public static function getImageList($id)
    {
        return Image::where("default", "normal")->where("id_module", $id)->where("module", "cars")->get();
    }

    public function calculateEkstra($ekstra, $days)
    {
        $x = 0;
        foreach ($ekstra as $key => $value) {
            $x += $this->customCalculate->customCalculate($key, $days, $value[0]);
        }
        return $x;
    }


    public static function ekstraProcess($ekstra, $price)
    {
        $langId = Language::where("url", app()->getLocale())->first()->id;

        $data = array();
        foreach ($ekstra as $key => $value) {
            $x = Ekstra::where("id", $key)->first();
            $data[] = array(
                'EkstaName' => EkstraLanguage::getSelectLang($x->id, 'title', $langId),
                'EkstaPrice' => $x->price * $price,
                'EkstaItem' => $value,
                'value' => $value[0],
                'sellType' => $x->sellType,
                'TotalItemPrice' => $x->price * $value[0] * $price,
                'mandatoryInContract' => $x->mandatoryInContract
            );
        }
        return $data;
    }

    public static function generalTotal($ekstra)
    {
        $data = array();
        foreach ($ekstra as $key => $value) {
            $x = Ekstra::where("id", $key)->first();
            $data[] = array(
                'EkstaName' => EkstraLanguage::getSelectLang($x->id, 'title', 1),
                'EkstaPrice' => $x->price,
                'EkstaItem' => $value,
                'TotalItemPrice' => $x->price * $value,
            );
        }
        return $data;
    }

    public function car_remove(array $cars)
    {
        Log::info('Removing cars without ids: ');
        $nWithoutId = 0;
        foreach ($cars as $key => $car) {
            $period_data = PeriodPrice::where("id_car", $car['id'])->where("mounth", $this->rent_Mounth($this->up_date))->where("id_location", $this->queryLocation)->where("status", TRUE)->first();
            if (!$period_data) {
                unset($cars[$key]);
                $nWithoutId++;
            }
        }

//        $stopsell = StopSell::where('id_car', $car['id'])
//            ->where('check_in', '<=', Carbon::parse($this->down_date)->format("Y-m-d"))
//            ->where('check_out', '>=', Carbon::parse($this->up_date)->format("Y-m-d"))->first();
//        if ($stopsell) {
//            unset($cars[$key]);
//            $nWithoutId++;
//        }
        $this->cars = $cars;
        Log::notice("$nWithoutId removed.");
    }

    ////-------------------------------------------------------------------------------///


    public static function getOperationType($location)
    {
        return Location::where("id", $location)->first()->type;
    }

    public static function getMounthName($date, $index)
    {
        setlocale(LC_TIME, 'turkish');
        $x = iconv('latin5', 'utf-8', strftime('%d %B %Y %A', strtotime($date)));
        $x = explode(" ", $x);
        return $x[$index];
    }

    public static function getPickupMain($id)
    {
        return Location::find($id)->id_parent ?? 0;
    }

    public static function getLocationName(int $id = NULL)
    {
        if($id != NULL)
        {
            $langId = Language::where("url", app()->getLocale())->first()->id;
            return LocationValue::where("id_location", $id)->where("id_lang", $langId)->first()->title ?? null;
        }else{
            return null;
        }

    }

    public static function getLocationParentName(int $id)
    {
        $langId = Language::where("url", app()->getLocale())->first()->id;
        $id_parent = Location::find($id)->id_parent;
        return LocationValue::where("id_location", $id_parent)->where("id_lang", $langId)->first()->title;
    }

    public static function getDropLocation(int $ids): array
    {
        $langId = Language::where("url", app()->getLocale())->first()->id;
        if ($ids == 0) {
            $location = Location::join('location_values', 'locations.id', '=', 'location_values.id_location')->select('locations.*', 'location_values.title')->where('location_values.id_lang', 1)->get();
        } else {
            $id_location = Location::find($ids)->id_parent;
            $transferLocation = TransferZoneFee::where("id_location", $id_location)->where("status", 1)->get();
            foreach ($transferLocation as $item) {
                $locatiValue = LocationValue::where("id_location", $item->id_location_transfer_zone)->where("id_lang", $langId)->first();
                $location[] = array(
                    'id' => $item->id_location_transfer_zone,
                    'title' => $locatiValue->title,
                    'id_parent' => Location::find($item->id_location_transfer_zone)->id_parent,
                    'type' => $locatiValue->type,
                );
            }
        }
        return $location;
    }

    public static function doco_convert($data)
    {
        if (strpos($data, ".")) {
            $chng = str_replace(".", ",", $data);
            $data = $chng;
        }
        return $data;
    }

    public static function codo_convert($data)
    {
        if (strpos($data, ".")) {
            $chng = str_replace(",", ".", $data);
            $data = $chng;
        }
        return $data;
    }

}
