<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::with('headDoctor')->get();
        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved.',
            'data'    => $departments,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $department = Department::with(['headDoctor', 'doctors', 'staff'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Department retrieved.',
            'data'    => $department,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'           => 'required|string|max:20|unique:departments,code',
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'head_doctor_id' => 'nullable|string|exists:doctors,id',
            'room_number'    => 'nullable|string|max:40',
            'status'         => 'nullable|in:active,inactive',
        ]);

        $department = Department::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Department created.',
            'data'    => $department,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'code'           => 'sometimes|required|string|max:20|unique:departments,code,' . $id,
            'name'           => 'sometimes|required|string|max:100',
            'description'    => 'nullable|string',
            'head_doctor_id' => 'nullable|string|exists:doctors,id',
            'room_number'    => 'nullable|string|max:40',
            'status'         => 'nullable|in:active,inactive',
        ]);

        $department->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Department updated.',
            'data'    => $department,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted.',
        ]);
    }
}
