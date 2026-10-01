<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Models\LabTestType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabTestTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $testTypes = LabTestType::all();
        return response()->json([
            'success' => true,
            'message' => 'Lab test types retrieved.',
            'data'    => $testTypes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => 'nullable|string|max:100',
            'price'        => 'nullable|numeric|min:0',
            'unit'         => 'nullable|string|max:40',
            'normal_range' => 'nullable|string|max:150',
        ]);

        $testType = LabTestType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lab test type created.',
            'data'    => $testType,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $testType = LabTestType::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:150',
            'category'     => 'nullable|string|max:100',
            'price'        => 'nullable|numeric|min:0',
            'unit'         => 'nullable|string|max:40',
            'normal_range' => 'nullable|string|max:150',
        ]);

        $testType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lab test type updated.',
            'data'    => $testType,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $testType = LabTestType::findOrFail($id);
        $testType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lab test type deleted.',
        ]);
    }
}
