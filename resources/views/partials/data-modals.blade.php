<!-- Import Modal -->
<div id="import-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Import Books</h3>
            <form action="{{ route('admin.books.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 text-left">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">File (CSV/XLSX)</label>
                    <input type="file" name="file" accept=".csv,.xlsx" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Duplicate Handling</label>
                    <select name="mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="skip">Skip duplicates</option>
                        <option value="update">Update existing</option>
                    </select>
                </div>
                <div class="flex justify-between gap-4 mt-6">
                    <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">Start Import</button>
                </div>
            </form>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('admin.books.import.template') }}" class="text-sm text-indigo-600 hover:underline">Download Template</a>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="export-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Export Books</h3>
            <form action="{{ route('admin.books.export') }}" method="POST" class="mt-4 text-left">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Format</label>
                    <select name="format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="xlsx">Excel (XLSX)</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Columns (comma separated or empty for all)</label>
                    <input type="text" name="columns" placeholder="id,isbn,title,price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="flex justify-between gap-4 mt-6">
                    <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">Start Export</button>
                </div>
            </form>
        </div>
    </div>
</div>
