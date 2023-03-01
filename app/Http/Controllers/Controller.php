<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Setting;
use App\Repository\Data;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Redis;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function staticData(): array
    {
        $redis = new Redis();
        $redis->connect('localhost', 6379);
        if (!$redis->get('logo')) {
            $data["logo"] = Setting::where("key", "logo")->orderBy("id", "desc")->limit(1)->first()->value;
            $redis->set('logo', json_encode(Setting::where("key", "logo")->orderBy("id", "desc")->limit(1)->first()->value));
        } else {
            $data["logo"] = json_decode($redis->get('logo'));
        }
        $data["favicon"] = Setting::where("key", "favicon")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["title"] = Setting::where("key", "title")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["email"] = Setting::where("key", "email")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["description"] = Setting::where("key", "description")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["850"] = Setting::where("key", "850")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["address1"] = Setting::where("key", "address1")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["phone1"] = Setting::where("key", "phone1")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["phone2"] = Setting::where("key", "phone2")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["countryDiscount"] = Setting::where("key", "outside_price")->orderBy("id", "desc")->limit(1)->first()->value;

        if (!$redis->get('header_advert')) {
            $data["header_advert"] = Setting::where("key", "header_advert")->orderBy("id", "desc")->limit(1)->first()->value;
            $redis->set('header_advert', json_encode(Setting::where("key", "header_advert")->orderBy("id", "desc")->limit(1)->first()->value));
        } else {
            $data["header_advert"] = json_decode($redis->get('header_advert'));
        }

        if (!$redis->get('home_image')) {
            $data["home_image"] = Setting::where("key", "home_image")->orderBy("id", "desc")->limit(1)->first()->value;
            $redis->set('home_image', json_encode(Setting::where("key", "home_image")->orderBy("id", "desc")->limit(1)->first()->value));
        } else {
            $data["home_image"] = json_decode($redis->get('home_image'));
        }

        if (!$redis->get('cars_image')) {
            $data["cars_image"] = Setting::where("key", "cars_image")->orderBy("id", "desc")->limit(1)->first()->value;
            $redis->set('cars_image', json_encode(Setting::where("key", "cars_image")->orderBy("id", "desc")->limit(1)->first()->value));
        } else {
            $data["cars_image"] = json_decode($redis->get('cars_image'));
        }

        $data["card_payment"] = Setting::where("key", "card_payment")->orderBy("id", "desc")->limit(1)->first()->value;
        $data["languages"] = Language::where("view", 1)->orderBy('sort', 'asc')->get();
        return $data;
    }

}
