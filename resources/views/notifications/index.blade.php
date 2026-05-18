<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Your Notifications</h3>
                        @if($notifications->count() > 0)
                            <div class="flex gap-2">
                                <button onclick="markAllAsRead()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                    Mark all as read
                                </button>
                                <button onclick="deleteAllNotifications()" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition">
                                    Delete all
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Notifications List -->
                    <div class="divide-y divide-gray-200">
                        @forelse($notifications as $notification)
                            <div class="py-4 flex items-start justify-between" id="notification-{{ $notification->id }}">
                                <div class="flex-1">
                                    <p class="text-sm {{ $notification->read_at ? 'text-gray-600' : 'font-semibold text-gray-900' }}">
                                        @php
                                            $msg = 'Notification';
                                            try {
                                                $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                                                $msg = $data['message'] ?? $msg;
                                            } catch (\Exception $e) {}
                                        @endphp
                                        {{ $msg }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    @if(!$notification->read_at)
                                        <button onclick="markAsRead('{{ $notification->id }}')" 
                                            class="text-xs px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded transition">
                                            Mark as read
                                        </button>
                                    @endif
                                    <button onclick="deleteNotification('{{ $notification->id }}')" 
                                        class="text-xs px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                                <p class="mt-1 text-sm text-gray-500">You don't have any notifications yet.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function markAsRead(notificationId) {
        let url = '{{ route('notifications.mark-read', ['id' => ':id']) }}'.replace(':id', notificationId);
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function markAllAsRead() {
        fetch('{{ route('notifications.mark-all-read') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function deleteNotification(notificationId) {
        if (confirm('Delete this notification?')) {
            let url = '{{ route('notifications.destroy', ['id' => ':id']) }}'.replace(':id', notificationId);
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }

    function deleteAllNotifications() {
        if (confirm('Delete all notifications? This cannot be undone.')) {
            fetch('{{ route('notifications.destroy-all') }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }
    </script>
</x-app-layout>
