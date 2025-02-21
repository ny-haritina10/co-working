<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Espace;
use App\Models\Client;
use App\Models\Option;
use App\Models\ReservationOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Str;

class ReservationController extends Controller
{
    public function importCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a valid CSV file',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();

        try {
            $file = $request->file('file');
            $handle = fopen($file->getPathname(), "r");
            
            // Skip the header row
            $header = fgetcsv($handle);
            
            $imported = 0;
            $errors = [];
            $line = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $line++;
                try {
                    // Find the espace by label
                    $espace = Espace::where('label', $data[1])->first();
                    if (!$espace) {
                        $errors[] = "Line {$line}: Espace '{$data[1]}' not found";
                        continue;
                    }

                    // Find the client by phone number
                    $client = Client::where('numero_client', $data[2])->first();
                    if (!$client) {
                        $errors[] = "Line {$line}: Client with phone '{$data[2]}' not found";
                        continue;
                    }

                    // Combine date and time
                    try {
                        $dateTime = Carbon::createFromFormat(
                            'd/m/Y H:i',
                            $data[3] . ' ' . $data[4]
                        );
                    } catch (\Exception $e) {
                        $errors[] = "Line {$line}: Invalid date/time format. Use DD/MM/YYYY for date and HH:mm for time";
                        continue;
                    }

                    // Create the reservation
                    $reservation = Reservation::create([
                        'id_client' => $client->id,
                        'id_espace' => $espace->id,
                        'reference' => $data[0],
                        'datetime_reservation' => $dateTime,
                        'hour_duration' => (int)$data[5]
                    ]);

                    // Handle options if present
                    if (!empty($data[6])) {
                        // Split options and trim whitespace
                        $optionCodes = array_map('trim', explode(',', $data[6]));
                        
                        foreach ($optionCodes as $optionCode) {
                            $optionCodeUpper = Str::upper($optionCode);
                            $option = Option::where('code', $optionCodeUpper)->first();
                                                        
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
                return response()->json([
                    'success' => true,
                    'message' => "{$imported} reservations imported successfully",
                    'errors' => $errors
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No reservations were imported',
                    'errors' => $errors
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error processing the CSV file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}