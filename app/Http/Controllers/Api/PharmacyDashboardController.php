<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PharmacyDashboardController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $totalMedicines = Medicine::count();
        $inStock = Medicine::where('status', 'in_stock')->count();
        $lowStock = Medicine::where('status', 'low_stock')->count();
        $outOfStock = Medicine::where('status', 'out_of_stock')->count();
        $expiringSoon = Medicine::where('status', 'expiring_soon')
            ->orWhere('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->count();
        $expired = Medicine::where('status', 'expired')
            ->orWhere('expiry_date', '<', Carbon::now())
            ->count();

        $totalValue = Medicine::sum(\Illuminate\Support\Facades\DB::raw('quantity * selling_price'));
        $lowStockMedicines = Medicine::where('status', 'low_stock')->orWhere('quantity', '<=', DB::raw('min_stock'))->limit(10)->get();
        $expiringMedicines = Medicine::where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->limit(10)->get();

        return response()->json([
            'success' => true,
            'message' => 'Pharmacy dashboard retrieved.',
            'data'    => [
                'total_medicines'    => $totalMedicines,
                'in_stock'           => $inStock,
                'low_stock'          => $lowStock,
                'out_of_stock'       => $outOfStock,
                'expiring_soon'      => $expiringSoon,
                'expired'            => $expired,
                'total_value'        => $totalValue,
                'low_stock_items'    => $lowStockMedicines,
                'expiring_items'     => $expiringMedicines,
            ],
        ]);
    }
}
