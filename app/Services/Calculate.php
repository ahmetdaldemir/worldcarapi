<?php namespace App\Services;

use App\Abstract\CalculateAbstract;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Ekstra;
use App\Models\Image;
use App\Models\Location;
use App\Models\PeriodPrice;
use App\Models\Setting;
use App\Models\TransferZoneFee;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Calculate extends CalculateAbstract
{

    protected mixed $pickup;
    protected mixed $dropoff;
    protected mixed $pickup_time;
    protected mixed $dropoff_time;
    protected mixed $pickup_date;
    protected mixed $dropoff_date;

    protected mixed $language;
    protected mixed $currency;
    protected int $calculate;
    private $reservation_time_diff;
    private $full_date;
    private $period;
    private $main_location;
    private $drop_location;
    private $settings;
    private $queryLocation;
    private $up_price;
    /**
     * @var int
     */
    private $drop_price;
    /**
     * @var int|mixed
     */
    private $outside_discount;
    /**
     * @var true|null
     */
    private $date_mounth_range;
    private $outside_country;
    private $currency_Data;
    private $up_drop_price;

    public function __construct(Request $request)
    {


        $this->pickup = $request->pickup;
        $this->dropoff = $request->dropoff;
        $this->pickup_time = $request->pickup_time;
        $this->dropoff_time = $request->dropoff_time;
        $this->pickup_date = $request->pickup_date;
        $this->dropoff_date = $request->dropoff_date;
        $this->language = $request->language;
        $this->currency = $request->currency;
        $this->reservation_time_diff = Setting::where('key', "reservation_time")->first()->value;
        $this->full_date = $this->date_difference() + $this->time_difference();
        $this->calculate = CAL_GREGORIAN;
        $this->cars_all = Car::where("is_active", 1)->get();
        $cars = json_decode($this->cars_all, TRUE);
        $this->car_remove($cars);
        $this->period = $this->rent_period();
        $this->main_location = Location::find($this->pickup);
        $this->drop_location = Location::find($this->dropoff);
        $this->settings = new Setting();
        $this->date_mounth_range = $this->date_month_range();
        if ($this->main_location->id_parent != 0) {
            $this->queryLocation = $this->main_location->id_parent;
        } else {
            $this->queryLocation = $this->main_location->id;
        }
        $this->up_price = $this->main_location->price;
        $this->drop_price = 0;
        if($this->outside_country != "TR")
        {
            $this->outside_discount = Setting::where("key", "outside_price")->orderBy("id", "desc")->limit(1)->first()->value;
        }else{
            $this->outside_discount = 0;
        }
        $currency = "EUR_".$request->currency;
        $currencyResponse = new CurrencyService($currency);
        $currencyData = $currencyResponse->getCurrency();
        $this->currency_Data = $currencyData;
        $this->up_drop_price = $this->drop_location->drop_price;
    }

    public function index()
    {
        $this->mount_diff();
        $this->year_diff();
        $this->mount_difference();
        $this->date_difference();
        $this->time_difference();

        $response = [];
        foreach ($this->cars as $car) {
            $response[] = array(
                'id_car' => $car['id'],
                'price' => $this->price_calculate($car['id']),
                'car' => static::get_brand_model($car['brand'], $car['model']) . " " . $car['car_name'],
                'days' => $this->full_date,
                'time_diff' => $this->time_difference(),
                'period' => $this->period,
                'checkin_location' => $this->main_location,
                'checkout_location' => $this->drop_location,
                'checkin' => $this->pickup_date,
                'checkout' => $this->dropoff_date,
                'checkout_time' => $this->pickup_time,
                'checkin_time' => $this->dropoff_time,
                'location_value' => Location::getViewLocationMeta($this->pickup,$this->language),
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
            );
        }
        return $response;
    }

    public function date_month_range()
    {
        $start_mounth = date("m", strtotime($this->pickup_date));
        $finish_mounth = date("m", strtotime($this->dropoff_date));
        if ($start_mounth != $finish_mounth) {
            return TRUE;
        }
    }
    public function car_remove(array $cars)
    {
        $nWithoutId = 0;
        foreach ($cars as $key => $car) {
            $period_data = PeriodPrice::where("id_car", $car['id'])->where("mounth", $this->rent_mounth($this->pickup_date))->where("id_location", $this->pickup)->where("status", TRUE)->first();
            if (!$period_data) {
                unset($cars[$key]);
                $nWithoutId++;
            }
        }
        $this->cars = $cars;
    }

    public function rent_mounth($date): string
    {
        return date("m", strtotime($date));
    }


    public function price_calculate($id_car): array
    {
        $rent_price = 0;
        $diffarence_period_price = 0;
         $transferPrice = TransferZoneFee::where('id_location', $this->main_location->id)->where('id_location_transfer_zone', $this->drop_location->id)->first();
        if ($transferPrice) {
            $this->drop_Location_Price = $transferPrice->price;
        }

        if ($this->pickup != $this->dropoff) {
            if ($this->up_drop_price > 0) {
                $this->drop_price = $this->up_drop_price;
            } else {
                $this->drop_price = $this->drop_Location_Price;
            }
        }


        if (is_null($this->date_mounth_range) && $this->date_mounth_range == FALSE) {
            // if ($this->main_location->min_day <= $this->full_date) {
            $period_data = PeriodPrice::where("id_car", $id_car)->where("mounth", $this->rent_Mounth($this->pickup_date))->where("id_location", $this->queryLocation)->where("status", TRUE)->first();
            if ($period_data) {
                if ($this->full_date >= $period_data->min_day) {
                    $deneme = "girdi";
                    $this->peridprocess = true;
                    $this->price = $period_data->{$this->period} * $this->full_date;
                    $this->day_price = $period_data->{$this->period};
                    $this->discount = $period_data->discount;
                } else {
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

            $first_period = PeriodPrice::where("id_car", $id_car)->where("min_day", '<=', $this->full_date)->where("mounth", $this->rent_Mounth($this->pickup_date))->where("id_location", $this->main_location->id_parent)->where("status", TRUE)->first();
            $second_period = PeriodPrice::where("id_car", $id_car)->where("mounth", $this->rent_Mounth($this->dropoff_date))->where("id_location", $this->main_location->id_parent)->where("status", TRUE)->first();
            $diffarence_period_days_count = 0;
            $price = 0;
            if ($this->main_location->min_day <= $this->full_date) {

                if (isset($first_period) && isset($second_period)) {


                    $first_period_price = $first_period->{$this->period} * $up_date_count;
                    $second_period_price = $second_period->{$this->period} * ($last_down_date_count + 1);

                    $this->day_price = $first_period->{$this->period};


                    $this->test = $this->mounth_diffarence;

                    if ($this->mountdiff >= 2) {
                        foreach ($this->mounth_diffarence as $difference) {
                            $diffarence_period_days_count = cal_days_in_month($this->calculate, $difference, date("Y"));
                            $diffarence_period = PeriodPrice::where("id_car", $id_car)->where("mounth", $difference)->where("id_location", $this->main_location->id_parent)->where("status", TRUE)->first();
                            if ($diffarence_period) {
                                $diffarence_period_price += $diffarence_period->{$this->period} * $diffarence_period_days_count;
                            }
                        }
                    }
                    if ($first_period->min_day <= $this->full_date) {
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

        if (($this->main_location->min_day > $this->full_date) || $this->peridprocess == false) {

            $priceArray = array(
                'drop_price' => 0,
                'min_day' => $this->main_location->min_day,
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
                'test' => $this->main_location->min_day,
            );
        } else {
            $priceArray = array(
                'drop_price' => number_format(($this->drop_price * (float)$this->currency_Data), 2),
                'min_day' => $this->main_location->min_day,
                'up_price' => number_format(($this->up_price * (float)$this->currency_Data), 2),
                'up_drop_price' => number_format(($this->up_drop_price * (float)$this->currency_Data), 2),
                'main_price' => number_format(($this->price + $this->up_price + $this->drop_price) * (float)$this->currency_Data, 2),
                'total_day' => $this->full_date,
                'day_price' => number_format((($rent_price / $this->full_date) * (float)$this->currency_Data), 2),
                'rent_price' => $rent_price * (float)$this->currency_Data,
                'discount' => $this->discount,
                'discount_price' => $total_price * (float)$this->currency_Data,
                'currency_data' => (float)$this->currency_Data,
                'mandatoryInContract' => $this->mandatoryInContract(),
                'ekstra_including_price' => $ekstra_including_price,
                'this_price' => $this->drop_price,
                'period_process' => $this->peridprocess,
            );
        }
        return $priceArray;
    }

    public static function get_brand_model($brand = null, $model = null)
    {
        $brand = \DB::table('brands')->where('id', $brand)->first();
        $brand = $brand->brandname ?? "Bulunamadı";
        $model = CarModel::find($model);
        $model = \DB::table('car_models')->where('id', $model)->first();
        $model = $model->modelname ?? "Bulunamadı";
        return $brand . " " . $model;
    }

    public static function getImageList($id)
    {
        return Image::where("default", "normal")->where("id_module", $id)->where("module", "cars")->get();
    }
    public function rent_period()
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
}
