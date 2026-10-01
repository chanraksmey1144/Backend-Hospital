<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
   // GET /api/notification-preferences
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        // Retrieve existing settings, or auto-create default settings if none exist yet
        $preferences = NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email'        => true,
                'push'         => true,
                'appointments' => true,
                'laboratory'   => false,
                'billing'      => true,
                'inventory'    => true,
                'system'       => false,
            ]
        );
        return response()->json([
            'success' => true,
            'message' => 'Notification preferences retrieved.',
            'data'    => $preferences,
        ]);
    }
    // PUT /api/notification-preferences
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'        => 'sometimes|boolean',
            'push'         => 'sometimes|boolean',
            'appointments' => 'sometimes|boolean',
            'laboratory'   => 'sometimes|boolean',
            'billing'      => 'sometimes|boolean',
            'inventory'    => 'sometimes|boolean',
            'system'       => 'sometimes|boolean',
        ]);
        $user = $request->user();
        $preferences = NotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );
        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully.',
            'data'    => $preferences,
        ]);
    }
}
