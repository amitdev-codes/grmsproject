<x-notification::layouts.master>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f7fb; color: #172033; font-family: Figtree, sans-serif; }
        .page { max-width: 960px; margin: 40px auto; padding: 0 20px; }
        .header, .toolbar, .notification, .notification-main { display: flex; align-items: center; }
        .header { justify-content: space-between; gap: 16px; margin-bottom: 22px; }
        h1 { margin: 0 0 5px; font-size: 26px; }
        .muted { color: #64748b; font-size: 14px; }
        .toolbar { justify-content: space-between; gap: 14px; margin-bottom: 16px; }
        .tabs { display: flex; gap: 7px; flex-wrap: wrap; }
        a, button { font: inherit; }
        .tab, .button { border: 1px solid #d8dee9; border-radius: 7px; padding: 8px 12px; color: #334155; background: #fff; text-decoration: none; cursor: pointer; font-size: 13px; }
        .tab.active { background: #172033; border-color: #172033; color: #fff; }
        .button.primary { color: #fff; background: #2563eb; border-color: #2563eb; }
        .button:hover, .tab:hover { filter: brightness(.97); }
        .list { overflow: hidden; border: 1px solid #e0e5ed; border-radius: 10px; background: #fff; }
        .notification { justify-content: space-between; align-items: flex-start; gap: 20px; padding: 18px 20px; border-bottom: 1px solid #edf0f5; }
        .notification:last-child { border-bottom: 0; }
        .notification.unread { background: #f2f7ff; }
        .notification-main { align-items: flex-start; gap: 12px; min-width: 0; }
        .indicator { flex: 0 0 9px; width: 9px; height: 9px; margin-top: 6px; border-radius: 50%; background: #2563eb; }
        .indicator.read { background: #cbd5e1; }
        .content { min-width: 0; }
        .title { font-weight: 600; }
        .message { margin-top: 5px; line-height: 1.5; white-space: pre-wrap; }
        .reference { margin-top: 7px; color: #475569; font-size: 13px; }
        .time { display: block; margin-top: 8px; color: #64748b; font-size: 12px; }
        .state { display: inline-flex; margin-top: 8px; padding: 3px 8px; border-radius: 99px; background: #dbeafe; color: #1d4ed8; font-size: 11px; font-weight: 600; }
        .state.read { background: #eef2f7; color: #64748b; }
        .actions { display: flex; flex: 0 0 auto; align-items: center; gap: 10px; }
        .actions a { color: #2563eb; font-size: 13px; text-decoration: none; }
        .actions a:hover { text-decoration: underline; }
        .empty { padding: 54px 20px; text-align: center; }
        .pagination { margin-top: 18px; }
        .pagination nav { display: flex; justify-content: space-between; align-items: center; }
        .pagination a, .pagination span { color: #334155; text-decoration: none; }
        @media (max-width: 640px) {
            .page { margin-top: 24px; padding: 0 12px; }
            .header, .toolbar { align-items: flex-start; flex-direction: column; }
            .notification { padding: 15px 13px; }
        }
    </style>

    <main class="page">
        <header class="header">
            <div>
                <h1>Notifications</h1>
                <div class="muted">{{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }}</div>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notification.read-all') }}">
                    @csrf
                    <button class="button primary" type="submit">Mark all as read</button>
                </form>
            @endif
        </header>

        <div class="toolbar">
            <nav class="tabs" aria-label="Filter notifications">
                @foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label)
                    <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('notification.index', ['status' => $key]) }}">
                        {{ $label }}{{ $key === 'unread' ? " ({$unreadCount})" : '' }}
                    </a>
                @endforeach
            </nav>
            <span class="muted">{{ $notifications->total() }} notification{{ $notifications->total() === 1 ? '' : 's' }}</span>
        </div>

        <section class="list" aria-label="Notification list">
            @forelse ($notifications as $notification)
                @php($data = $notification->data)
                <article class="notification {{ $notification->read_at ? '' : 'unread' }}">
                    <div class="notification-main">
                        <span class="indicator {{ $notification->read_at ? 'read' : '' }}" aria-hidden="true"></span>
                        <div class="content">
                            <div class="title">{{ $data['title'] ?? 'Grievance notification' }}</div>
                            @if (!empty($data['message']))
                                <div class="message">{{ $data['message'] }}</div>
                            @endif
                            @if (!empty($data['reference_no']))
                                <div class="reference">Reference: {{ $data['reference_no'] }}</div>
                            @endif
                            @if (!empty($data['reason']))
                                <div class="message">Remarks: {{ $data['reason'] }}</div>
                            @endif
                            <span class="state {{ $notification->read_at ? 'read' : '' }}">
                                {{ $notification->read_at ? 'Read' : 'Unread' }}
                            </span>
                            <small class="time">{{ $notification->created_at->diffForHumans() }} · {{ $notification->created_at->format('M j, Y g:i A') }}</small>
                        </div>
                    </div>
                    <div class="actions">
                        @if (!empty($data['action_url']))
                            <a href="{{ $data['action_url'] }}">Open</a>
                        @endif
                        <form method="POST" action="{{ route($notification->read_at ? 'notification.unread' : 'notification.read', $notification->id) }}">
                            @csrf
                            <button class="button" type="submit">{{ $notification->read_at ? 'Mark unread' : 'Mark read' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <div class="title">No {{ $status === 'all' ? '' : $status }} notifications</div>
                    <p class="muted">Notifications will appear here when there is an update for you.</p>
                </div>
            @endforelse
        </section>

        @if ($notifications->hasPages())
            <div class="pagination">{{ $notifications->links() }}</div>
        @endif
    </main>
</x-notification::layouts.master>
