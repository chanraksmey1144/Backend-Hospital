<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MedicalRecord::with(['patient', 'doctor']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $records = $query->orderBy('visit_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Medical records retrieved.',
            'data'    => $records,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = MedicalRecord::with(['patient', 'doctor'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Medical record retrieved.',
            'data'    => $record,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'      => 'required|string|exists:patients,id',
            'doctor_id'       => 'required|string|exists:doctors,id',
            'visit_date'      => 'required|date',
            'chief_complaint' => 'nullable|string',
            'diagnosis'       => 'nullable|string',
            'treatment_plan'  => 'nullable|string',
            'notes'           => 'nullable|string',
            'status'          => 'nullable|in:open,closed',
            'vitals'          => 'nullable|array',
            'vitals.bloodPressure'      => 'nullable|string',
            'vitals.heartRate'          => 'nullable|numeric',
            'vitals.temperature'        => 'nullable|numeric',
            'vitals.respiratoryRate'    => 'nullable|numeric',
            'vitals.weight'             => 'nullable|numeric',
            'vitals.height'             => 'nullable|numeric',
            'vitals.oxygenSaturation'   => 'nullable|numeric',
            'follow_up_date'  => 'nullable|date',
        ]);

        $validated['code'] = 'MR-' . strtoupper(uniqid());

        $record = MedicalRecord::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Medical record created.',
            'data'    => $record,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $record = MedicalRecord::findOrFail($id);

        $validated = $request->validate([
            'patient_id'      => 'sometimes|required|string|exists:patients,id',
            'doctor_id'       => 'sometimes|required|string|exists:doctors,id',
            'visit_date'      => 'sometimes|required|date',
            'chief_complaint' => 'nullable|string',
            'diagnosis'       => 'nullable|string',
            'treatment_plan'  => 'nullable|string',
            'notes'           => 'nullable|string',
            'status'          => 'nullable|in:open,closed',
            'vitals'          => 'nullable|array',
            'follow_up_date'  => 'nullable|date',
        ]);

        $record->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Medical record updated.',
            'data'    => $record,
        ]);
    }

    public function timeline(string $patientId): JsonResponse
    {
        $records = MedicalRecord::with(['doctor'])
            ->where('patient_id', $patientId)
            ->orderBy('visit_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Patient timeline retrieved.',
            'data'    => $records,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $record = MedicalRecord::findOrFail($id);
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medical record deleted.',
        ]);
    }
}
