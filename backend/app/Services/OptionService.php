<?php

namespace App\Services;

use App\Models\Option;

class OptionService
{
    public function getAllOptions()
    {
        return Option::all();
    }
    
    public function importCsv($file)
    {
        try {
            $handle = fopen($file->getPathname(), "r");

            // Skip header
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

                    // Find by unique column "code", then update or insert
                    Option::updateOrCreate(
                        ['code' => $optionData['code']],
                        ['label' => $optionData['label'], 'price' => $optionData['price']]
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Error on row {$imported}: " . $e->getMessage();
                }
            }

            fclose($handle);

            return [
                'success' => true,
                'message' => "{$imported} records imported successfully",
                'errors' => $errors,
                'status' => 200
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error processing the CSV file',
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }
}