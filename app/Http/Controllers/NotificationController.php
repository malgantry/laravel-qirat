<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Fetch notifications logic (shared)
        $query = Auth::user()->notifications();
        
        // Fetch notifications (limit for JSON/Dropdown, paginate for View)
        
        if ($request->wantsJson()) {
            $notifications = $query->take(20)->get()->map(function($n) {
                return [
                    'id' => $n->id,
                    'data' => [
                        'title' => $n->data['title'] ?? 'تنبيه',
                        'body' => $n->data['body'] ?? '',
                        'icon' => $n->data['icon'] ?? 'bi-bell',
                        'color' => $n->data['color'] ?? 'text-slate-500',
                        'link' => $n->data['link'] ?? '#',
                    ],
                    'created_at' => $n->created_at->toIso8601String(),
                    'read_at' => $n->read_at
                ];
            });
            return response()->json($notifications);
        }

        // 2. View Response
        $notifications = $query->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['status' => 'success']);
    }
}
