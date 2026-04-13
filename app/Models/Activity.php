<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $table = 'activities';

    protected $fillable = [
        'user_id',
        'action',
        'method',
        'url',
        'route',
        'payload',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Relasi ke user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper: ambil nama user (biar aman di blade)
     */
    public function getUserNameAttribute()
    {
        return $this->user?->name ?? 'Guest';
    }

    /**
     * Helper: ringkas action (optional)
     */
    public function getActionLabelAttribute()
    {
        return strtoupper($this->action);
    }
}
