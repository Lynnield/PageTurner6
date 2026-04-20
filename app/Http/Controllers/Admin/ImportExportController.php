<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookExportRequest;
use App\Http\Requests\Admin\BookImportRequest;
use App\Models\ExportLog;
use App\Models\ImportLog;
use App\Services\ExportService;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ImportExportController extends Controller
{
    public function __construct(
        private readonly ImportService $importService,
        private readonly ExportService $exportService
    ) {}

    public function importBooks(BookImportRequest $request)
    {
        $log = $this->importService->queueBookImport(
            $request->file('file'),
            $request->string('mode')->toString(),
            $request->user()
        );

        return redirect()
            ->back()
            ->with('status', "Import queued (log #{$log->id}).");
    }

    public function downloadBookTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=books_import_template.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['isbn', 'title', 'author', 'price', 'stock', 'category', 'description'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['9780743273565', 'The Great Gatsby', 'F. Scott Fitzgerald', '15.99', '100', 'Fiction', 'A classic novel.']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function listImports(Request $request)
    {
        $imports = ImportLog::query()
            ->where('import_type', 'books')
            ->latest()
            ->paginate(20);

        return view('admin.imports.index', compact('imports'));
    }

    public function showImport(ImportLog $importLog)
    {
        $importLog->load(['failures' => function ($q) {
            $q->latest()->limit(100);
        }]);

        return view('admin.imports.show', compact('importLog'));
    }

    public function exportBooks(BookExportRequest $request)
    {
        $cols = $request->input('columns');
        $requestedColumns = is_string($cols) ? explode(',', $cols) : [];
        $columns = $this->exportService->normalizeBookColumns($requestedColumns);
        $filters = $this->exportService->normalizeBookFilters($request->validated());

        $result = $this->exportService->exportBooks($filters, $columns, $request->string('format')->toString(), $request->user());

        if (($result['mode'] ?? null) === 'download') {
            return $result['response'];
        }

        /** @var \App\Models\ExportLog $log */
        $log = $result['log'];

        return redirect()
            ->back()
            ->with('status', "Export queued (log #{$log->id}).");
    }

    public function exportOrders(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'user_id']);
        $type = $request->input('type', 'admin'); // admin|financial
        
        $export = new \App\Exports\OrderExport($filters, $type);
        
        $filename = "orders_export_" . now()->format('Y_m_d_His') . ".xlsx";
        
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx']
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UserImport, $request->file('file'));

        return redirect()->back()->with('status', 'Users imported successfully.');
    }

    public function exportUsers(Request $request)
    {
        $redactPii = $request->boolean('redact_pii', true);
        $export = new \App\Exports\UserExport($redactPii);
        
        $filename = "users_export_" . now()->format('Y_m_d_His') . ".xlsx";
        
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }

    public function listExports(Request $request)
    {
        $exports = ExportLog::query()
            ->latest()
            ->paginate(20);

        return view('admin.exports.index', compact('exports'));
    }

    public function downloadExport(ExportLog $exportLog)
    {
        $path = $this->exportService->downloadExport($exportLog);

        return Response::download($path, basename($path));
    }
}

