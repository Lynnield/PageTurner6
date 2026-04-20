<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Models\Audit as BaseAudit;

class Audit extends BaseAudit
{
    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'http_method',
        'request_uuid',
        'metadata',
        'checksum',
        'previous_checksum',
    ];

    protected $casts = [
        'metadata' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($audit) {
            $lastAudit = static::latest('id')->first();
            $audit->previous_checksum = $lastAudit ? $lastAudit->checksum : null;

            $payload = json_encode([
                'user_id' => $audit->user_id,
                'event' => $audit->event,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
                'previous_checksum' => $audit->previous_checksum,
            ]);

            $audit->checksum = hash_hmac('sha256', $payload, config('app.key'));
        });

        static::created(function ($audit) {
            $isCritical = false;

            // Permission changes / Role assignments
            if ($audit->auditable_type === User::class && $audit->event === 'updated') {
                if (isset($audit->new_values['role']) || isset($audit->new_values['permissions'])) {
                    $isCritical = true;
                }
            }

            // System settings
            if ($audit->event === 'settings_changed') {
                $isCritical = true;
            }

            if ($isCritical) {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\CriticalSecurityAlert($audit));
                }
            }
        });
    }

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}

