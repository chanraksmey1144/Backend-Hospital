<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use Illuminate\Http\JsonResponse;

class LaboratoryDashboardController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $totalOrders = LabOrder::count();
        $ordered = LabOrder::where('status', 'ordered')->count();
        $processing = LabOrder::where('status', 'processing')->count();
        $completed = LabOrder::where('status', 'completed_lab')->count();
        $cancelled = LabOrder::where('status', 'cancelled_lab')->count();
        $recentOrders = LabOrder::with(['patient', 'doctor', 'testType'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Laboratory dashboard retrieved.',
            'data'    => [
                'total_orders'  => $totalOrders,
                'ordered'       => $ordered,
                'processing'    => $processing,
                'completed'     => $completed,
                'cancelled'     => $cancelled,
                'recent_orders' => $recentOrders,
            ],
        ]);
    }
}
