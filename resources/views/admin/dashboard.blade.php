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
                            <div class="flex justify-between"><span>Failed Imports:</span> <span class="font-bold text-red-600">{{ $importStats['failed'] }}</span></div>
                            <div class="flex justify-between"><span>Total Exports:</span> <span class="font-bold">{{ $exportStats['total'] }}</span></div>
                        </div>
                        <h5 class="text-xs font-bold text-gray-500 uppercase">{{ __('Recent Imports') }}</h5>
                        <ul class="text-sm divide-y text-gray-600 mt-1">
                            @forelse($importStats['recent'] as $log)
                                <li class="py-1 flex justify-between"><span>{{ Str::limit($log->original_filename, 15) }}</span> <span>{{ $log->status }}</span></li>
                            @empty
                                <li class="py-1 text-gray-400">No recent imports.</li>
                            @endforelse
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
                        <div class="text-sm space-y-3">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="font-medium text-gray-600">Database Size</span>
                                <span class="font-bold">{{ $systemHealth['db_size'] }}</span>
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
