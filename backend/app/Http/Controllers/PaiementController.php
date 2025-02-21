<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaiementController extends Controller
{
    public function importCsv(Request $request)
    {
        // Validate the uploaded file
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
                    // Find the associated reservation
                    $reservation = Reservation::where('reference', $data[1])->first();
                    
                    if (!$reservation) {
                        $errors[] = "Line {$line}: Reservation with reference '{$data[1]}' not found";
                        continue;
                    }

                    // Parse the date
                    try {
                        $paymentDate = Carbon::createFromFormat('d/m/Y', $data[2])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $errors[] = "Line {$line}: Invalid date format. Please use DD/MM/YYYY format";
                        continue;
                    }

                    // Create the payment record
                    Paiement::create([
                        'id_reservation' => $reservation->id,
                        'reference' => $data[0], // ref_paiement from CSV
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
                return response()->json([
                    'success' => true,
                    'message' => "{$imported} payments imported successfully",
                    'errors' => $errors
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No payments were imported',
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