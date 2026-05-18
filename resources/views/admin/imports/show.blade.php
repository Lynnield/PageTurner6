<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Import Details: #{{ $importLog->id }}
            </h2>
            <a href="{{ route('admin.imports.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to list</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-bold border-b pb-2 mb-4">Summary</h3>
                        <dl class="grid grid-cols-2 gap-y-2 text-sm">
                            <dt class="text-gray-500 font-medium">Original File:</dt>
                            <dd class="text-gray-900">{{ $importLog->original_filename }}</dd>

                            <dt class="text-gray-500 font-medium">Status:</dt>
                            <dd>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $importLog->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $importLog->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $importLog->status === 'queued' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $importLog->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($importLog->status) }}
                                </span>
                            </dd>

                            <dt class="text-gray-500 font-medium">Mode:</dt>
                            <dd class="text-gray-900">{{ ucfirst($importLog->mode) }}</dd>

                            <dt class="text-gray-500 font-medium">Date:</dt>
                            <dd class="text-gray-900">{{ $importLog->created_at->format('Y-m-d H:i:s') }}</dd>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold border-b pb-2 mb-4">Progress</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700">Completion</span>
                                    <span class="text-gray-600">
                                        {{ $importLog->total_rows > 0 ? round(($importLog->processed_rows / $importLog->total_rows) * 100) : 0 }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" 
                                         style="width: {{ $importLog->total_rows > 0 ? ($importLog->processed_rows / $importLog->total_rows) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-gray-50 p-2 rounded">
                                    <div class="text-xs text-gray-500 uppercase">Total</div>
                                    <div class="text-lg font-bold">{{ $importLog->total_rows }}</div>
                                </div>
                                <div class="bg-green-50 p-2 rounded">
                                    <div class="text-xs text-green-600 uppercase">Success</div>
                                    <div class="text-lg font-bold text-green-700">{{ $importLog->success_rows }}</div>
                                </div>
                                <div class="bg-red-50 p-2 rounded">
                                    <div class="text-xs text-red-600 uppercase">Failed</div>
                                    <div class="text-lg font-bold text-red-700">{{ $importLog->failed_rows }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($importLog->error_message)
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                        <p class="font-bold mb-1 text-red-800">System Error:</p>
                        {{ $importLog->error_message }}
                    </div>
                @endif
            </div>

            @if($importLog->failures->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold border-b pb-2 mb-4">Row Failures (Recent 100)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Row #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Errors</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data Snapshot</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($importLog->failures as $failure)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900 font-bold">{{ $failure->row }}</td>
                                        <td class="px-4 py-2 text-sm text-red-600">
                                            <ul class="list-disc list-inside">
                                                @foreach($failure->errors as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-500 italic">
                                            @foreach($failure->values as $key => $val)
                                                <span class="font-bold">{{ $key }}:</span> {{ Str::limit($val, 20) }}@if(!$loop->last), @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
