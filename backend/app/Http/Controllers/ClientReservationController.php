<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ClientReservationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientReservationController extends Controller
{
    private $reservationService;

    public function __construct(ClientReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_client' => 'required|exists:client,id',
            'status' => 'nullable|in:upcoming,past,all',
        ]);

        $status = $request->get('status', 'all');

        $reservations = $this->reservationService->getClientReservations(
            $request->id_client,
            $status
        );

        return response()->json($reservations);
    }
}