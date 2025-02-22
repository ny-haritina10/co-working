<?php

namespace App\Http\Controllers;

use App\Services\CsvService;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_espace' => 'required|exists:espaces,id',
            'id_client' => 'required|exists:client,id',
            'date_reservation' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hour_begin' => 'required|integer|min:8|max:18',
            'duration' => 'required|integer|min:1|max:10',
            'options' => 'nullable|array',
            'options.*' => 'exists:options,id'
        ]);

        try {
            $reservation = $this->reservationService->createReservation(
                $request->id_client,
                $request->id_espace,
                $request->date_reservation,
                $request->hour_begin,
                $request->duration,
                $request->options ?? []
            );

            return response()->json([
                'message' => 'Reservation created successfully',
                'data' => $reservation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}