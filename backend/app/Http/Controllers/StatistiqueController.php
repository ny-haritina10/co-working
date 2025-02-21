<?php

namespace App\Http\Controllers;

use App\Services\StatistiqueService;
use Illuminate\Http\Request;

class StatistiqueController extends Controller
{
    protected $statistiqueService;

    public function __construct(StatistiqueService $statistiqueService)
    {
        $this->statistiqueService = $statistiqueService;
    }

    public function getRevenueByPeriod(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'date_min' => 'required|date_format:Y-m-d',
            'date_max' => 'required|date_format:Y-m-d|after_or_equal:date_min',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }
    
        $revenue = $this->statistiqueService->calculateRevenueBetweenDates($request->date_min, $request->date_max);
    
        return response()->json([
            'date_min' => $request->date_min,
            'date_max' => $request->date_max,
            'revenue' => $revenue
        ]);
    }   

    public function getTotalRevenue()
    {
        $paidRevenue = $this->statistiqueService->calculateTotalPaidRevenue();
        $unpaidRevenue = $this->statistiqueService->calculateTotalUnpaidRevenue();

        return response()->json([
            'paid_revenue' => $paidRevenue,
            'unpaid_revenue' => $unpaidRevenue,
            'total_revenue' => $paidRevenue + $unpaidRevenue,
        ]);
    }

    public function getTopTimeSlots()
    {
        // top 3 slots
        $limit = 3;
        $topSlots = $this->statistiqueService->getTopTimeSlots($limit);

        return response()->json([
            'top_time_slots' => $topSlots,
            'message' => 'Top 3 most popular reservation time slots'
        ]);
    }
}