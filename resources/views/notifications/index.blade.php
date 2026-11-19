@extends('layouts.admin')

@section('section_label', 'Notifications')
@section('section_hint', 'Send push notifications to customers\' phones. They also appear in the app\'s notification list.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">Notifications</h1>
            <p class="subtitle">{{ number_format($deviceCount) }} customer phone(s) can currently receive notifications.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('notifications.create') }}">Send Notification</a>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Notification</th>
                    <th>Linked Product</th>
                    <th>Status</th>
                    <th>Delivered</th>
                    <th>Sent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notifications as $notification)
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if ($notification->imageUrl())
                                    <img class="thumb" src="{{ $notification->imageUrl() }}" alt="">
                                @endif
                                <div>
                                    <div class="strong">{{ $notification->title }}</div>
                                    <div class="muted">{{ \Illuminate\Support\Str::limit($notification->body, 90) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $notification->product?->name ?? '—' }}</td>
                        <td><span class="status">{{ $notification->status }}</span></td>
                        <td>
                            {{ number_format($notification->sent_count) }} phone(s)
                            @if ($notification->failed_count > 0)
                                <div class="muted">{{ number_format($notification->failed_count) }} failed</div>
                            @endif
                        </td>
                        <td>{{ ($notification->sent_at ?? $notification->created_at)->format('d M Y, h:i A') }}</td>
                        <td>
                            <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Delete this notification? It will be removed from the mobile app\'s notification list.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger-soft btn-small" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No notifications sent yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($notifications->hasPages())
        <div class="actions" style="margin-top: 16px;">
            @if ($notifications->previousPageUrl())
                <a class="btn btn-outline btn-small" href="{{ $notifications->previousPageUrl() }}">Previous</a>
            @endif
            <span class="muted">Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span>
            @if ($notifications->nextPageUrl())
                <a class="btn btn-outline btn-small" href="{{ $notifications->nextPageUrl() }}">Next</a>
            @endif
        </div>
    @endif
@endsection
