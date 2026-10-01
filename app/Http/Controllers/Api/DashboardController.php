<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\LabOrder;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin(): JsonResponse
    {
        $today = now();
        $todayDate = $today->toDateString();
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalAppointments = Appointment::count();
        $todayAppointments = Appointment::whereDate('date', $todayDate)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();
        $todaysRevenue = Payment::whereDate('date', $todayDate)->sum('amount');
        $totalRevenue = Payment::sum('amount');
        $pendingAppointments = Appointment::whereIn('status', ['scheduled', 'confirmed'])->count();
        $totalDepartments = Department::count();
        $totalStaff = Staff::count();

        $appointmentCounts = Appointment::whereBetween('date', [
            $today->copy()->subDays(29)->toDateString(),
            $todayDate,
        ])
            ->selectRaw('date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $appointmentTrend = collect(range(29, 0))->map(function ($daysAgo) use ($today, $appointmentCounts) {
            $date = $today->copy()->subDays($daysAgo)->toDateString();
            return ['date' => $date, 'count' => (int) ($appointmentCounts[$date] ?? 0)];
        });

        $patientGrowth = collect(range(5, 0))->map(function ($monthsAgo) use ($today) {
            $month = $today->copy()->startOfMonth()->subMonths($monthsAgo);
            return [
                'month' => $month->format('M'),
                'count' => Patient::whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ])->count(),
            ];
        });

        $revenueByMonth = collect(range(5, 0))->map(function ($monthsAgo) use ($today) {
            $month = $today->copy()->startOfMonth()->subMonths($monthsAgo);
            return [
                'month' => $month->format('M'),
                'amount' => (float) Payment::whereBetween('date', [
                    $month->copy()->startOfMonth()->toDateString(),
                    $month->copy()->endOfMonth()->toDateString(),
                ])->sum('amount'),
            ];
        });

        $statusDistribution = Appointment::selectRaw('status, COUNT(*) as value')
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => ['status' => $row->status, 'value' => (int) $row->value]);

        $todaysSchedule = Appointment::with(['patient', 'doctor'])
            ->whereDate('date', $todayDate)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('time')
            ->limit(6)
            ->get()
            ->map(fn ($appointment) => array_merge($appointment->toArray(), [
                'patientName' => trim(($appointment->patient?->first_name ?? '') . ' ' . ($appointment->patient?->last_name ?? '')),
                'doctorName' => trim(($appointment->doctor?->title ?? '') . ' ' . ($appointment->doctor?->first_name ?? '') . ' ' . ($appointment->doctor?->last_name ?? '')),
            ]));

        $upcomingAppointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('date', '>=', $todayDate)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('date')
            ->orderBy('time')
            ->limit(5)
            ->get()
            ->map(fn ($appointment) => array_merge($appointment->toArray(), [
                'patientName' => trim(($appointment->patient?->first_name ?? '') . ' ' . ($appointment->patient?->last_name ?? '')),
                'doctorName' => trim(($appointment->doctor?->title ?? '') . ' ' . ($appointment->doctor?->first_name ?? '') . ' ' . ($appointment->doctor?->last_name ?? '')),
            ]));

        $lowStockMedicines = Medicine::whereIn('status', ['low_stock', 'out_of_stock'])
            ->orderBy('quantity')
            ->limit(5)
            ->get();

        $recentActivities = Activity::orderByDesc('timestamp')->limit(8)->get();

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard retrieved.',
            'data'    => [
                'total_patients'       => $totalPatients,
                'total_doctors'        => $totalDoctors,
                'total_appointments'   => $totalAppointments,
                'today_appointments'   => $todayAppointments,
                'todays_revenue'       => $todaysRevenue,
                'total_revenue'        => $totalRevenue,
                'pending_appointments' => $pendingAppointments,
                'total_departments'    => $totalDepartments,
                'total_staff'          => $totalStaff,
                'appointment_trend'    => $appointmentTrend,
                'patient_growth'       => $patientGrowth,
                'revenue_by_month'     => $revenueByMonth,
                'status_distribution'  => $statusDistribution,
                'todays_schedule'      => $todaysSchedule,
                'upcoming_appointments'=> $upcomingAppointments,
                'low_stock_medicines'  => $lowStockMedicines,
                'recent_activities'    => $recentActivities,
                'pending_lab_results'  => LabOrder::whereNotIn('status', ['completed_lab', 'cancelled_lab'])->count(),
                'unpaid_invoices'      => Invoice::whereIn('status', ['pending', 'partial'])->count(),
            ],
        ]);
    }

    public function patient(string $id): JsonResponse
    {
        $patient = Patient::findOrFail($id);

        $upcomingAppointments = Appointment::with(['doctor', 'department'])
            ->where('patient_id', $id)
            ->where('date', '>=', now()->toDateString())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('date', 'asc')
            ->limit(5)
            ->get();

        $recentRecords = MedicalRecord::with('doctor')
            ->where('patient_id', $id)
            ->orderBy('visit_date', 'desc')
            ->limit(5)
            ->get();

        $recentAppointments = Appointment::with('doctor')
            ->where('patient_id', $id)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(5)
            ->get()
            ->map(fn ($appointment) => array_merge($appointment->toArray(), [
                'doctorName' => trim(($appointment->doctor?->title ?? '') . ' ' . ($appointment->doctor?->first_name ?? '') . ' ' . ($appointment->doctor?->last_name ?? '')),
            ]));

        $nextAppointment = Appointment::with('doctor')
            ->where('patient_id', $id)
            ->whereDate('date', '>=', now()->toDateString())
            ->whereIn('status', ['scheduled', 'confirmed', 'checked_in', 'in_consultation'])
            ->orderBy('date')
            ->orderBy('time')
            ->first();

        if ($nextAppointment) {
            $nextAppointment = array_merge($nextAppointment->toArray(), [
                'doctorName' => trim(($nextAppointment->doctor?->title ?? '') . ' ' . ($nextAppointment->doctor?->first_name ?? '') . ' ' . ($nextAppointment->doctor?->last_name ?? '')),
            ]);
        }

        $pendingInvoices = Invoice::where('patient_id', $id)
            ->whereIn('status', ['pending', 'partial'])
            ->get();

        $totalAppointments = Appointment::where('patient_id', $id)->count();
        $totalPrescriptions = Prescription::where('patient_id', $id)->count();
        $unpaidAmount = $pendingInvoices->sum(fn ($invoice) => (float) $invoice->total - (float) $invoice->paid_amount);

        return response()->json([
            'success' => true,
            'message' => 'Patient dashboard retrieved.',
            'data'    => [
                'patient'               => $patient,
                'upcoming_appointments' => $upcomingAppointments,
                'recent_records'        => $recentRecords,
                'recent_appointments'   => $recentAppointments,
                'next_appointment'      => $nextAppointment,
                'pending_invoices'      => $pendingInvoices->count(),
                'total_appointments'    => $totalAppointments,
                'completed_appointments'=> Appointment::where('patient_id', $id)->where('status', 'completed')->count(),
                'total_records'         => MedicalRecord::where('patient_id', $id)->count(),
                'total_prescriptions'   => $totalPrescriptions,
                'unpaid_amount'         => $unpaidAmount,
            ],
        ]);
    }
}
