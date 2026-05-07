<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $user = $request->user();

        $nonLues = $user->unreadNotifications()->count();
        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ];
            })
            ->values();

        return $this->success([
            'non_lues' => $nonLues,
            'notifications' => $notifications,
        ], 'Notifications');
    }

    public function markRead(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->notFound();
        }

        $notification->markAsRead();

        return $this->success(null, 'Notification marquée comme lue');
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return $this->success(null, 'Toutes les notifications ont été marquées comme lues');
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->notFound();
        }

        $notification->delete();

        return $this->success(null, 'Notification supprimée');
    }
}

