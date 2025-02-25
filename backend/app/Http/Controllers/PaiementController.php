<?php

namespace App\Http\Controllers;

use App\Services\CsvService;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaiementController extends Controller
{
    protected $csvService;
    protected $paiementService;

    public function __construct(CsvService $csvService, PaiementService $paiementService)
    {
        $this->csvService = $csvService;
        $this->paiementService = $paiementService;
    }

    public function getAllPaiements(): JsonResponse
    {
        $paiements = $this->paiementService->getAllPaiements();
        
        return response()->json([
            'success' => true,
            'data' => $paiements
        ], 200);
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

    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'id_client' => 'required|exists:client,id',
            'id_reservation' => 'required|exists:reservations,id',
        ]);

        try {
            // Check if the reservation belongs to the authenticated client
            $reservation = $this->paiementService->verifyReservationOwnership(
                $request->id_reservation,
                $request->id_client
            );

            $payment = $this->paiementService->createPayment($reservation);

            return response()->json([
                'message' => 'Payment processed successfully',
                'data' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}