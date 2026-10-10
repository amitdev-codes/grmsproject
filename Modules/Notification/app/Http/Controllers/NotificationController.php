<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->validate([
            'status' => ['nullable', 'in:all,unread,read'],
        ])['status'] ?? 'all';

        $notifications = $request->user()->notifications()
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('notification::index', [
            'notifications' => $notifications,
            'status' => $status,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): \Illuminate\Http\RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markUnread(Request $request, string $notification): \Illuminate\Http\RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsUnread();

        return back()->with('success', 'Notification marked as unread.');
    }

    public function markAllRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
