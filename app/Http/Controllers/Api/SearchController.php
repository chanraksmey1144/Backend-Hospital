<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'message' => 'Search query too short.',
                'data'    => [],
            ]);
        }

        $results = [];

        $patients = Patient::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->limit(5)->get()->map(fn($p) => ['type' => 'patient', 'id' => $p->id, 'name' => $p->first_name . ' ' . $p->last_name, 'code' => $p->code]);
        $results = $results->merge($patients);

        $doctors = Doctor::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('specialization', 'like', "%{$query}%")
            ->limit(5)->get()->map(fn($d) => ['type' => 'doctor', 'id' => $d->id, 'name' => $d->title . ' ' . $d->first_name . ' ' . $d->last_name, 'specialization' => $d->specialization]);
        $results = $results->merge($doctors);

        $appointments = Appointment::where('code', 'like', "%{$query}%")
            ->limit(5)->get()->map(fn($a) => ['type' => 'appointment', 'id' => $a->id, 'code' => $a->code, 'date' => $a->date]);
        $results = $results->merge($appointments);

        $records = MedicalRecord::where('code', 'like', "%{$query}%")
            ->orWhere('diagnosis', 'like', "%{$query}%")
            ->limit(5)->get()->map(fn($r) => ['type' => 'medical_record', 'id' => $r->id, 'code' => $r->code, 'diagnosis' => $r->diagnosis]);
        $results = $results->merge($records);

        return response()->json([
            'success' => true,
            'message' => 'Search results.',
            'data'    => $results->values(),
        ]);
    }
}
