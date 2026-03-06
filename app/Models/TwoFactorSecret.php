<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorSecret extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'secret',
        'recovery_codes',
        'enabled_at',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'enabled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
