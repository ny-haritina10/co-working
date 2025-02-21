<?php

namespace App\Http\Controllers;

use App\Models\Espace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EspaceController extends Controller
{
    public function importCsv(Request $request)
    {
        // validation, input name need to be `file` 
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a valid CSV file',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $file = $request->file('file');
            $handle = fopen($file->getPathname(), "r");
            
            // skip header
            $header = fgetcsv($handle);
            
            $imported = 0;
            $errors = [];
            
            while (($data = fgetcsv($handle)) !== false) {
                try {
                    // Map CSV columns to database fields
                    $espaceData = [
                        'label' => $data[0],
                        'hour_price' => str_replace(',', '', $data[1]) 
                    ];
                    
                    Espace::updateOrCreate(
                        ['label' => $espaceData['label']],
                        ['hour_price' => $espaceData['hour_price']]
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $imported++;
                    $errors[] = "Error on row {$imported}: " . $e->getMessage();
                }
            }
            
            fclose($handle);
            
            return response()->json([
                'success' => true,
                'message' => "{$imported} records imported successfully",
                'errors' => $errors
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing the CSV file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}