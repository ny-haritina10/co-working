<?php

namespace App\Http\Controllers;

use App\Services\EspaceService;
use App\Services\CsvService;
use Illuminate\Http\Request;

class EspaceController extends Controller
{
    protected $espaceService;
    protected $csvService;

    public function __construct(EspaceService $espaceService, CsvService $csvService)
    {
        $this->espaceService = $espaceService;
        $this->csvService = $csvService;
    }

    public function importCsv(Request $request)
    {
        $validationResult = $this->csvService->validateCsv($request);
        if (!$validationResult['success']) {
            return response()->json($validationResult, $validationResult['status']);
        }

        $importResult = $this->espaceService->importCsv($request->file('file'));
        return response()->json($importResult, $importResult['status']);
    }
}