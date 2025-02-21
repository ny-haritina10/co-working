<?php

namespace App\Http\Controllers;

use App\Services\CsvService;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected $csvService;
    protected $reservationService;

    public function __construct(CsvService $csvService, ReservationService $reservationService)
    {
        $this->csvService = $csvService;
        $this->reservationService = $reservationService;
    }

    public function importCsv(Request $request)
    {
        $validationResult = $this->csvService->validateCsv($request);
        if (!$validationResult['success']) {
            return response()->json($validationResult, $validationResult['status']);
        }

        $importResult = $this->reservationService->importCsv($request->file('file'));
        return response()->json($importResult, $importResult['status']);
    }
}