<?php

namespace App\Traits;

use App\Enums\Audit\AuditAction;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;

trait AuditablePrice
{
    /**
     * Boot the trait to hook into model events.
     */
    public static function bootAuditablePrice(): void
    {
        static::created(function ($model) {
            static::logPriceActivity(AuditAction::CREATE_PRICE, $model, null, $model->toArray());
        });

        static::updated(function ($model) {
            // Only log if something actually changed
            if ($model->wasChanged()) {
                static::logPriceActivity(
                    AuditAction::UPDATE_PRICE,
                    $model,
                    $model->getOriginal(),
                    $model->getChanges()
                );
            }
        });

        static::deleted(function ($model) {
            static::logPriceActivity(AuditAction::DELETE_PRICE, $model, $model->toArray(), null);
        });
    }

    /**
     * Dispatch the log event to the AuditLogService.
     */
    protected static function logPriceActivity(AuditAction $action, $model, ?array $previous = null, ?array $new = null): void
    {
        try {
            app(AuditLogService::class)->logResourceChange(
                $action,
                class_basename($model), // e.g., 'RepairPrice', 'Variant'
                $model->getKey(),
                $previous,
                $new
            );
        } catch (\Throwable $e) {
            Log::error('Failed to log auditable price activity', [
                'model' => class_basename($model),
                'error' => $e->getMessage()
            ]);
        }
    }
}
