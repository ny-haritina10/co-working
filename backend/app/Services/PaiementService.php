<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Str;

class PaiementService
{
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
                    $reservation = Reservation::where('reference', $data[1])->first();

                    if (!$reservation) {
                        $errors[] = "Line {$line}: Reservation with reference '{$data[1]}' not found";
                        continue;
                    }

                    try {
                        $paymentDate = Carbon::createFromFormat('d/m/Y', $data[2])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $errors[] = "Line {$line}: Invalid date format. Please use DD/MM/YYYY format";
                        continue;
                    }

                    Paiement::create([
                        'id_reservation' => $reservation->id,
                        'reference' => $data[0],
                        'date_paiement' => $paymentDate
                    ]);

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
                    'message' => "{$imported} payments imported successfully",
                    'errors' => $errors,
                    'status' => 200
                ];
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No payments were imported',
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

    public function validatePaiement($id)
    {
        $paiement = Paiement::find($id);

        if (!$paiement) {
            return [
                'success' => false,
                'message' => 'Paiement not found',
                'status' => 404
            ];
        }

        if ($paiement->validated_at) {
            return [
                'success' => false,
                'message' => 'Paiement has already been validated',
                'status' => 400
            ];
        }

        $paiement->update(['validated_at' => now()]);

        return [
            'success' => true,
            'message' => 'Paiement successfully validated',
            'paiement' => $paiement,
            'status' => 200
        ];
    }

    public function verifyReservationOwnership(int $reservationId, int $clientId): Reservation
    {
        $reservation = Reservation::with(['paiements'])->findOrFail($reservationId);

        if ($reservation->id_client !== $clientId) {
            throw new \Exception('This reservation does not belong to the authenticated client');
        }

        // Check if reservation is already paid
        if ($reservation->paiements->whereNotNull('validated_at')->isNotEmpty()) {
            throw new \Exception('This reservation is already paid');
        }

        // Check if there's a pending payment
        if ($reservation->paiements->whereNull('validated_at')->isNotEmpty()) {
            throw new \Exception('This reservation already has a pending payment');
        }

        return $reservation;
    }

    public function createPayment(Reservation $reservation): Paiement
    {
        // validated_at will be set by admin later
        return Paiement::create([
            'id_reservation' => $reservation->id,
            'reference' => $this->generatePaymentReference(),
            'date_paiement' => Carbon::now()->toDateString(),
        ]);
    }

    private function generatePaymentReference(): string
    {
        do {
            // Format: PAY-YYYYMMDD-XXXXX (e.g., PAY-20250122-A12B4)
            $reference = 'PAY-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Paiement::where('reference', $reference)->exists());

        return $reference;
    }
}