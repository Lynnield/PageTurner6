<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTaskRun extends Model
{
    protected $fillable = [
        'task',
        'command',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'output',
        'error',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms' => 'integer',
    ];
}

