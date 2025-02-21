<?php

namespace App\Http\Controllers;

use App\Services\CsvService;
use App\Services\PaiementService;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    protected $csvService;
    protected $paiementService;

    public function __construct(CsvService $csvService, PaiementService $paiementService)
    {
        $this->csvService = $csvService;
        $this->paiementService = $paiementService;
    }

    public function importCsv(Request $request)
    {
        $validationResult = $this->csvService->validateCsv($request);
        if (!$validationResult['success']) {
            return response()->json($validationResult, $validationResult['status']);
        }

        $importResult = $this->paiementService->importCsv($request->file('file'));
        return response()->json($importResult, $importResult['status']);
    }

    public function validatePaiement($id)
    {
        $validationResult = $this->paiementService->validatePaiement($id);
        return response()->json($validationResult, $validationResult['status']);
    }
}