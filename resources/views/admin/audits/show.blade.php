<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Log Details') }} #{{ $audit->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 shadow sm:rounded-lg grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="block text-sm text-gray-500 font-bold">Date</span>
                    <span class="text-gray-900">{{ $audit->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                <div>
                    <span class="block text-sm text-gray-500 font-bold">User</span>
                    <span class="text-gray-900">{{ $audit->user ? $audit->user->name : 'System' }} (ID: {{ $audit->user_id ?? 'N/A' }})</span>
                </div>
                <div>
                    <span class="block text-sm text-gray-500 font-bold">Event</span>
                    <span class="text-gray-900">{{ ucfirst($audit->event) }}</span>
                </div>
                <div>
                    <span class="block text-sm text-gray-500 font-bold">Target Model</span>
                    <span class="text-gray-900">{{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</span>
                </div>
                <div>
                    <span class="block text-sm text-gray-500 font-bold">IP Address</span>
                    <span class="text-gray-900">{{ $audit->ip_address }}</span>
                </div>
                <div class="col-span-3">
                    <span class="block text-sm text-gray-500 font-bold">User Agent</span>
                    <span class="text-gray-900">{{ $audit->user_agent }}</span>
                </div>
                <div class="col-span-4">
                    <span class="block text-sm text-gray-500 font-bold">URL</span>
                    <span class="text-gray-900">{{ $audit->http_method }} {{ $audit->url }}</span>
                </div>
            </div>

            @if($audit->event === 'updated')
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4">Value Changes (Diff)</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-red-600 mb-2 border-b pb-1">Old Values</h4>
                        <pre class="bg-gray-50 p-4 rounded text-sm overflow-auto max-h-96">@php echo json_encode($audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp</pre>
                    </div>
                    <div>
                        <h4 class="font-semibold text-green-600 mb-2 border-b pb-1">New Values</h4>
                        <pre class="bg-gray-50 p-4 rounded text-sm overflow-auto max-h-96">@php echo json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp</pre>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4">Payload</h3>
                <pre class="bg-gray-50 p-4 rounded text-sm overflow-auto">@php echo json_encode($audit->new_values ?: $audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp</pre>
            </div>
            @endif
            
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4">Integrity Check</h3>
                <p class="text-sm text-gray-600 mb-2">This log is protected against tampering using an HMAC SHA-256 cryptographic hash calculated at the moment of creation.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <span class="block text-sm text-gray-500 font-bold">Checksum</span>
                        <code class="text-xs break-all bg-gray-100 px-2 py-1 rounded">{{ $audit->checksum ?? 'N/A' }}</code>
                    </div>
                    <div>
                        <span class="block text-sm text-gray-500 font-bold">Previous Checksum</span>
                        <code class="text-xs break-all bg-gray-100 px-2 py-1 rounded">{{ $audit->previous_checksum ?? 'N/A' }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>