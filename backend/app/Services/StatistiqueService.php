<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatistiqueService
{
    public function calculateRevenueBetweenDates($minDate, $maxDate)
    {
        $minDate = $minDate instanceof Carbon ? $minDate : Carbon::parse($minDate);
        $maxDate = $maxDate instanceof Carbon ? $maxDate : Carbon::parse($maxDate);

        // base revenue from reservations (spaces)
        $spaceRevenue = Reservation::query()
            ->join('espaces', 'reservations.id_espace', '=', 'espaces.id')
            ->whereBetween('datetime_reservation', [$minDate, $maxDate])
            ->select(DB::raw('SUM(espaces.hour_price * reservations.hour_duration) as total'))
            ->first()
            ->total ?? 0;

        // additional revenue from options
        $optionsRevenue = Reservation::query()
            ->join('reservation_options', 'reservations.id', '=', 'reservation_options.id_reservation')
            ->join('options', 'reservation_options.id_option', '=', 'options.id')
            ->whereBetween('reservations.datetime_reservation', [$minDate, $maxDate])
            ->sum('options.price');

        return $spaceRevenue + $optionsRevenue;
    }
}