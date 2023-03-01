<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Plate;
use App\Models\Reservation;
use App\Models\ReservationOperation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class OperationController extends Controller
{

    public function index(Request $request)
    {

        $reservationarray = [];

        if ($request->has('closed')) {
            $query = Reservation::where('test', 1);
        }else{
            $query = Reservation::whereNull('deleted_at')->where("status" ,'!=', "closed")->where('test', 1);
        }


        if ($request->type == "1") {
            if ($request->has('checkin')) {
                $query->whereBetween('checkin', [Carbon::parse($request->checkin)->format('Y-m-d'), Carbon::parse($request->checkout)->format('Y-m-d')])->orderBy('checkin', 'asc')->orderBy('checkin_time', 'asc');
            }
        }
        if ($request->type == "2") {
            if ($request->has('checkout')) {
                $query->whereNotNull('drop_date')->whereBetween('checkout', [Carbon::parse($request->checkin)->format('Y-m-d'), Carbon::parse($request->checkout)->format('Y-m-d')])->orderBy('checkout', 'asc')->orderBy('checkout_time', 'asc');
            }
        }

        if ($request->has('payment_method') && !empty($request->payment_method) &&  $request->payment_method != 0) {
            $query->where('payment_method', '=', $request->payment_method);
        }

        if ($request->has('reservation_status') && !is_null($request->reservation_status) &&  $request->reservation_status != 0) {
            $query->where('status', '=', $request->reservation_status);
        }



        if ($request->plate_type != 2) {
            if ($request->plate_type != 0) {
                $query->whereNotNull('plate');
            } else {
                $query->whereNull('plate');
            }
        }


        if ($request->plates && !empty($request->plates)) {
            $query->where('plate', '=', $request->plates);
        }


        if ($request->car && !empty($request->car)) {
            $query->where('car', '=', $request->car);
        }


        if ($request->city && !empty($request->city)) {
            $id_parent = [];
            $gets = $query->get();
            $location = Location::where('id_parent',$request->city)->pluck('id');
            if ($request->type == "1") {
                $query->whereIn('up_location',  $location);
            }

            if ($request->type == "2") {
                $query->whereIn('drop_location',  $location);
            }
        }


        $x = $query->get();
        foreach ($x as $item) {
            Log::info('id/'.$item->id);
            if ($item->plate == null) {
                $carinfo = $item->reservationCar->brandfunction->brandname ." ".  $item->reservationCar->modelfunction->modelname;
                $carinfodetail =  $item->reservationCar->fuel . " " . $item->reservationCar->transmission;
            } else {
                $carinfo = $item->getPlate->car->brandfunction->brandname . " " . $item->getPlate->car->modelfunction->modelname;
                $carinfodetail = $item->getPlate->car->fuel . " " . $item->getPlate->car->transmission;
            }
            $reservationarray[] = array(
                'id' => $item->id,
                'pnr' => $item->pnr,
                'id_reservation' => $item->id,
                'type' => $request->type,
                'km' => 1,
                'files' =>1,
                'fuel'        => 1,
                'id_user'      => $request->user_id,
                'deleted_at' => $item->deleted_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'customer_fullname'   => $item->customer->fullname,
                'customer_phone'      => $item->customer->phone,
                'customer_id'      => $item->customer->id,
                'car_info'            => $carinfo,
                'car_info_detail'     => $carinfodetail,
                'checkin'             => Carbon::parse($item->checkin)->format('d-m-Y'),
                'checkout'            => Carbon::parse($item->checkout)->format('d-m-Y'),
                'checkintime'         => Carbon::parse($item->checkin_time)->format('H:i'),
                'checkouttime'        => Carbon::parse($item->checkout_time)->format('H:i'),
                'day_price'           => $item->day_price,
                'ekstras'             => $item->reservationEkstras,
                'days'                => $item->days,
                'drop_date'           => $item->drop_date,
                'up_date'             => $item->up_date,
                'ekstra_price'        => $item->ekstra_price,
                'comfirm_date'        => $item->comfirm_date,
                'total_amount'        => $item->total_amount,
                'rest'                => $item->rest,
                'location'            => $this->getLocationDetail($item->id) ?? "0",
                'up_location'         => $item->up_location,
                'id_currency'         => $item->id_currency,
                'currency_icon'       => Currency::find($item->id_currency)->right_icon,
                'payment_method'      => $item->getPaymentMethod(),
                'up_location_name'    => $item->uplocationName(),
                'drop_location_name'  => $item->droplocationName(),
                'note' => $item->reservationNotes,
                'reservationStatus' => Reservation::RESERVATION_STATUS_STRING[$item->status??"not_found"],
                'reservationStatusClass' => $item->getReservationStatusClass(),
                'reservationInfo' => $item->reservationInformation,
                'reservationInfoUp' => json_decode($item->reservationInformation->up_drop_information ?? null, TRUE),
                'tdcolor' => $this->tdcolor($item->id,$request->type),
                'is_letter' => $this->islettetcolor($item->is_letter),
                'plate' => $item->plate ? $item->getPlate->plate : "Atanmadı" ,
                'warning_color' => Reservation::RESERVATION_STATUS_COLOR[$item->status],
                'delivery_personal' =>  $item->reservationOperationDrop() ?? "0",
                'drop_personal' => $item->reservationOperationUp() ?? "0",
                'reservationrest' => $item->reservationRest(),
                'reservationcount' => $item->reservationCount(),

            );
        }

        return  response($reservationarray,200);

    }
    public function getLocationDetail($id)
    {
        $data = [];
        $item = Reservation::find($id);
        $reservationinformation = $item->reservationInformation->up_drop_information ?? null;
        $details = json_decode($reservationinformation, true);

        if($details && !is_null($details))
        {
            $data = array(
                'up_location'   =>   Location::getViewCenterId($item->reservationInformation->up_location ?? null)[0]->title   ?? null,
                'drop_location' =>   Location::getViewCenterId($item->reservationInformation->drop_location ?? null)[0]->title ?? null,
                'up_type'       =>   Reservation::TYPE_TRANSLATIONS[$details['up']['type'] ?? Reservation::TYPE_TRANSLATIONS['up']],
                'drop_type'     =>   Reservation::TYPE_TRANSLATIONS[$details['drop']['type'] ?? Reservation::TYPE_TRANSLATIONS['drop']]
            );
        }else{
            $data = array(
                'up_location'   =>   Location::getViewCenterId($item->reservationInformation->up_location ?? null)[0]->title   ?? null,
                'drop_location' =>   Location::getViewCenterId($item->reservationInformation->drop_location ?? null)[0]->title ?? null,
                'up_type'       =>   "Bulunamadı",
                'drop_type'     =>   "Bulunamadı"
            );
        }

        return $data;

        //  'uplocationName' => Location::getViewLocationName($item->up_location),
        // 'droplocationName' => Location::getViewLocationName($item->drop_location),
    }
    public function tdcolor($id,$type = null)
    {
        if($type == 'in')
        {
            $reservationoperation = ReservationOperation::where('type','drop')->where('id_reservation', $id)->first();
            if ($reservationoperation) {
                return "background-color:#ff9900;color:#fff";
            }else{
                return "background-color:#ffffcc;color:#000";
            }
        }else if($type == 'out')
        {
            $reservationoperation = ReservationOperation::where('type', 'up')->where('id_reservation', $id)->first();
            if ($reservationoperation) {
                return "background-color:#ffcc00;color:#fff";
            }else{
                return "background-color:#ffffcc;color:#000";
            }
        }
    }

    public function islettetcolor($veriable)
    {
        if ($veriable == 1) {
            return "background-color:#f8902c;";
        } else {
            return "background-color:#f00;";
        }
    }


    public function save(Request $request)
    {
        $imagename = [];
        $reservationoperationresponse = ReservationOperation::where('id_reservation', $request->id)->get();
        if ($reservationoperationresponse) {
            $drop = $reservationoperationresponse->where('type', 'drop')->first();
            if (!$drop && $request->type == 'up') {
                return redirect()->back()->withErrors(['msg' => $request->id . " - Çıkış Yapılmamış"]);
            }
            $check = $reservationoperationresponse->where('type', $request->type)->first();
            if ($check) {
                return redirect()->back()->withErrors(['msg' => $request->id . " - Çıkış Dönüş Yapıldı"]);
            }
        }
        $reservation = Reservation::find($request->id);

        if (is_null($reservation)) {
            return redirect()->back()->withErrors(['msg' => $request->id . " - Rezervasyon Bulunamadı"]);
        }
        if ($reservation->plate == null) {
            return redirect()->back()->withErrors(['msg' => $request->id . " - Plaka Atanmamış"]);
        }

        $plate = Plate::find($reservation->plate);
        if (!is_null($reservation->plate)) {
            if ($request->file('file') != "") {

                foreach ($request->file('file') as $file) {
                    $name = rand(1, 99999) . "_" . time() . '.' . $file->extension();
                    $file->move(storage_path('app/public/reservation_history'), $name);
                    $imagename[] = $name;
                }
            }

            $reservationoperation = new ReservationOperation();
            $reservationoperation->id_reservation = $request->id;
            $reservationoperation->type = $request->type;
            $reservationoperation->km = $request->km;
            $reservationoperation->fuel = $request->fuel;
            $reservationoperation->files = json_encode($imagename);
            $reservationoperation->id_user = $request->id_user;
            $reservationoperation->save();


            if ($request->type == 'up') {
                $plate->status = Plate::PLATE_STATUS_FREE;
                $plate->oil_km_current = $request->km;
                $plate->save();

                $reservation->up_date = Carbon::now()->format('Y-m-d');
                $reservation->status = Reservation::RESERVATION_STATUS_COMPLATE;
                $reservation->save();

                $operationtype = "operationup";

                if (filter_var($reservation->customer->email, FILTER_VALIDATE_EMAIL)) {
                    $this->dispatch(new \App\Jobs\Survey($reservation));
                }

            }

            if ($request->type == 'drop') {
                $plate->status = Plate::PLATE_STATUS_BUSY;
                $plate->save();

                $reservation->drop_date = Carbon::now()->format('Y-m-d');
                $reservation->save();

                $operationtype = "operationdrop";
            }


            $log = [
                'Başlık' => "Operasyon İşlemi Yapıldı",
                'Tip' => $operationtype,
                'Km' => $request->km,
                'Fuel' => $request->fuel,
            ];
            $this->saveLogReservation($operationtype, $reservation->id, $log);

        }

        if ($request->type == "up") {
            $type = "Dönüş İşlemi Yapıldı";
        } else {
            $type = "Çıkış İşlemi Yapıldı";
        }

        return redirect()->back()->withErrors(['msg' => $type]);;
    }
}
