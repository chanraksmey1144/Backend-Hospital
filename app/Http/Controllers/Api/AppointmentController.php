<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::with(['patient', 'doctor', 'department']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhereHas('doctor', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $perPage = $request->get('per_page', 15);
        $appointments = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Appointments retrieved.',
            'data'    => $appointments,
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        $query = Appointment::with(['patient', 'doctor', 'department'])
            ->where('date', $today);

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('time', 'asc')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => "Today's appointments retrieved.",
            'data'    => $appointments,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $appointment = Appointment::with(['patient', 'doctor', 'department'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Appointment retrieved.',
            'data'    => $appointment,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->filled('department_id')) {
            $departmentId = Doctor::whereKey($request->input('doctor_id'))->value('department_id');
            if ($departmentId) {
                $request->merge(['department_id' => $departmentId]);
            }
        }

        $validated = $request->validate([
            'patient_id'    => 'required|string|exists:patients,id',
            'doctor_id'     => 'required|string|exists:doctors,id',
            'department_id' => 'required|string|exists:departments,id',
            'date'          => 'required|date|after_or_equal:today',
            'time'          => 'required|date_format:H:i',
            'type'          => 'nullable|in:checkup,followup,consultation,emergency,procedure',
            'notes'         => 'nullable|string',
            'fee'           => 'nullable|numeric|min:0',
            'duration'      => 'nullable|integer|min:10',
        ]);

        $validated['code'] = 'APT-' . strtoupper(uniqid());

        $appointment = Appointment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Appointment created.',
            'data'    => $appointment,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'patient_id'    => 'sometimes|required|string|exists:patients,id',
            'doctor_id'     => 'sometimes|required|string|exists:doctors,id',
            'department_id' => 'sometimes|required|string|exists:departments,id',
            'date'          => 'sometimes|required|date',
            'time'          => 'sometimes|required|date_format:H:i',
            'type'          => 'nullable|in:checkup,followup,consultation,emergency,procedure',
            'notes'         => 'nullable|string',
            'fee'           => 'nullable|numeric|min:0',
            'duration'      => 'nullable|integer|min:10',
        ]);

        $appointment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated.',
            'data'    => $appointment,
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,confirmed,checked_in,in_consultation,completed,cancelled,no_show',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Appointment status updated.',
            'data'    => $appointment,
        ]);
    }

    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => 'required|string|exists:doctors,id',
            'date'     => 'required|date',
        ]);

        $doctorId = $request->doctor_id;
        $date = Carbon::parse($request->date);
        $dayName = strtolower($date->format('l'));

        $schedule = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->first();

        if (!$schedule) {
            return response()->json([
                'success' => true,
                'message' => 'No schedule found for this day.',
                'data'    => [],
            ]);
        }

        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->where('date', $date->toDateString())
            ->whereNotIn('status', ['cancelled', 'no_show', 'completed'])
            ->pluck('time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        $slots = [];
        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);

        while ($start->lessThan($end)) {
            $slotTime = $start->format('H:i');
            if (!in_array($slotTime, $bookedSlots, true)) {
                $slots[] = $slotTime;
            }
            $start->addMinutes(30);
        }

        return response()->json([
            'success' => true,
            'message' => 'Available slots retrieved.',
            'data'    => $slots,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted.',
        ]);
    }
}
