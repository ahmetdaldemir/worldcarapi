<?php


namespace App\Services;


use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\EmailTemplate;
use App\Models\Language;
use App\Models\Location;
use App\Models\LocationValue;
use App\Models\TransferZoneFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetData
{

    protected $ekstraCalculate;

    public function __construct()
    {
        $this->ekstraCalculate =  new EkstraCalculate();
    }

    public static function getLocationTitle($id_location, $id_lang)
    {
        $x = LocationValue::where("id_location", $id_location)->where("id_lang", $id_lang)->get();
        foreach ($x as $item) {
            return $item->title ?? null;
        }
    }


    public static function getLocationMeta($id_location, $id_lang)
    {
        $x = LocationValue::where("id_location", $id_location)->where("id_lang", $id_lang)->get();
        foreach ($x as $item) {
            return $item->meta_title ?? null;
        }
    }

    public static function getCityAllHtml()
    {
        $a = "";
        $city = DB::table("city")->get();
        foreach ($city as $val) {
            $a .= '<option value="' . $val->id . '">' . $val->name . '</option>';
        }
        return $a;
    }

    public static function getCityAll()
    {
        $city = DB::table("city")->get();
        foreach ($city as $val) {
            $data[] = array(
                'id_city' => $val->id,
                'name_city' => $val->name,
                'code_city' => $val->code,
            );
        }
        echo json_encode($data);
    }

    public function calculateEkstra($ekstra, $days,$currencyExchange)
    {
        $x = 0;
        foreach ($ekstra as $key => $value) {
            $x += $this->ekstraCalculate->getDataForCalculate($key, $days, $value[0],$currencyExchange);
        }
        return $x;
    }


    public static function getEmailTitle($itemId,$languageId)
    {
        $emailtemplate = EmailTemplate::where('email_template_id',$itemId)->where('language_id',$languageId)->first();
        if($emailtemplate)
        {
            return $emailtemplate->title;
        }
    }

    public static function getEmailTemplate($itemId,$languageId)
    {
        $emailtemplate = EmailTemplate::where('email_template_id',$itemId)->where('language_id',$languageId)->first();
        if($emailtemplate)
        {
            return $emailtemplate->template;
        }
    }


    public function getDropLocation(Request $request,$langId = "tr")
    {
        $location = array();
        $langId = Language::where("url", $langId)->first()->id;
        if ($request->id == 0) {
            $locations = Location::where("id_parent", 0)->orderBy('sort',"asc")->get();
            foreach ($locations as $item) {
                $locatiValue = LocationValue::where("id_location", $item->id)->where("id_lang", $langId)->first();
                $location[] = array(
                    'id' => $item->id,
                    'title' => $locatiValue->title,
                    'id_parent' => $item->id_parent,
                    'type' => $locatiValue->type,
                    'parentList' => Location::join('location_values', 'locations.id', '=', 'location_values.id_location')->select('locations.*', 'location_values.title')->where('locations.id_parent', $item->id)->where('location_values.id_lang', $langId)->orderBy('locations.id',"asc")->get(),
                );

            }
        } else {
            $id_location = Location::find($request->id)->id_parent;
            $transferLocation = TransferZoneFee::where("id_location", $id_location)->where("status", 1)->orderBy('id_location_transfer_zone','asc')->get();
            foreach ($transferLocation as $item) {
                $locatiValue = LocationValue::where("id_location", $item->id_location_transfer_zone)->where("id_lang", $langId)->first();
                $location[] = array(
                    'id' => $item->id_location_transfer_zone,
                    'title' => $locatiValue->title,
                    'id_parent' => Location::find($item->id_location_transfer_zone)->id_parent,
                    'type' => $locatiValue->type,
                    'parentList' => Location::join('location_values', 'locations.id', '=', 'location_values.id_location')->select('locations.*', 'location_values.title')->where('locations.id_parent', $item->id_location_transfer_zone)->where('location_values.id_lang', $langId)->orderBy('locations.id',"asc")->get(),
                );
            }
        }
        return $location;
    }


    public function droplocation(Request $request,$langId = "tr")
    {
        $location = array();
        $langId = Language::where("url", $langId)->first()->id;
        if ($request->id == 0) {
            $locations = Location::where("id_parent", 0)->orderBy('sort',"asc")->get();
            foreach ($locations as $item) {
                $locatiValue = LocationValue::where("id_location", $item->id)->where("id_lang", $langId)->first();
                $location[] = array(
                    'id' => $item->id,
                    'title' => $locatiValue->title,
                    'id_parent' => $item->id_parent,
                    'type' => $locatiValue->type,
                    'parentList' => Location::join('location_values', 'locations.id', '=', 'location_values.id_location')->select('locations.*', 'location_values.title')->where('locations.id_parent', $item->id)->where('location_values.id_lang', $langId)->orderBy('locations.id',"asc")->get(),
                );

            }
        } else {
            $id_location = Location::find($request->id)->id_parent;
            $transferLocation = TransferZoneFee::where("id_location", $id_location)->where("status", 1)->orderBy('id_location_transfer_zone','asc')->get();
            foreach ($transferLocation as $item) {
                $locatiValue = LocationValue::where("id_location", $item->id_location_transfer_zone)->where("id_lang", $langId)->first();
                $location[] = array(
                    'id' => $item->id_location_transfer_zone,
                    'title' => $locatiValue->title,
                    'id_parent' => Location::find($item->id_location_transfer_zone)->id_parent,
                    'type' => $locatiValue->type,
                    'parentList' => Location::join('location_values', 'locations.id', '=', 'location_values.id_location')->select('locations.*', 'location_values.title')->where('locations.id_parent', $item->id_location_transfer_zone)->where('location_values.id_lang', $langId)->orderBy('locations.id',"asc")->get(),
                );
            }
        }
        return $location;
    }
    public static function getCarInfoFullNoYear($id_car)
    {

        $car = Car::find($id_car);
        $brand = @Brand::find($car->brand)->brandname ?? null;
        $model = @CarModel::find($car->model)->modelname ?? null;
        return $brand . " " . $model;
    }

}
