<?php

namespace App\Models;


use App\Services\GetData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    protected $tagName = 'Client Information';

    use HasFactory;
    use SoftDeletes;

    const RESERVATION_MAIL = "10";
    const  RESERVATION_MAIL_COMFIRM = "comfirm";
    const  RESERVATION_MAIL_RED = "red";
    const  RESERVATION_MAIL_COMFRM_PLEASE = "comfirm_please";
    const  RESERVATION_MAIL_RESERVATION_EDIT= "reservation_edit";

    const RESERVATION_MAIL_STRING = [
        self::RESERVATION_MAIL => "FIRST MAIL",
        self::RESERVATION_MAIL_COMFIRM => "RESERVASYONUNUZ ONAYLANDI",
        self::RESERVATION_MAIL_RED => "REZERVASYONUNUZ RED EDİLDİ",
        self::RESERVATION_MAIL_COMFRM_PLEASE => "REZERVSYON ONAYLAMA HATIRLATMASI",
        self::RESERVATION_MAIL_RESERVATION_EDIT => "REZERVASYON DÜZENLEME HATIRLATMASI",
    ];

    public function getTrackingStatus()
    {
        return self::RESERVATION_MAIL_STRING[$this->reservation_mail_type];
    }


    public function getPaymentMethod()
    {
        return self::RESERVATION_PAYMENT_STRING[$this->payment_method];
    }

    const RESERVATION_DEBIT_CART = "debit-card";
    const RESERVATION_DELIVERY_DEBIT_CART = "delivery-debit-card";
    const RESERVATION_DELIVERY_CASH = "debit-cash";
    const RESERVATION_ONLINE_CREDIT_CART = "online-credit-card";
    const RESERVATION_ONLINE_CREDIT_NULL = "";


    const RESERVATION_STATUS_WAIT = "waiting";
    const RESERVATION_STATUS_COMFIRM = "comfirm";
    const RESERVATION_STATUS_CLOSED = "closed";
    const RESERVATION_STATUS_COMPLATE = "complated";
    const RESERVATION_STATUS_NOT_FOUNT = "not_found";
    const RESERVATION_STATUS_NEW = "new";

    public const TYPE_TRANSLATIONS = [
        'up_hotel' => Reservation::RESERVATION_UP_HOTEL,
        'drop_hotel' => Reservation::RESERVATION_DROP_HOTEL,
        'drop_center' => Reservation::RESERVATION_DROP_CENTER,
        'drop_address' => Reservation::RESERVATION_DROP_ADDRESS,
        'drop_airport' => Reservation::RESERVATION_DROP_AIRPORT,
        'up_center' => Reservation::RESERVATION_UP_CENTER,
        'up_address' => Reservation::RESERVATION_UP_ADDRESS,
        'up_airport' => Reservation::RESERVATION_UP_AIRPORT,
        'ofis' => Reservation::RESERVATION_UP_OFFICE
    ];


    const  RESERVATION_UP_OFFICE = "OFİS";
    const  RESERVATION_UP_HOTEL = "OTELDE TESLİM EDİLECEK";
    const  RESERVATION_DROP_HOTEL = "OTELDE İADE EDECEK";
    const  RESERVATION_DROP_CENTER = "OFİSTE İADE EDECEK";
    const  RESERVATION_DROP_ADDRESS = "ADRESTE İADE EDECEK";
    const  RESERVATION_DROP_AIRPORT = "HAVALİMANINDA İADE EDECEK";
    const  RESERVATION_UP_CENTER = "OFİSTEN TESLİM ALACAK";
    const  RESERVATION_UP_ADDRESS = "ADRESE TESLİM EDİLECEK";
    const  RESERVATION_UP_AIRPORT = "HAVALİMANINDA TESLİM EDİLECEK";


    public const RESERVATION_PLATE_TYPE = [
        'drop' => "Dönüş",
        'up' => "Çıkış",
    ];


    const RESERVATION_PAYMENT_STRING = [
        self::RESERVATION_DEBIT_CART => "BANKA",
        self::RESERVATION_DELIVERY_DEBIT_CART => "TESLİMATTA KK",
        self::RESERVATION_DELIVERY_CASH => "NAKİT",
        self::RESERVATION_ONLINE_CREDIT_CART => "KK",
        self::RESERVATION_ONLINE_CREDIT_NULL => "",
    ];

    const RESERVATION_PAYMENT_COLOR = [
        self::RESERVATION_DEBIT_CART => "text-red",
        self::RESERVATION_DELIVERY_DEBIT_CART => "text-warning",
        self::RESERVATION_DELIVERY_CASH => "text-cyan-700",
        self::RESERVATION_ONLINE_CREDIT_CART => "text-green",
        self::RESERVATION_ONLINE_CREDIT_NULL => "",
    ];


    const RESERVATION_STATUS_STRING = [
        self::RESERVATION_STATUS_WAIT => "Beklemede",
        self::RESERVATION_STATUS_COMFIRM => "Onaylandı",
        self::RESERVATION_STATUS_CLOSED => "İptal",
        self::RESERVATION_STATUS_COMPLATE => "Tamamlandı",
        self::RESERVATION_STATUS_NOT_FOUNT => "Bulunamadı",
        self::RESERVATION_STATUS_NEW => "Yeni Rezervasyon",
    ];

    const RESERVATION_STATUS_CLASS = [
        self::RESERVATION_STATUS_WAIT => "warning",
        self::RESERVATION_STATUS_COMFIRM => "success",
        self::RESERVATION_STATUS_CLOSED => "danger",
        self::RESERVATION_STATUS_COMPLATE => "primary",
        self::RESERVATION_STATUS_NOT_FOUNT => "warning",
        self::RESERVATION_STATUS_NEW => "success",
    ];

    const RESERVATION_STATUS_COLOR = [
        self::RESERVATION_STATUS_WAIT => "warning",
        self::RESERVATION_STATUS_COMFIRM => "#009a3d",
        self::RESERVATION_STATUS_CLOSED => "#f00",
        self::RESERVATION_STATUS_COMPLATE => "#065991",
        self::RESERVATION_STATUS_NOT_FOUNT => "#ff9d00",
        self::RESERVATION_STATUS_NEW => "#0084b6",
    ];


    const RESERVATION_SOURCE_STRING = [
        self::RESERVATION_SOURCE_PHONE => "TELEFON",
        self::RESERVATION_SOURCE_WHATSAPP => "WHATSAPP",
        self::RESERVATION_STATUS_FACEBOOK => "FACEBOOK",
        self::RESERVATION_STATUS_INSTAGRAM => "INSTAGRAM",
        self::RESERVATION_STATUS_GOOGLE => "GOOGLE",
        self::RESERVATION_STATUS_RECOMMENDS => "ÖNERİ",
        self::RESERVATION_STATUS_NULL => "",
    ];


    const RESERVATION_SOURCE_PHONE = "phone";
    const RESERVATION_SOURCE_WHATSAPP = "whatsapp";
    const RESERVATION_STATUS_FACEBOOK = "facebook";
    const RESERVATION_STATUS_INSTAGRAM = "instagram";
    const RESERVATION_STATUS_GOOGLE = "google";
    const RESERVATION_STATUS_RECOMMENDS = "recommends";
    const RESERVATION_STATUS_NULL = "";


    const RESERVATION_LOG_TYPE_STATUS_CHANGE = "statuschange";
    const RESERVATION_LOG_TYPE_DAY_CHANGE = "daychange";
    const RESERVATION_LOG_TYPE_PLATE_ADD = "plateadd";
    const RESERVATION_LOG_TYPE_PLATE_CHANGE = "platechange";
    const RESERVATION_LOG_TYPE_PRICE_CHANGE = "pricechange";
    const RESERVATION_LOG_TYPE_UP_APPLY = "upapply";
    const RESERVATION_LOG_TYPE_DROP_APPLY = "dropapply";
    const RESERVATION_LOG_TYPE_MAIL_SEND = "mailsend";
    const RESERVATION_LOG_TYPE_REST_CHANGE = "restchange";
    const RESERVATION_LOG_TYPE_DETAIL_CHANGE = "changedetail";
    const RESERVATION_LOG_TYPE_SETTING_CHANGE = "changereservationsetting";
    const RESERVATION_LOG_TYPE_CURRENCY_CHANGE = "changecurrency";
    const RESERVATION_LOG_TYPE_EKSTRA_CHANGE = "changeekstra";
    const RESERVATION_LOG_TYPE_NEW_RESERVATION = "newreservation";
    const RESERVATION_LOG_TYPE_NEW_OPERATION = "operation";
    const RESERVATION_LOG_TYPE_OPERATION_UP = "operationup";
    const RESERVATION_LOG_TYPE_OPERATION_DROP = "operationdrop";

    const RESERVATION_LOG_TYPE_STRING = [
        self::RESERVATION_LOG_TYPE_STATUS_CHANGE => "Durum Değişikliği",
        self::RESERVATION_LOG_TYPE_DAY_CHANGE => "Gün Değiştirildi",
        self::RESERVATION_LOG_TYPE_PLATE_ADD => "Plaka Atandı",
        self::RESERVATION_LOG_TYPE_PLATE_CHANGE => "Plaka Değiştirildi",
        self::RESERVATION_LOG_TYPE_PRICE_CHANGE => "Fiyat Değiştirildi",
        self::RESERVATION_LOG_TYPE_UP_APPLY => "Dönüş Yapıldı",
        self::RESERVATION_LOG_TYPE_DROP_APPLY => "Çıkış Yapıldı",
        self::RESERVATION_LOG_TYPE_MAIL_SEND => "Mail Gönderildi",
        self::RESERVATION_LOG_TYPE_REST_CHANGE => "Hesap Tamamlandı",
        self::RESERVATION_LOG_TYPE_DETAIL_CHANGE => "Rezervasyon Teslimat Bilgileri Güncellendi",
        self::RESERVATION_LOG_TYPE_SETTING_CHANGE => "Rezervasyon checkin checkout trarihleri değiştirildi",
        self::RESERVATION_LOG_TYPE_CURRENCY_CHANGE => "Kur Değiştirildi",
        self::RESERVATION_LOG_TYPE_EKSTRA_CHANGE => "Ekstra Değiştirildi",
        self::RESERVATION_LOG_TYPE_NEW_RESERVATION => "Yeni Rezervasyon",
        self::RESERVATION_LOG_TYPE_NEW_OPERATION => "Operasyon İşlemi Yapıldı",
        self::RESERVATION_LOG_TYPE_OPERATION_UP => "Dönüş İşlemi yapıldı",
        self::RESERVATION_LOG_TYPE_OPERATION_DROP => "Çıkış İşlemi Yapıldı",
    ];



    protected $data;

    public function __construct()
    {
        $this->data = new GetData();
    }

    public function getPaymentStatus()
    {
        return self::RESERVATION_PAYMENT_STRING[$this->payment_method];
    }

    public function getReservationStatus()
    {
        return self::RESERVATION_STATUS_STRING[$this->status];
    }

    public function getReservationStatusClass()
    {
        return self::RESERVATION_STATUS_CLASS[$this->status ?? "not_found"];
    }

    public function reservationInformation()
    {
        return $this->belongsTo(ReservationInformation::class, 'id', 'id_reservation');
    }

    public function reservationCar()
    {
        return $this->hasOne(Car::class, 'id', 'car');
    }

    public function reservationCarNew()
    {
        if(is_null($this->plate)) {
            return Car::where('id',$this->car)->first();
        }else{
            $plate = Plate::where('id', $this->plate)->first();
            return Car::where('id',$plate->id_car)->first();
        }

    }

    public function reservationNotes()
    {
        return $this->belongsTo(ReservationNote::class, 'id', 'id_reservation');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id');
    }

    public function reservationEkstras()
    {
        return $this->hasMany(ReservationEkstra::class, 'id_reservation');
    }

    public function reservationInvoice()
    {
        return $this->hasMany(ReservationInvoice::class, 'id_reservation', 'id');
    }

    public function reservationPayment()
    {
        return $this->hasMany(ReservationPayment::class, 'id_reservation', 'id');
    }

    public function reservationCurrency()
    {
        return $this->belongsTo(Currency::class, 'id_currency', 'id');
    }

    public function reservationSurvey()
    {
        return $this->hasOne(ReservationSurvey::class, 'id_reservation', 'id');
    }

    public function getPlate()
    {
        return $this->belongsTo(Plate::class, 'plate', 'id');
    }

    public function reservationLog()
    {
        return $this->hasMany(ReservationLog::class, 'id_reservation', 'id');
    }

    public function reservationPlate()
    {
        return $this->hasMany(ReservationPlate::class, 'id_reservation', 'id');
    }

    public function reservationPlateList()
    {
        $reservationplate = ReservationPlate::where('id_reservation', $this->id);
        if ($reservationplate->first() != null) {
            $reservationplatedesc = ReservationPlate::where('id_reservation', $this->id)->orderBy('id', 'desc')->first();
            $reservationplateasc = ReservationPlate::where('id_reservation', $this->id)->orderBy('id', 'asc')->first();
            return Plate::find($reservationplateasc->id_plate)->plate . " > " . Plate::find($reservationplatedesc->id_plate)->plate;
        } else {
            return "Atanmadı";
        }
    }

    public function plateDiff()
    {
        if ($this->car != Plate::find($this->plate)->id_car) {
            return $this->data->getCarInfoFullNoYear($this->car)  ." \n Farklı Araç Verildi";
        }
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

    public function current()
    {
        return $this->belongsTo(Currency::class, 'id_currency', 'id');
    }

    public function reservationEmail()
    {
        return $this->belongsTo(ReservationEmail::class, 'id_reservation', 'id');
    }

    public function reservationOperation()
    {
        return $this->belongsTo(ReservationOperation::class, 'id_reservation', 'id');
    }


    public function reservationRest()
    {
        return ReservationRest::where('id_reservation',$this->id)->get();
    }

    public function tdcolor()
    {
        $reservationoperation = ReservationOperation::whereIn('type', array('up', 'drop'))->where('id_reservation', $this->id)->get();

        if ($reservationoperation) {
            if (count($reservationoperation) == 2) {
                return "background-color:#4682B4;color:#fff";
            } else if (count($reservationoperation) == 1) {
                return "background-color:#4682B4;color:#fff";
            } else if (count($reservationoperation) > 2) {
                return "background-color:#4682B4;color:#fff";
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    function private_str()
    {
        $start = 0;
        $end = 0;
        $reservation = Reservation::find($this->id);
        if ($reservation) {

            $customer = Customer::find($reservation->id_customer);

            if($reservation->reservationInformation)
            {
                if(
                    !empty($reservation->reservationInformation->email)
                    and
                    trim($reservation->reservationInformation->email) == trim($customer->email)
                    and
                    trim($reservation->reservationInformation->phone) == trim($customer->phone)
                    and
                    trim($reservation->reservationInformation->firstname) != trim($customer->firstname)
                    and
                    trim($reservation->reservationInformation->lastname) != trim($customer->lastname)
                )
                {
                    if($reservation->reservationInformation->firstname != null)
                    {
                        $str = $reservation->reservationInformation->firstname." ".$reservation->reservationInformation->lastname;

                    }else{
                        $str = $reservation->customer->firstname." ".$reservation->customer->lastname;
                    }
                    $after = mb_substr($str, 0, $start, 'utf8');
                    $repeat = str_repeat('*', $end);
                    $before = mb_substr($str, ($start + $end), strlen($str), 'utf8');
                    return $after . $repeat . $before.'(Birleştir)';
                }else{
                    if($reservation->reservationInformation->firstname !=  null)
                    {
                        $str = $reservation->reservationInformation->firstname." ".$reservation->reservationInformation->lastname;

                    }else{
                        $str = $reservation->customer->firstname." ".$reservation->customer->lastname;
                    }
                    $after = mb_substr($str, 0, $start, 'utf8');
                    $repeat = str_repeat('*', $end);
                    $before = mb_substr($str, ($start + $end), strlen($str), 'utf8');
                    return $after . $repeat . $before;
                }

            }

        }

    }

    public function reservationCount()
    {
        $reservation = Reservation::find($this->id);
        return Reservation::where('id_customer', $reservation->id_customer)->count();
    }

    public function reservationOperationUp()
    {
        return ReservationOperation::where('id_reservation', $this->id)->where('type', 'up')->first();
    }

    public function reservationOperationDrop()
    {
        return ReservationOperation::where('id_reservation', $this->id)->where('type', 'drop')->first();
    }


    public function reservationPlateText()
    {
        return Plate::where('id', $this->plate)->first();
    }

    public function uplocationName()
    {
        return $this->data->getLocationTitle($this->up_location,$this->id_language);
    }
    public function droplocationName()
    {
        return $this->data->getLocationTitle($this->drop_location,$this->id_language);
    }

    public function fullname()
    {
        if($this->reservationInformation)
        {
            return $this->customer->fullname;
        }else{
            return $this->reservationInformation->firstname." ".$this->reservationInformation->lastname;
        }
    }
    public function statusColor()
    {
        if($this->status == Reservation::RESERVATION_STATUS_CLOSED)
        {
            return "#f00";
        }else if($this->status == Reservation::RESERVATION_STATUS_NEW)
        {
            return "#00ff24";
        }else if($this->status == Reservation::RESERVATION_STATUS_COMFIRM)
        {
            return "#4dbb5f";
        }else if($this->status == Reservation::RESERVATION_STATUS_COMPLATE)
        {
            return "#097cb5";
        }else if($this->status == Reservation::RESERVATION_STATUS_WAIT)
        {
            return "#fec135";
        }else if($this->status == Reservation::RESERVATION_STATUS_WAIT)
        {
            return "#fff";
        }
    }



}

