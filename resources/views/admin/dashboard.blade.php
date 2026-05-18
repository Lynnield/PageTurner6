<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white p-6 rounded shadow flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold mb-1">{{ __('System Maintenance') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Manually trigger an immediate backup of the database and files.') }}</p>
                </div>
                <form action="{{ route('admin.backup.run') }}" method="POST">
                    @csrf
                    <x-primary-button>{{ __('Run Backup Now') }}</x-primary-button>
                </form>
            </div>

            <!-- Administrative Tools -->
            <div class="bg-white p-6 rounded shadow mt-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">{{ __('Administrative Tools') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- User Management -->
                    <div class="p-4 border rounded hover:bg-gray-50 transition">
                        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            User Management
                        </h4>
                        <div class="flex flex-col gap-2 mt-3">
                            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                @csrf
                                <input type="file" name="file" class="text-xs w-full" required>
                                <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">Import</button>
                            </form>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.users.export', ['redact_pii' => 0]) }}" class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 flex-1 text-center">Full Export</a>
                                <a href="{{ route('admin.users.export', ['redact_pii' => 1]) }}" class="text-xs px-3 py-1.5 bg-gray-600 text-white rounded hover:bg-gray-700 flex-1 text-center">GDPR Redacted</a>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Reporting -->
                    <div class="p-4 border rounded hover:bg-gray-50 transition">
                        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                            Financial Reports
                        </h4>
                        <div class="flex flex-col gap-2 mt-3">
                            <a href="{{ route('admin.orders.export', ['type' => 'financial']) }}" class="text-xs px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-center">Revenue & Tax Report (Excel)</a>
                            <a href="{{ route('admin.orders.export', ['type' => 'admin']) }}" class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-center">Sales Summary (Excel)</a>
                        </div>
                    </div>

                    <!-- Security & Compliance -->
                    <div class="p-4 border rounded hover:bg-gray-50 transition">
                        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04kM12 20.935a11.952 11.952 0 00-4.597-9.335M12 20.935a11.952 11.952 0 004.597-9.335M12 20.935V11.6"></path></svg>
                            Security & Compliance
                        </h4>
                        <div class="flex flex-col gap-2 mt-3">
                            <a href="{{ route('admin.audits.export') }}" class="text-xs px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-center">Export Audit Trail (CSV)</a>
                            <a href="{{ route('admin.audits.index') }}" class="text-xs px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-center">View Audit Dashboard</a>
                        </div>
                    </div>

                    <!-- Book Management Tools -->
                    <div class="p-4 border rounded hover:bg-gray-50 transition">
                        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Book Inventory Tools
                        </h4>
                        <div class="flex flex-col gap-2 mt-3">
                            <div class="flex gap-2">
                                <button onclick="document.getElementById('import-modal').classList.remove('hidden')" class="text-xs px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 flex-1">Import Books</button>
                                <button onclick="document.getElementById('export-modal').classList.remove('hidden')" class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 flex-1">Export Books</button>
                            </div>
                            <a href="{{ route('admin.books.index') }}" class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-center">Manage Book Catalog</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Users') }}</div><div class="text-3xl font-bold">{{ $metrics['users'] }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Books') }}</div><div class="text-3xl font-bold">{{ $metrics['books'] }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Categories') }}</div><div class="text-3xl font-bold">{{ $metrics['categories'] }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Orders') }}</div><div class="text-3xl font-bold">{{ $metrics['orders'] }}</div></div>
            </div>

            <!-- Data Management Widgets -->
            <div class="bg-white p-6 rounded shadow mt-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">{{ __('Data Management Overview') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Import/Export Status -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">{{ __('Import/Export Status') }}</h4>
                        <div class="text-sm space-y-1 mb-3">
                            <div class="flex justify-between"><span>Total Imports:</span> <span class="font-bold">{{ $importStats['total'] }}</span></div>
                            @if($importStats['processing'] > 0)
                                <div class="flex justify-between text-blue-600 font-bold animate-pulse">
                                    <span>Processing Imports:</span> <span>{{ $importStats['processing'] }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between"><span>Failed Imports:</span> <span class="font-bold text-red-600">{{ $importStats['failed'] }}</span></div>
                            <div class="flex justify-between"><span>Total Exports:</span> <span class="font-bold">{{ $exportStats['total'] }}</span></div>
                            @if($exportStats['processing'] > 0)
                                <div class="flex justify-between text-indigo-600 font-bold animate-pulse">
                                    <span>Processing Exports:</span> <span>{{ $exportStats['processing'] }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('admin.imports.index') }}" class="text-xs text-center px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">View Import History</a>
                            <a href="{{ route('admin.exports.index') }}" class="text-xs text-center px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">View Export History</a>
                        </div>
                        <h5 class="text-xs font-bold text-gray-500 uppercase mt-4">{{ __('Recent Activity') }}</h5>
                        <ul class="text-sm divide-y text-gray-600 mt-1">
                            @foreach($importStats['recent'] as $log)
                                <li class="py-1 flex justify-between">
                                    <span class="truncate w-32">{{ $log->original_filename }}</span> 
                                    <span class="text-xs px-2 rounded-full 
                                        {{ $log->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $log->status }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Audit Log Summary -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">{{ __('Recent Audit Events') }}</h4>
                        <ul class="text-sm divide-y text-gray-600">
                            @forelse($auditStats['recent'] as $audit)
                                <li class="py-2">
                                    <div class="flex justify-between font-medium text-gray-900">
                                        <span>{{ ucfirst($audit->event) }}</span>
                                        <span class="text-xs text-gray-500">{{ $audit->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-xs">
                                        User: {{ $audit->user->name ?? 'System' }} | Target: {{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}
                                    </div>
                                </li>
                            @empty
                                <li class="py-2 text-gray-400">No recent audit logs.</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('admin.audits.index') }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">View all audits &rarr;</a>
                    </div>

                    <!-- System Health -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">{{ __('System Health') }}</h4>
                        <div class="text-sm space-y-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="font-medium text-gray-600">Database Size</span>
                                <span class="font-bold">{{ $systemHealth['db_size'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="font-medium text-gray-600">Backup Status</span>
                                <span class="font-bold {{ $systemHealth['backup_health'] === 'Healthy' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $systemHealth['backup_health'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="font-medium text-gray-600">Pending Jobs</span>
                                <span class="font-bold">{{ $systemHealth['jobs_queue'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-{{ $systemHealth['failed_jobs'] > 0 ? 'red' : 'green' }}-50 rounded">
                                <span class="font-medium text-{{ $systemHealth['failed_jobs'] > 0 ? 'red' : 'green' }}-600">Failed Jobs</span>
                                <span class="font-bold text-{{ $systemHealth['failed_jobs'] > 0 ? 'red' : 'green' }}-600">{{ $systemHealth['failed_jobs'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 border-t pt-6">
                    <!-- Notification Stats -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">{{ __('Notification Statistics') }}</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-3 rounded text-center">
                                <div class="text-xs text-blue-600 uppercase font-bold">Unread</div>
                                <div class="text-xl font-bold text-blue-800">{{ $systemHealth['notification_stats']['unread'] }}</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded text-center">
                                <div class="text-xs text-gray-600 uppercase font-bold">Total</div>
                                <div class="text-xl font-bold text-gray-800">{{ $systemHealth['notification_stats']['total'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- API Usage -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">{{ __('API Usage (24h)') }}</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-purple-50 p-3 rounded text-center">
                                <div class="text-xs text-purple-600 uppercase font-bold">Total API Calls</div>
                                <div class="text-xl font-bold text-purple-800">{{ $systemHealth['api_usage']['total_requests'] }}</div>
                            </div>
                            <div class="bg-orange-50 p-3 rounded text-center">
                                <div class="text-xs text-orange-600 uppercase font-bold">Rate Limit Hits</div>
                                <div class="text-xl font-bold text-orange-800">{{ $systemHealth['api_usage']['recent_429s'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow mt-6">
                <h3 class="text-lg font-bold mb-4">{{ __('Order Status Summary') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach(['pending','processing','completed','cancelled'] as $s)
                        <div class="p-4 border rounded"><div class="text-sm text-gray-500">{{ ucfirst($s) }}</div><div class="text-2xl font-bold">{{ $statusSummary[$s] ?? 0 }}</div></div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Recent Orders') }}</h3>
                <div class="divide-y">
                    @forelse($recentOrders as $order)
                        <div class="py-3 flex justify-between">
                            <div>#{{ $order->id }} — {{ $order->user->name }}</div>
                            <div class="text-sm">{{ ucfirst($order->status) }}</div>
                        </div>
                    @empty
                        <div class="py-3 text-gray-500">{{ __('No recent orders') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Recent Reviews') }}</h3>
                <div class="divide-y">
                    @forelse($recentReviews as $review)
                        <div class="py-3">
                            <div class="font-semibold">{{ $review->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ __('Book ID:') }} {{ $review->book->id }}</div>
                            <div>{{ $review->rating }} ★ — {{ \Illuminate\Support\Str::limit($review->comment, 80) }}</div>
                        </div>
                    @empty
                        <div class="py-3 text-gray-500">{{ __('No recent reviews') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
