<?php namespace App\Services;


use App\Contract\Currency;
use Illuminate\Support\Facades\DB;

class CurrencyService implements Currency
{
    protected $currency;


    public function __construct($txt, $quantity = 1)
    {
        $ex = explode("_", $txt);
        $currencyData = \App\Models\Currency::where('left_icon', $ex[1])->first();
        $this->setCurrencyIcon($currencyData->right_icon);
        $this->setCurrency($currencyData->exchange);

    }

    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getCurrencyIcon()
    {
        return $this->currencyIcon;
    }


    public function setCurrencyIcon(string $currencyIcon)
    {
        $this->currencyIcon = $currencyIcon;
    }
}
