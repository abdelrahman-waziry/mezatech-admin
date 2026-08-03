<?php

namespace App\Models;

use App\Enums\Audit\AuditAction;
use App\Enums\Audit\AuditCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    /**
     * Audit logs are immutable — disable updated_at tracking.
     */
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'session_id',
        'action',
        'category',
        'resource',
        'resource_id',
        'previous_value',
        'new_value',
        'success',
        'error_message',
        'ip_address',
        'country',
        'city',
        'timezone',
        'isp',
        'browser',
        'browser_version',
        'operating_system',
        'device_type',
        'user_agent',
        'request_url',
        'http_method',
        'response_status',
        'execution_time_ms',
        'is_suspicious',
        'suspicious_reason',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_value' => 'array',
            'new_value' => 'array',
            'metadata' => 'array',
            'success' => 'boolean',
            'is_suspicious' => 'boolean',
            'response_status' => 'integer',
            'execution_time_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot the model — auto-generate UUID on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            if (empty($log->uuid)) {
                $log->uuid = (string) Str::uuid();
            }
            if (empty($log->created_at)) {
                $log->created_at = now();
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

    public function scopeSuspicious(Builder $query): Builder
    {
        return $query->where('is_suspicious', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAction(Builder $query, string|AuditAction $action): Builder
    {
        $value = $action instanceof AuditAction ? $action->value : $action;
        return $query->where('action', $value);
    }

    public function scopeForCategory(Builder $query, string|AuditCategory $category): Builder
    {
        $value = $category instanceof AuditCategory ? $category->value : $category;
        return $query->where('category', $value);
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->where('created_at', '>=', $from))
            ->when($to, fn (Builder $q) => $q->where('created_at', '<=', $to));
    }

    public function scopeForIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('success', true);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('success', false);
    }

    public function scopeForResource(Builder $query, string $resource): Builder
    {
        return $query->where('resource', $resource);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get the AuditAction enum instance.
     */
    public function getActionEnumAttribute(): ?AuditAction
    {
        return AuditAction::tryFrom($this->action);
    }

    /**
     * Get the AuditCategory enum instance.
     */
    public function getCategoryEnumAttribute(): ?AuditCategory
    {
        return AuditCategory::tryFrom($this->category);
    }

    /**
     * Get a formatted location string.
     */
    public function getLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->country]);
        return implode(', ', $parts) ?: 'Unknown';
    }

    /**
     * Get a formatted device string.
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([$this->browser, $this->operating_system]);
        return implode(' / ', $parts) ?: 'Unknown';
    }
}
