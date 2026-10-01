<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
   // GET /api/users
    public function index(Request $request): JsonResponse
    {
        $query = User::query();
        if ($request->filled('role')) {
            $query->where('role', strtoupper($request->role));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        $users = $query->latest()->paginate($request->get('per_page', 15));
        return response()->json([
            'success' => true,
            'message' => 'Users list retrieved.',
            'data'    => $users,
        ]);
    }
    // GET /api/users/{id}
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'User found.',
            'data'    => $user,
        ]);
    }
}
