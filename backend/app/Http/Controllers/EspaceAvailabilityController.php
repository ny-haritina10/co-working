<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\EspaceAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class EspaceAvailabilityController extends Controller
{
    private $availabilityService;

    public function __construct(EspaceAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function getDailyAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::parse($request->date);
        $availability = $this->availabilityService->getDailyAvailability($date);

        return response()->json($availability);
    }
}