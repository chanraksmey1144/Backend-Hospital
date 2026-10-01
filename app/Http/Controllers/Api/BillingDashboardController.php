<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BillingDashboardController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $totalInvoices = Invoice::count();
        $pending = Invoice::where('status', 'pending')->count();
        $partial = Invoice::where('status', 'partial')->count();
        $paid = Invoice::where('status', 'paid')->count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereIn('status', ['pending', 'partial'])->sum(DB::raw('total - paid_amount'));
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total');
        $recentInvoices = Invoice::with(['patient'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Billing dashboard retrieved.',
            'data'    => [
                'total_invoices'  => $totalInvoices,
                'pending'         => $pending,
                'partial'         => $partial,
                'paid'            => $paid,
                'total_revenue'   => $totalRevenue,
                'pending_amount'  => $pendingAmount,
                'monthly_revenue' => $monthlyRevenue,
                'recent_invoices' => $recentInvoices,
            ],
        ]);
    }
}
