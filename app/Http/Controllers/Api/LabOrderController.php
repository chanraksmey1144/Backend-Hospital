<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LabOrder::with(['patient', 'doctor', 'testType']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $perPage = $request->get('per_page', 15);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lab orders retrieved.',
            'data'    => $orders,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $order = LabOrder::with(['patient', 'doctor', 'testType'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Lab order retrieved.',
            'data'    => $order,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'   => 'required|string|exists:patients,id',
            'doctor_id'    => 'required|string|exists:doctors,id',
            'test_type_id' => 'required|string|exists:lab_test_types,id',
            'notes'        => 'nullable|string',
            'result'       => 'nullable|string',
            'result_notes' => 'nullable|string',
        ]);

        $validated['code'] = 'LBT-' . strtoupper(uniqid());
        $validated['ordered_date'] = now();

        $order = LabOrder::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lab order created.',
            'data'    => $order,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = LabOrder::findOrFail($id);

        $validated = $request->validate([
            'patient_id'   => 'sometimes|required|string|exists:patients,id',
            'doctor_id'    => 'sometimes|required|string|exists:doctors,id',
            'test_type_id' => 'sometimes|required|string|exists:lab_test_types,id',
            'notes'        => 'nullable|string',
            'result'       => 'nullable|string',
            'result_notes' => 'nullable|string',
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lab order updated.',
            'data'    => $order,
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status'       => 'required|in:ordered,processing,completed_lab,cancelled_lab',
            'result'       => 'nullable|string',
            'result_notes' => 'nullable|string',
        ]);

        $order = LabOrder::findOrFail($id);

        if ($validated['status'] === 'completed_lab') {
            $validated['completed_date'] = now();
        }

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lab order status updated.',
            'data'    => $order,
        ]);
    }
}
