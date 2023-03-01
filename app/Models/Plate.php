<?php

namespace App\Models;

class Plate
{
    const PLATE_STATUS_FAULT = "10";
    const PLATE_STATUS_CRASHED = "20";
    const PLATE_STATUS_ARCHIVE = "30";
    const PLATE_STATUS_FREE = "40";
    const PLATE_STATUS_BUSY = "50";

    const PLATE_STATUS_STRING = [
        self::PLATE_STATUS_FAULT => "BAKIMDA",
        self::PLATE_STATUS_CRASHED => "KAZALI",
        self::PLATE_STATUS_ARCHIVE => "ARŞİV",
        self::PLATE_STATUS_FREE => "MÜSAİT",
        self::PLATE_STATUS_BUSY => "KİRADA",
    ];

    public function getPlateStatus()
    {
        return self::PLATE_STATUS_STRING[$this->status];
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'id_car', 'id')->orderBy('brand', 'asc');
    }

    public function takeout(): bool
    {
        return false;
    }

    public function plate_document()
    {
        return $this->hasMany(PlateDocument::class, 'id_plate', 'id');
    }

    public function reservation()
    {
        $data = [];
        $reservation = Reservation::where('plate', $this->id)->orderBy('id', 'desc')->first();
        if ($reservation) {
            return array(
                'drop_location' => LocationValue::where('id_location', $reservation->drop_location)->where('id_lang', 1)->first()->title,
                'up_location' => LocationValue::where('id_location', $reservation->up_location)->where('id_lang', 1)->first()->title,
                'checkin' => $reservation->checkin,
                'pnr' => $reservation->pnr,
                'checkout' => $reservation->checkout,
            );
        }

    }


    public function get_last_plate()
    {
        $plate = Reservation::where('plate', $this->id)->orderBy('id', 'DESC')->first();
        if ($plate) {
            return Location::getViewLocationName($plate->drop_location) ?? "bulunamadı";
        } else {
            return "-";
        }
    }


    public function getDropData()
    {
        $data = array(
            'checkin' => 'Müsait',
            'checkout' => 'Müsait',
            'droplocation' => 'Müsait',
            'checkouttime' => '-',
            'checkintime' => '-'
        );
        $location = new Location();
        $reservation = Reservation::where('plate', $this->id)->whereNotNull('drop_date')->orderByDesc('id')->first();
        if ($reservation) {
            $data = array(
                'checkin' => date('d-m-Y', strtotime($reservation->checkin)),
                'checkintime' => date('H:i', strtotime($reservation->checkin_time)),
                'checkout' => date('d-m-Y', strtotime($reservation->checkout)),
                'checkouttime' => date('H:i', strtotime($reservation->checkout_time)),
                'droplocation' => $location->getViewLocationName($reservation->drop_location)->sort ?? "Bulunamadı",
            );
        }

        return $data;
    }
}
