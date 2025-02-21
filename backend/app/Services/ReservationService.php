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

class ReservationService
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
}