<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditSession extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'session_id',
        'login_at',
        'logout_at',
        'duration_seconds',
        'auth_method',
        'token_id',
        'device_type',
        'browser',
        'browser_version',
        'operating_system',
        'platform',
        'user_agent',
        'ip_address',
        'country',
        'city',
        'timezone',
        'isp',
        'is_active',
        'ended_reason',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
            'is_active' => 'boolean',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * Boot the model — auto-generate UUID on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (AuditSession $session) {
            if (empty($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * End this session.
     */
    public function endSession(string $reason = 'logout'): void
    {
        $this->update([
            'logout_at' => now(),
            'duration_seconds' => $this->login_at ? now()->diffInSeconds($this->login_at) : null,
            'is_active' => false,
            'ended_reason' => $reason,
        ]);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            if ($this->is_active && $this->login_at) {
                $seconds = now()->diffInSeconds($this->login_at);
            } else {
                return 'N/A';
            }
        } else {
            $seconds = $this->duration_seconds;
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
        }
        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        }
        return sprintf('%ds', $secs);
    }
}
