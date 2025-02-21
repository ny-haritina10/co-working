<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OptionController extends Controller 
{

    public function importCsv(Request $request) 
    {
        $validator = Validator::make(
            $request->all(),
            ['file' => 'required|file|mimes:csv']
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Please upload a valid CSV file',
                    'errors' => $validator->errors()
                ], 400
            );
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
                    $optionData = [
                        'code' => $data[0],
                        'label' => $data[1],
                        'price' => str_replace(',', '', $data[2])
                    ];
                    
                    // find by unique columns code, then update or insert 
                    Option::updateOrCreate(
                        ['code' => $optionData['code']], 
                        ['label' => $optionData['label'], 'price' => $optionData['price']] 
                    );
            
                    $imported++;
                } 
                catch (\Exception $e) {
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