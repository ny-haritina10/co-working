<?php


namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;

class ClientReservationService
{
    public function getClientReservations(int $clientId, string $status): array
    {
        $query = Reservation::with(['espace', 'options', 'paiements'])
            ->where('id_client', $clientId)
            ->orderBy('datetime_reservation', 'desc');

        switch ($status) {
            case 'upcoming':
                $query->where('datetime_reservation', '>=', Carbon::now());
                break;
            case 'past':
                $query->where('datetime_reservation', '<', Carbon::now());
                break;
        }

        return $query->get()->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'reference' => $reservation->reference,
                'datetime_reservation' => $reservation->datetime_reservation->format('Y-m-d H:i'),
                'hour_duration' => $reservation->hour_duration,
                'end_time' => $reservation->datetime_reservation
                    ->addHours($reservation->hour_duration)
                    ->format('Y-m-d H:i'),
                'espace' => [
                    'id' => $reservation->espace->id,
                    'label' => $reservation->espace->label,
                    'hour_price' => $reservation->espace->hour_price,
                ],
                'options' => $reservation->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'code' => $option->code,
                        'label' => $option->label,
                        'price' => $option->price,
                    ];
                }),
                'payment_status' => $this->getPaymentStatus($reservation),
                'total_price' => $this->calculateTotalPrice($reservation),
            ];
        })->all();
    }

    private function getPaymentStatus($reservation): string
    {
        if ($reservation->paiements->isEmpty()) {
            return 'unpaid';
        }

        return $reservation->paiements->whereNotNull('validated_at')->isNotEmpty()
            ? 'paid'
            : 'pending';
    }

    private function calculateTotalPrice($reservation): float
    {
        $espacePrice = $reservation->espace->hour_price * $reservation->hour_duration;
        $optionsPrice = $reservation->options->sum('price');

        return $espacePrice + $optionsPrice;
    }
}