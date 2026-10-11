<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $notification->markAsRead();

        $actionUrl = $notification->data['action_url'] ?? null;
        if (is_string($actionUrl)) {
            $parts = parse_url($actionUrl);
            $allowedHosts = array_filter([
                $request->getHost(),
                parse_url((string) config('app.url'), PHP_URL_HOST),
            ]);
            $path = $parts['path'] ?? null;

            if (
                $path
                && str_starts_with($path, '/')
                && ! str_starts_with($path, '//')
                && (! isset($parts['host']) || in_array($parts['host'], $allowedHosts, true))
            ) {
                $target = $path.(! empty($parts['query']) ? '?'.$parts['query'] : '');

                return redirect()->to($target);
            }
        }

        return redirect()->route('notification.index');
    }

    public function markUnread(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsUnread();

        return back()->with('success', 'Notification marked as unread.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
