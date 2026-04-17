<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportLog extends Model
{
    protected $fillable = [
        'user_id',
        'export_type',
        'format',
        'filters',
        'columns',
        'status',
        'total_rows',
        'file_disk',
        'stored_path',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'total_rows' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

