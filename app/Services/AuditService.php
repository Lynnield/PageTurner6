<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditService: Centralized audit logging for security events
 * Tracks login/logout, failed attempts, permission changes, and critical operations
 */
class AuditService
{
    /**
     * Log an audit event with rich metadata
     */
    public static function log(
        string $event,
        string $auditable_type = null,
        int $auditable_id = null,
        array $old_values = [],
        array $new_values = [],
        ?User $user = null
    ): Audit {
        $user = $user ?? Auth::user();

        $audit = new Audit();
        $audit->user_id = $user?->id;
        $audit->user_type = $user ? get_class($user) : null;
        $audit->event = $event;
        $audit->auditable_type = $auditable_type;
        $audit->auditable_id = $auditable_id;
        $audit->old_values = $old_values;
        $audit->new_values = $new_values;
        $audit->url = Request::fullUrl();
        $audit->ip_address = Request::ip() ?? '127.0.0.1';
        $audit->user_agent = Request::userAgent() ?? 'System';
        $audit->http_method = Request::method() ?? 'CLI';
        $audit->save();

        return $audit;
    }

    /**
     * Log a security event
     */
    public static function logSecurityEvent(string $event, string $details = ''): Audit
    {
        return self::log(
            $event,
            auditable_type: 'security',
            new_values: ['details' => $details]
        );
    }

    /**
     * Log import operation
     */
    public static function logImport(int $importLogId, int $rowsProcessed, int $rowsFailed): void
    {
        self::log(
            'import',
            auditable_type: 'ImportLog',
            auditable_id: $importLogId,
            new_values: [
                'processed' => $rowsProcessed,
                'failed' => $rowsFailed,
                'message' => "Import completed - {$rowsProcessed} rows processed, {$rowsFailed} failed"
            ]
        );
    }

    /**
     * Log export operation
     */
    public static function logExport(int $exportLogId, int $rowsExported, string $format): void
    {
        self::log(
            'export',
            auditable_type: 'ExportLog',
            auditable_id: $exportLogId,
            new_values: [
                'rows' => $rowsExported,
                'format' => $format,
                'message' => "Export completed - {$rowsExported} rows exported as {$format}"
            ]
        );
    }

    /**
     * Get audit trail with filters
     */
    public static function getTrail(array $filters = [])
    {
        $query = Audit::query();

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['model'])) {
            $query->where('model', $filters['model']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate(50);
    }
}
