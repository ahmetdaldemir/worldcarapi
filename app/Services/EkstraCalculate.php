<?php namespace App\Services;


use App\Models\Ekstra;
use Carbon\Carbon;

class EkstraCalculate
{

    protected $ekstra;
    protected $reservationdata;
    protected $totalday;


    public function __construct($id = "", $totalprice = "")
    {
        $this->ekstra = Ekstra::find($id);
//        $this->totalday = ($this->date_Difference() +  $this->time_Difference());
        $this->reservationprice = $totalprice;
        $this->customecalculate = new CustomCalculate();
    }


    public function calculate()
    {
        if ($this->ekstra->sellType == "daily") {
            return ($this->ekstra->price * $this->totalday) + $this->reservationprice;
        } else {
            return $this->ekstra->price + $this->reservationprice;
        }
    }


    public function date_Difference(): int
    {
        $start = Carbon::parse($this->reservationdata->up_date);
        $end = Carbon::parse($this->reservationdata->down_date);
        return $end->diffInDays($start);
    }

    public function time_Difference(): int
    {
        $baslangic = new DateTime($this->reservationdata->up_time);
        $fark = $baslangic->diff(new DateTime($this->reservationdata->down_time));
        $diff = $fark->h;
        if ($diff > 2) {
            return 1;
        } else {
            return 0;
        }
    }


    public function getDataForCalculate($id_ekstra, $day, $value,$currencyExchange)
    {
        $ekstra = Ekstra::find($id_ekstra);
        if ($ekstra->sellType == "daily") {
            return ($ekstra->price * $day) * $value * $currencyExchange;
        } else {
            return $ekstra->price * $value * $currencyExchange;
        }
    }


    public function customCalculate($id_ekstra, $day, $value)
    {
        $ekstra = Ekstra::find($id_ekstra);
        if ($ekstra->mandatoryInContract == 'yes') {
            if ($ekstra->sellType == "daily") {
                return ($ekstra->price * $day) * $value;
            } else {
                return $ekstra->price * $value;
            }
        }
    }
    public function ekstraAll($reservationdata, $currencyData)
    {
        $datediff = $this->customecalculate->customDifference($reservationdata['cikis_tarihi_submit'], $reservationdata['donus_tarihi_submit']);
        $timediff = $this->customecalculate->timeDifference($reservationdata['cikis_saati_submit'], $reservationdata['donus_saati_submit']);
        $days = $datediff + $timediff;
        $ekstras = Ekstra::where('status',1)->get();
        foreach ($ekstras as $item) {
            $data[] = array(
                'id' => $item->id,
                'currency' => $currencyData,
                'price' => $item->price * $currencyData,
                'image' => $item->image,
                'mandatoryInContract' => $item->mandatoryInContract,
                'itemOfCustom' => $item->itemOfCustom,
                'value' => $item->value,
                'type' => $item->type,
                'sellType' => $item->sellType,
                'totalPrice' => $this->customCalculate($item->id, $days, $currencyData),
            );
        }
        return $data;
    }


    public function reservationEkstraCalculate($id_ekstra, $day, $value)
    {
        $ekstra = Ekstra::find($id_ekstra);
        if ($ekstra->sellType == "daily") {
            return ($ekstra->price * $day) * $value;
        } else {
           return $ekstra->price * $value;
        }
    }

}
