<?php

namespace App\Services;

use App\Models\Espace;

class EspaceService
{
    public function importCsv($file)
    {
        try {
            $handle = fopen($file->getPathname(), "r");

            $header = fgetcsv($handle);

            $imported = 0;
            $errors = [];

            while (($data = fgetcsv($handle)) !== false) {
                try {
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