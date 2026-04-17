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
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}

