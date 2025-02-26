<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Espace;
use App\Models\Client;
use App\Models\Option;
use App\Models\ReservationOption;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ReservationService
{
    private const OPENING_HOUR = 8;
    private const CLOSING_HOUR = 18;

    public function createReservation(
        int $clientId,
        int $espaceId,
        string $dateReservation,
        int $hourBegin,
        int $duration,
        array $optionIds
    ): Reservation {
        $this->validateReservationTime($hourBegin, $duration);        
        $dateTimeReservation = Carbon::parse($dateReservation)->setHour($hourBegin);
        
        $this->checkSpaceAvailability($espaceId, $dateTimeReservation, $duration);
        $reference = $this->generateReference();

        // transaction to ensure data consistency
        return DB::transaction(function () use ($clientId, $espaceId, $dateTimeReservation, $duration, $optionIds, $reference) {
            $reservation = Reservation::create([
                'id_client' => $clientId,
                'id_espace' => $espaceId,
                'reference' => $reference,
                'datetime_reservation' => $dateTimeReservation,
                'hour_duration' => $duration
            ]);

            // attach options 
            if (!empty($optionIds)) {
                $reservation->options()->attach($optionIds);
            }

            // load relationships for response
            return $reservation->load(['espace', 'options']);
        });
    }

    private function validateReservationTime(int $hourBegin, int $duration): void
    {
        $endHour = $hourBegin + $duration;

        if ($hourBegin < self::OPENING_HOUR || $endHour > self::CLOSING_HOUR) {
            throw new \Exception(
                sprintf('Reservation must be between %dh and %dh', self::OPENING_HOUR, self::CLOSING_HOUR)
            );
        }
    }

    private function checkSpaceAvailability(int $espaceId, Carbon $dateTimeReservation, int $duration): void
    {
        $endTime = $dateTimeReservation->copy()->addHours($duration);

        // check for conflicting reservation
        $conflictingReservation = Reservation::where('id_espace', $espaceId)
            ->where(function ($query) use ($dateTimeReservation, $endTime) {
                $query->where(function ($q) use ($dateTimeReservation, $endTime) {
                    $q->where('datetime_reservation', '<', $endTime)
                        ->whereRaw('datetime_reservation + (hour_duration || \' hours\')::interval > ?', [$dateTimeReservation]);
                });
            })
            ->exists();

        if ($conflictingReservation) {
            throw new \Exception('This space is not available for the selected time period');
        }
    }

    private function generateReference(): string
    {
        do {
            // Format: RES-YYYYMMDD-XXXXX (e.g., RES-20250122-A12B4)
            $reference = 'RES-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Reservation::where('reference', $reference)->exists());

        return $reference;
    }

    public function importCsv($file)
    {
        DB::beginTransaction();
        
        try {
            $handle = fopen($file->getPathname(), "r");
            $header = fgetcsv($handle);
            
            $imported = 0;
            $errors = [];
            $line = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $line++;
                try {
                    $espace = Espace::where('label', $data[1])->first();
                    if (!$espace) {
                        $errors[] = "Line {$line}: Espace '{$data[1]}' not found";
                        continue;
                    }

                    $client = Client::where('numero_client', $data[2])->first();
                    if (!$client) {
                        $errors[] = "Line {$line}: Client with phone '{$data[2]}' not found";
                        continue;
                    }

                    try {
                        $dateTime = Carbon::createFromFormat('d/m/Y H:i', $data[3] . ' ' . $data[4]);
                    } catch (\Exception $e) {
                        $errors[] = "Line {$line}: Invalid date/time format. Use DD/MM/YYYY for date and HH:mm for time";
                        continue;
                    }

                    $reservation = Reservation::create([
                        'id_client' => $client->id,
                        'id_espace' => $espace->id,
                        'reference' => $data[0],
                        'datetime_reservation' => $dateTime,
                        'hour_duration' => (int)$data[5]
                    ]);

                    if (!empty($data[6])) {
                        $optionCodes = array_map('trim', explode(',', $data[6]));

                        foreach ($optionCodes as $optionCode) {
                            $option = Option::where('code', Str::upper($optionCode))->first();
                            
                            if (!$option) {
                                $errors[] = "Line {$line}: Option '{$optionCode}' not found";
                                continue;
                            }

                            ReservationOption::create([
                                'id_reservation' => $reservation->id,
                                'id_option' => $option->id
                            ]);
                        }
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Line {$line}: " . $e->getMessage();
                }
            }

            fclose($handle);

            if ($imported > 0) {
                DB::commit();
                return [
                    'success' => true,
                    'message' => "{$imported} reservations imported successfully",
                    'errors' => $errors,
                    'status' => 200
                ];
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No reservations were imported',
                    'errors' => $errors,
                    'status' => 422
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error processing the CSV file',
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function cancelReservation(string $reference): void
    {
        DB::transaction(function () use ($reference) {
            $reservation = Reservation::where('reference', $reference)
                ->with(['options', 'paiements'])
                ->firstOrFail();

            $reservation->options()->detach();
            $reservation->paiements()->delete();
            $reservation->delete();
        });
    }

    public function getClientReservations(int $clientId): Collection
    {
        $reservations = Reservation::where('id_client', $clientId)
            ->with(['client', 'options', 'paiements'])  // eager loading to fetch all necessary data
            ->get();

        if ($reservations->isEmpty()) {
            return collect([]);
        }

        return $reservations->map(function ($reservation) {
            $startTime = $reservation->datetime_reservation;
            $endTime = $startTime->copy()->addHours($reservation->hour_duration);

            return [
                'id_reservation' => $reservation->id,
                'reservation_date' => $startTime->toDateString(),
                'hour_begin' => $startTime->format('H:i'),
                'hour_end' => $endTime->format('H:i'),
                'name_client' => $reservation->client->name_client ?? 'Unknown', 
                'options' => $reservation->options->pluck('label')->toArray(), 
                'duration' => $reservation->hour_duration,
                'reservation_amount' => $this->calculateReservationAmount($reservation),
                'status' => $this->determineReservationStatus($reservation)
            ];
        });
    }

    private function calculateReservationAmount(Reservation $reservation): float
    {
        $baseAmount = $reservation->espace->hour_price * $reservation->hour_duration;
        $optionsAmount = $reservation->options->sum('price');
        
        return round($baseAmount + $optionsAmount, 2);
    }

    private function determineReservationStatus(Reservation $reservation): string
    {
        $reservationEnd = $reservation->datetime_reservation->copy()->addHours($reservation->hour_duration);

        if ($reservationEnd->isPast()) {
            return 'Fait';
        }

        if ($reservation->paiements->isEmpty()) {
            return 'A payer'; 
        }

        $latestPayment = $reservation->paiements->sortByDesc('created_at')->first();

        if ($latestPayment->validated_at === null) {
            return 'En attente'; 
        }

        return 'Payé'; 
    }
}