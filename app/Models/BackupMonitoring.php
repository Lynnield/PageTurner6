<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupMonitoring extends Model
{
    protected $table = 'backup_monitoring';

    protected $fillable = [
        'name',
        'status',
        'disk',
        'size_bytes',
        'healthy',
        'started_at',
        'finished_at',
        'message',
        'meta',
    ];

    protected $casts = [
        'healthy' => 'boolean',
        'meta' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'size_bytes' => 'integer',
    ];
}

