<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::with('department');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('availability')) {
            $query->where('availability', $request->availability);
        }

        $perPage = $request->get('per_page', 15);
        $doctors = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Doctors retrieved.',
            'data'    => $doctors,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $doctor = Doctor::with(['department', 'schedules'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Doctor retrieved.',
            'data'    => $doctor,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'code' => $request->input('code') ?? 'DOC-' . Str::upper(Str::random(8)),
            'title' => $request->input('title') ?? 'Dr.',
        ]);

        $validated = $request->validate([
            'code'             => 'required|string|max:20|unique:doctors,code',
            'title'            => 'required|string|max:10',
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'department_id'    => 'required|string|exists:departments,id',
            'specialization'   => 'nullable|string|max:150',
            'license_no'       => 'nullable|string|max:50',
            'experience_years' => 'nullable|integer|min:0',
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|unique:doctors,email',
            'qualification'    => 'nullable|string|max:100',
            'bio'              => 'nullable|string',
            'fee'              => 'nullable|numeric|min:0',
            'availability'     => 'nullable|in:available,on_leave,unavailable',
            'rating'           => 'nullable|numeric|min:0|max:5',
        ]);

        $doctor = Doctor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Doctor created.',
            'data'    => $doctor,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $doctor = Doctor::findOrFail($id);

        $validated = $request->validate([
            'code'             => 'sometimes|required|string|max:20|unique:doctors,code,' . $id,
            'title'            => 'sometimes|required|string|max:10',
            'first_name'       => 'sometimes|required|string|max:100',
            'last_name'        => 'sometimes|required|string|max:100',
            'department_id'    => 'sometimes|required|string|exists:departments,id',
            'specialization'   => 'nullable|string|max:150',
            'license_no'       => 'nullable|string|max:50',
            'experience_years' => 'nullable|integer|min:0',
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|unique:doctors,email,' . $id,
            'qualification'    => 'nullable|string|max:100',
            'bio'              => 'nullable|string',
            'fee'              => 'nullable|numeric|min:0',
            'availability'     => 'nullable|in:available,on_leave,unavailable',
            'rating'           => 'nullable|numeric|min:0|max:5',
        ]);

        $doctor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Doctor updated.',
            'data'    => $doctor,
        ]);
    }

    public function updateSchedule(Request $request, string $id): JsonResponse
    {
        $doctor = Doctor::findOrFail($id);

        $validated = $request->validate([
            'schedules'   => 'required|array|min:1',
            'schedules.*.day'       => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time'   => 'required|date_format:H:i|after:schedules.*.start_time',
        ]);

        foreach ($validated['schedules'] as $schedule) {
            DoctorSchedule::updateOrCreate(
                ['doctor_id' => $doctor->id, 'day' => $schedule['day']],
                ['start_time' => $schedule['start_time'], 'end_time' => $schedule['end_time']]
            );
        }

        $doctor->load('schedules');

        return response()->json([
            'success' => true,
            'message' => 'Doctor schedule updated.',
            'data'    => $doctor,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor deleted.',
        ]);
    }
}
