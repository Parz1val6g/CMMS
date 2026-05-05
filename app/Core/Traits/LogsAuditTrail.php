<?php

namespace App\Core\Traits;

use App\Core\Models\AuditLog;

/**
 * Automatically writes to audit_logs on model created / updated / deleted.
 * Hidden attributes ($hidden) are stripped from both old and new value snapshots.
 */
trait LogsAuditTrail
{
    protected static function bootLogsAuditTrail(): void
    {
        static::created(fn ($model) => static::recordAudit($model, 'created', [], $model->getAttributes()));
        static::updated(fn ($model) => static::recordAudit($model, 'updated', $model->getOriginal(), $model->getChanges()));
        static::deleted(fn ($model) => static::recordAudit($model, 'deleted', $model->getAttributes(), []));
    }

    private static function recordAudit($model, string $event, array $old, array $new): void
    {
        $hidden = array_flip($model->getHidden());
        $old    = array_diff_key($old, $hidden);
        $new    = array_diff_key($new, $hidden);

        AuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'event'          => $event,
            'old_values'     => $old ?: null,
            'new_values'     => $new ?: null,
            'user_id'        => auth()->id(),
            'ip_address'     => request()->ip(),
        ]);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
