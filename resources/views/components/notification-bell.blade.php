<!-- Notification Bell Component -->
<div class="relative">
    <!-- Bell Icon Button -->
    <button id="notification-bell" onclick="toggleNotificationDropdown()" 
        class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
        <!-- Bell Icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        <!-- Unread Badge -->
        <span id="notification-badge" class="absolute top-1 right-1 flex items-center justify-center h-5 w-5 bg-red-100 text-red-600 text-xs font-bold rounded-full border border-red-200 hidden">
            <span id="badge-count">0</span>
        </span>
    </button>

    <!-- Dropdown Menu -->
    <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 max-h-96 flex flex-col">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-700">
                View all
            </a>
        </div>

        <!-- Notifications List -->
        <div id="notification-list" class="flex-1 overflow-y-auto">
            <!-- Loaded dynamically -->
            <div class="px-4 py-4 text-center text-sm text-gray-500">
                <p>Loading notifications...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between bg-gray-50 rounded-b-lg">
            <button onclick="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                Mark all as read
            </button>
            <button onclick="closeNotificationDropdown()" class="text-xs text-gray-500 hover:text-gray-700">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notification-dropdown');
    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        loadNotifications();
    } else {
        dropdown.classList.add('hidden');
    }
}

function closeNotificationDropdown() {
    document.getElementById('notification-dropdown').classList.add('hidden');
}

function loadNotifications() {
    fetch('{{ route('notifications.latest') }}?limit=10')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('notification-list');
            const badge = document.getElementById('badge-count');
            const badgeContainer = document.getElementById('notification-badge');
            
            const count = data.count || 0;
            badge.textContent = count;
            if (count > 0) {
                badgeContainer.classList.remove('hidden');
            } else {
                badgeContainer.classList.add('hidden');
            }

            if (!data.notifications || data.notifications.length === 0) {
                list.innerHTML = '<div class="px-4 py-4 text-center text-sm text-gray-500"><p>No notifications</p></div>';
                return;
            }

            list.innerHTML = data.notifications.map(n => {
                let message = 'New Notification';
                try {
                    // Laravel stores data as a JSON string or object depending on driver/version
                    const notificationData = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
                    message = notificationData.message || message;
                } catch (e) {
                    console.error('Error parsing notification data:', e);
                }

                return `
                    <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition">
                        <p class="text-sm ${n.read_at ? 'text-gray-600' : 'font-semibold text-gray-900'}">
                            ${message}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            ${new Date(n.created_at).toLocaleString()}
                        </p>
                        <div class="flex gap-2 mt-2">
                            ${!n.read_at ? `<button onclick="markAsRead('${n.id}', event)" class="text-xs px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded transition">Mark read</button>` : ''}
                            <button onclick="deleteNotification('${n.id}', event)" class="text-xs px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition">Delete</button>
                        </div>
                    </div>
                `;
            }).join('');
        })
        .catch(err => {
            console.error('Notification error:', err);
            document.getElementById('notification-list').innerHTML = '<div class="px-4 py-4 text-center text-sm text-red-500"><p>Error loading notifications</p></div>';
        });
}

function markAsRead(notificationId, event) {
    if (event) event.stopPropagation();
    let url = '{{ route('notifications.mark-read', ['id' => ':id']) }}'.replace(':id', notificationId);
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(res => res.json()).then(() => loadNotifications());
}

function markAllAsRead() {
    fetch('{{ route('notifications.mark-all-read') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(res => res.json()).then(() => loadNotifications());
}

function deleteNotification(notificationId, event) {
    if (event) event.stopPropagation();
    if (confirm('Delete this notification?')) {
        let url = '{{ route('notifications.destroy', ['id' => ':id']) }}'.replace(':id', notificationId);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(() => loadNotifications());
    }
}

// Load notifications on component load
loadNotifications();

// Refresh every 10 seconds for real-time feel
setInterval(loadNotifications, 10000);

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const bell = document.getElementById('notification-bell');
    const dropdown = document.getElementById('notification-dropdown');
    if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
