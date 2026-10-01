<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Prescription::with(['patient', 'doctor', 'items.medicine']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
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
        $prescriptions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Prescriptions retrieved.',
            'data'    => $prescriptions,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $prescription = Prescription::with(['patient', 'doctor', 'items.medicine'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Prescription retrieved.',
            'data'    => $prescription,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'                => 'required|string|exists:patients,id',
            'doctor_id'                 => 'required|string|exists:doctors,id',
            'date'                      => 'nullable|date',
            'items'                     => 'required|array|min:1',
            'items.*.medicine_id'       => 'required|string|exists:medicines,id',
            'items.*.medicine_name'     => 'nullable|string|max:150',
            'items.*.dosage'            => 'nullable|string|max:50',
            'items.*.frequency'         => 'nullable|string|max:50',
            'items.*.duration'          => 'nullable|string|max:50',
            'items.*.route'             => 'nullable|string|max:50',
            'items.*.instructions'      => 'nullable|string',
            'items.*.quantity'          => 'required|integer|min:1',
        ]);

        $prescription = DB::transaction(function () use ($validated) {
            $rx = Prescription::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id'  => $validated['doctor_id'],
                'code'       => 'RX-' . strtoupper(uniqid()),
                'date'       => $validated['date'] ?? now()->toDateString(),
                'status'     => 'issued',
                'issued_at'  => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $rx->items()->create($item);
            }

            return $rx;
        });

        $prescription->load(['patient', 'doctor', 'items.medicine']);

        return response()->json([
            'success' => true,
            'message' => 'Prescription created.',
            'data'    => $prescription,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $prescription = Prescription::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'sometimes|required|string|exists:patients,id',
            'doctor_id'  => 'sometimes|required|string|exists:doctors,id',
            'date'       => 'nullable|date',
            'status'     => 'nullable|in:issued,dispensed,partially_dispensed,cancelled',
        ]);

        $prescription->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Prescription updated.',
            'data'    => $prescription,
        ]);
    }

    public function dispense(Request $request, string $id): JsonResponse
    {
        $prescription = Prescription::with('items.medicine')->findOrFail($id);

        DB::transaction(function () use ($prescription) {
            foreach ($prescription->items as $item) {
                if ($item->medicine) {
                    $item->medicine->decrement('quantity', $item->quantity);
                }
            }

            $prescription->update([
                'status'    => 'dispensed',
                'issued_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Prescription dispensed.',
            'data'    => $prescription->fresh(['items.medicine']),
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:issued,dispensed,partially_dispensed,cancelled',
        ]);

        $prescription = Prescription::findOrFail($id);
        $prescription->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Prescription status updated.',
            'data'    => $prescription,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prescription deleted.',
        ]);
    }
}
