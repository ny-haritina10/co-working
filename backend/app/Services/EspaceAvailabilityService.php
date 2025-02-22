<?php


namespace App\Services;

use App\Models\Espace;
use App\Models\Reservation;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EspaceAvailabilityService
{
    private const OPENING_HOUR = 8;
    private const CLOSING_HOUR = 18;
    
    public function getDailyAvailability(Carbon $date): array
    {
        $espaces = Espace::all();
        $timeSlots = $this->generateTimeSlots();
        
        $reservations = Reservation::with(['paiements'])
            ->whereDate('datetime_reservation', $date)
            ->get();

        $availability = [];

        foreach ($espaces as $espace) {
            $espaceAvailability = [
                'id' => $espace->id,
                'label' => $espace->label,
                'slots' => []
            ];

            foreach ($timeSlots as $hour) {
                $status = $this->getSlotStatus($espace, $hour, $date, $reservations);
                $espaceAvailability['slots'][] = [
                    'hour' => $hour,
                    'status' => $status
                ];
            }

            $availability[] = $espaceAvailability;
        }

        return [
            'date' => $date->format('Y-m-d'),
            'espaces' => $availability
        ];
    }

    private function generateTimeSlots(): array
    {
        $slots = [];
        for ($hour = self::OPENING_HOUR; $hour < self::CLOSING_HOUR; $hour++) {
            $slots[] = $hour;
        }
        return $slots;
    }

    private function getSlotStatus(Espace $espace, int $hour, Carbon $date, Collection $reservations): string
    {
        $slotDateTime = $date->copy()->setHour($hour);
        
        $conflictingReservation = $reservations->first(function ($reservation) use ($espace, $slotDateTime) {
            if ($reservation->id_espace !== $espace->id) {
                return false;
            }

            $reservationStart = Carbon::parse($reservation->datetime_reservation);
            $reservationEnd = $reservationStart->copy()->addHours($reservation->hour_duration);

            return $slotDateTime->between($reservationStart, $reservationEnd);
        });

        if (!$conflictingReservation) {
            return 'libre';
        }

        $hasPaidReservation = $conflictingReservation->paiements
            ->where('validated_at', '!=', null)
            ->count() > 0;

        return $hasPaidReservation ? 'occupé' : 'Reservé, non payé';
    }
}