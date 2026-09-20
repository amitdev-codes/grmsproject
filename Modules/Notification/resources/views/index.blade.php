<x-notification::layouts.master>
    <main style="max-width: 760px; margin: 40px auto; padding: 0 20px; font-family: Figtree, sans-serif; color: #172033;">
        <h1 style="margin-bottom: 24px;">Notifications</h1>

        @forelse ($notifications as $notification)
            @php($data = $notification->data)
            <article style="border: 1px solid #d9dee8; border-radius: 8px; margin-bottom: 12px; padding: 16px; background: {{ $notification->read_at ? '#fff' : '#f2f7ff' }};">
                <strong>{{ $data['title'] ?? 'Grievance notification' }}</strong>
                @if (!empty($data['message']))
                    <div style="margin-top: 6px;">{{ $data['message'] }}</div>
                @endif
                @if (!empty($data['reference_no']))
                    <div style="margin-top: 6px; color: #5e6879;">Reference: {{ $data['reference_no'] }}</div>
                @endif
                @if (!empty($data['reason']))
                    <div style="margin-top: 6px; white-space: pre-wrap;">Remarks: {{ $data['reason'] }}</div>
                @endif
                @if (!empty($data['action_url']))
                    <a href="{{ $data['action_url'] }}" style="display: inline-block; margin-top: 12px; color: #1d4ed8;">Open grievance</a>
                @endif
                <small style="display: block; margin-top: 8px; color: #5e6879;">{{ $notification->created_at->diffForHumans() }}</small>
            </article>
        @empty
            <p>No notifications yet.</p>
        @endforelse
    </main>
</x-notification::layouts.master>
