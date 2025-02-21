<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CsvService 
{
    public function validateCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Please upload a valid CSV file',
                'errors' => $validator->errors(),
                'status' => 400
            ];
        }

        return ['success' => true];
    }
}