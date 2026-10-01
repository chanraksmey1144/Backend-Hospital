<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Patient::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->has('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $patients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Patients retrieved.',
            'data'    => $patients,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $patient = Patient::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Patient retrieved.',
            'data'    => $patient,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'code' => $request->input('code') ?? 'PT-' . Str::upper(Str::random(8)),
        ]);

        $validated = $request->validate([
            'code'              => 'required|string|max:20|unique:patients,code',
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'gender'            => 'required|in:male,female,other',
            'birth_date'        => 'nullable|date',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email',
            'address'           => 'nullable|string|max:255',
            'blood_group'       => 'nullable|string|max:5',
            'allergies'         => 'nullable|array',
            'allergies.*'       => 'string',
            'insurance_no'      => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone'   => 'nullable|string|max:30',
            'marital_status'    => 'nullable|in:single,married,divorced,widowed',
            'occupation'        => 'nullable|string|max:100',
            'status'            => 'nullable|in:active,inactive',
        ]);

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient created.',
            'data'    => $patient,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'code'              => 'sometimes|required|string|max:20|unique:patients,code,' . $id,
            'first_name'        => 'sometimes|required|string|max:100',
            'last_name'         => 'sometimes|required|string|max:100',
            'gender'            => 'sometimes|required|in:male,female,other',
            'birth_date'        => 'nullable|date',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email',
            'address'           => 'nullable|string|max:255',
            'blood_group'       => 'nullable|string|max:5',
            'allergies'         => 'nullable|array',
            'allergies.*'       => 'string',
            'insurance_no'      => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone'   => 'nullable|string|max:30',
            'marital_status'    => 'nullable|in:single,married,divorced,widowed',
            'occupation'        => 'nullable|string|max:100',
            'status'            => 'nullable|in:active,inactive',
            'last_visit'        => 'nullable|date',
        ]);

        $patient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated.',
            'data'    => $patient,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted.',
        ]);
    }
}
