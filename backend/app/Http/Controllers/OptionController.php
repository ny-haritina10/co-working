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
