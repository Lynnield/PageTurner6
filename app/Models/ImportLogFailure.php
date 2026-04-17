<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLogFailure extends Model
{
    protected $fillable = [
        'import_log_id',
        'row_number',
        'attribute',
        'errors',
        'values',
    ];

    protected $casts = [
        'errors' => 'array',
        'values' => 'array',
    ];

    public function importLog(): BelongsTo
    {
        return $this->belongsTo(ImportLog::class);
    }
}

