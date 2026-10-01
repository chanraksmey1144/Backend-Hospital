<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Staff::with('department');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $staff = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Staff retrieved.',
            'data'    => $staff,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $staff = Staff::with('department')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Staff retrieved.',
            'data'    => $staff,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'code' => $request->input('code') ?? 'STF-' . Str::upper(Str::random(8)),
        ]);

        $validated = $request->validate([
            'code'          => 'required|string|max:20|unique:staff,code',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'role'          => 'required|in:ADMIN,DOCTOR,NURSE,RECEPTIONIST,PHARMACIST,ACCOUNTANT',
            'department_id' => 'nullable|string|exists:departments,id',
            'email'         => 'nullable|email',
            'phone'         => 'nullable|string|max:30',
            'hire_date'     => 'nullable|date',
            'status'        => 'nullable|in:active,on_leave,inactive',
            'salary'        => 'nullable|numeric|min:0',
        ]);

        $staff = Staff::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Staff created.',
            'data'    => $staff,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'code'          => 'sometimes|required|string|max:20|unique:staff,code,' . $id,
            'first_name'    => 'sometimes|required|string|max:100',
            'last_name'     => 'sometimes|required|string|max:100',
            'role'          => 'sometimes|required|in:ADMIN,DOCTOR,NURSE,RECEPTIONIST,PHARMACIST,ACCOUNTANT',
            'department_id' => 'nullable|string|exists:departments,id',
            'email'         => 'nullable|email',
            'phone'         => 'nullable|string|max:30',
            'hire_date'     => 'nullable|date',
            'status'        => 'nullable|in:active,on_leave,inactive',
            'salary'        => 'nullable|numeric|min:0',
        ]);

        $staff->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Staff updated.',
            'data'    => $staff,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted.',
        ]);
    }
}
