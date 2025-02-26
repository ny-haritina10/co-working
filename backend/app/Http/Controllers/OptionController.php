<?php

namespace App\Http\Controllers;

use App\Services\OptionService;
use Illuminate\Http\Request;
use App\Services\CsvService;

class OptionController extends Controller
{
    protected $optionService;

    protected $csvService;

    public function __construct(OptionService $optionService, CsvService $csvService)
    {
        $this->optionService = $optionService;
        $this->csvService = $csvService;
    }

    // New endpoint to get all options
    public function index()
    {
        try {
            $options = $this->optionService->getAllOptions();
            return response()->json([
                'success' => true,
                'data' => $options,
                'message' => 'Options retrieved successfully',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve options',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function importCsv(Request $request)
    {
        $validationResult = $this->csvService->validateCsv($request);
        if (!$validationResult['success']) {
            return response()->json($validationResult, $validationResult['status']);
        }

        $importResult = $this->optionService->importCsv($request->file('file'));
        return response()->json($importResult, $importResult['status']);
    }
}