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

    public function calculateTotalPaidRevenue()
    {
        // paid reservations (those with payments)
        $paidReservations = Reservation::query()
            ->join('paiements', 'reservations.id', '=', 'paiements.id_reservation')
            ->join('espaces', 'reservations.id_espace', '=', 'espaces.id')
            ->select(DB::raw('SUM(espaces.hour_price * reservations.hour_duration) as total'))
            ->first()
            ->total ?? 0;

        // options revenue for paid reservations
        $paidOptionsRevenue = Reservation::query()
            ->join('paiements', 'reservations.id', '=', 'paiements.id_reservation')
            ->join('reservation_options', 'reservations.id', '=', 'reservation_options.id_reservation')
            ->join('options', 'reservation_options.id_option', '=', 'options.id')
            ->sum('options.price');

        return $paidReservations + $paidOptionsRevenue;
    }

    public function calculateTotalUnpaidRevenue()
    {
        // unpaid reservations (those without payments)
        $unpaidReservations = Reservation::query()
            ->leftJoin('paiements', 'reservations.id', '=', 'paiements.id_reservation')
            ->join('espaces', 'reservations.id_espace', '=', 'espaces.id')
            ->whereNull('paiements.id')
            ->select(DB::raw('SUM(espaces.hour_price * reservations.hour_duration) as total'))
            ->first()
            ->total ?? 0;

        // options revenue for unpaid reservations
        $unpaidOptionsRevenue = Reservation::query()
            ->leftJoin('paiements', 'reservations.id', '=', 'paiements.id_reservation')
            ->join('reservation_options', 'reservations.id', '=', 'reservation_options.id_reservation')
            ->join('options', 'reservation_options.id_option', '=', 'options.id')
            ->whereNull('paiements.id')
            ->sum('options.price');

        return $unpaidReservations + $unpaidOptionsRevenue;
    }

    public function getTopTimeSlots($limit)
    {
        return Reservation::query()
            ->select([
                DB::raw('EXTRACT(HOUR FROM datetime_reservation) as hour'),
                DB::raw('COUNT(*) as reservation_count')
            ])
            ->groupBy('hour')
            ->orderBy('reservation_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($slot) {
                return [
                    'time' => sprintf('%02d:00', $slot->hour),
                    'count' => $slot->reservation_count
                ];
            });
    }
}