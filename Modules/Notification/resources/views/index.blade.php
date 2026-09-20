<x-notification::layouts.master>
    <main style="max-width: 760px; margin: 40px auto; padding: 0 20px; font-family: Figtree, sans-serif; color: #172033;">
        <h1 style="margin-bottom: 24px;">Notifications</h1>

        @forelse ($notifications as $notification)
            @php($data = $notification->data)
            <article style="border: 1px solid #d9dee8; border-radius: 8px; margin-bottom: 12px; padding: 16px; background: {{ $notification->read_at ? '#fff' : '#f2f7ff' }};">
                <strong>{{ str_ends_with($notification->type, 'GrievanceAllocated') ? 'Grievance allocated' : 'Grievance requires reallocation' }}</strong>
                @if (!empty($data['reference_no']))
                    <div style="margin-top: 6px;">Reference: {{ $data['reference_no'] }}</div>
                @endif
                <small style="display: block; margin-top: 8px; color: #5e6879;">{{ $notification->created_at->diffForHumans() }}</small>
            </article>
        @empty
            <p>No notifications yet.</p>
        @endforelse
    </main>
</x-notification::layouts.master>
