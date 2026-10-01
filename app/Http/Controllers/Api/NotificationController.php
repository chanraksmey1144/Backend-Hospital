<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Notification::query()->orderBy('created_at', 'desc');

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        $perPage = $request->get('per_page', 15);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved.',
            'data'    => $notifications,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $query = Notification::where('is_read', false);

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $count = $query->count();

        return response()->json([
            'success' => true,
            'message' => 'Unread count retrieved.',
            'data'    => ['count' => $count],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data'    => $notification,
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $query = Notification::where('is_read', false);

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $query->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $query = Notification::query();

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted.',
        ]);
    }
}
