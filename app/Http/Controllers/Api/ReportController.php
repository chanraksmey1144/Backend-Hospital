<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $appointmentsByMonth = Appointment::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('MONTH(date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get();

        $revenueByMonth = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('MONTH(created_at) as month, SUM(total) as total')
            ->groupBy('month')
            ->get();

        $appointmentsByStatus = Appointment::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $topDiagnoses = MedicalRecord::whereBetween('visit_date', [$startDate, $endDate])
            ->whereNotNull('diagnosis')
            ->selectRaw('diagnosis, COUNT(*) as count')
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $newPatients = Patient::whereBetween('created_at', [$startDate, $endDate])->count();

        $totalAppointments = Appointment::whereBetween('date', [$startDate, $endDate])->count();
        $completedAppointments = Appointment::whereBetween('date', [$startDate, $endDate])->where('status', 'completed')->count();
        $cancelledAppointments = Appointment::whereBetween('date', [$startDate, $endDate])->where('status', 'cancelled')->count();

        return response()->json([
            'success' => true,
            'message' => 'Report data retrieved.',
            'data'    => [
                'appointments_by_month'  => $appointmentsByMonth,
                'revenue_by_month'       => $revenueByMonth,
                'appointments_by_status' => $appointmentsByStatus,
                'top_diagnoses'          => $topDiagnoses,
                'new_patients'           => $newPatients,
                'total_appointments'     => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'cancelled_appointments' => $cancelledAppointments,
                'date_range'             => ['start' => $startDate, 'end' => $endDate],
            ],
        ]);
    }
}
