<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        $audits = $query->latest()->paginate(20)->withQueryString();

        $events = Audit::select('event')->distinct()->pluck('event');
        $models = Audit::select('auditable_type')->distinct()->pluck('auditable_type');

        return view('admin.audits.index', compact('audits', 'events', 'models'));
    }

    public function show(Audit $audit)
    {
        $audit->load('user');
        return view('admin.audits.show', compact('audit'));
    }

    public function export(Request $request)
    {
        $query = $this->buildQuery($request);
        $audits = $query->latest()->get();

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=audit_logs_' . now()->format('Ymd_His') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['ID', 'User', 'Event', 'Target Model', 'Target ID', 'IP Address', 'Date', 'Old Values', 'New Values'];

        $callback = function () use ($audits, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($audits as $audit) {
                fputcsv($file, [
                    $audit->id,
                    $audit->user ? $audit->user->name : 'System',
                    $audit->event,
                    class_basename($audit->auditable_type),
                    $audit->auditable_id,
                    $audit->ip_address,
                    $audit->created_at->format('Y-m-d H:i:s'),
                    json_encode($audit->old_values),
                    json_encode($audit->new_values),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildQuery(Request $request): Builder
    {
        $query = Audit::query()->with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }
}
