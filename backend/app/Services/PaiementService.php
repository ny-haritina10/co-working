<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
}