<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('admin.audits.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div>
                            <x-input-label for="user_id" :value="__('User ID')" />
                            <x-text-input id="user_id" name="user_id" type="text" class="mt-1 block w-full" :value="request('user_id')" />
                        </div>
                        <div>
                            <x-input-label for="event" :value="__('Event Type')" />
                            <select id="event" name="event" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="">{{ __('All') }}</option>
                                @foreach($events as $event)
                                    <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>{{ ucfirst($event) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="auditable_type" :value="__('Target Model')" />
                            <select id="auditable_type" name="auditable_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="">{{ __('All') }}</option>
                                @foreach($models as $model)
                                    @if($model)
                                        <option value="{{ $model }}" {{ request('auditable_type') === $model ? 'selected' : '' }}>{{ class_basename($model) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="date_from" :value="__('Date From')" />
                            <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="request('date_from')" />
                        </div>
                        <div class="flex space-x-2">
                            <x-primary-button>{{ __('Filter') }}</x-primary-button>
                            <a href="{{ route('admin.audits.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">{{ __('Export CSV') }}</a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($audits as $audit)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $audit->created_at->format('M d, Y H:i:s') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $audit->user ? $audit->user->name : 'System' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($audit->event === 'created') bg-green-100 text-green-800
                                                @elseif($audit->event === 'updated') bg-blue-100 text-blue-800
                                                @elseif($audit->event === 'deleted') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($audit->event) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $audit->ip_address }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.audits.show', $audit) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View Details') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ __('No audit logs found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $audits->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>