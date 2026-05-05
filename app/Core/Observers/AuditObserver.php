<?php

namespace App\Core\Observers;

use App\Shared\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

/**
 * Generic observer that logs CRUD events to audit_logs.
 *
 * Register in AppServiceProvider via:
 *   ServiceOrder::observe(AuditObserver::class);
 */
class AuditObserver
{
    /**
     * Events that should always capture full model attributes.
     */
    private const FULL_SNAPSHOT_EVENTS = ['created', 'deleted'];

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->log($model, 'created', null, $model->toArray());
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changed = $model->getDirty();
        if (empty($changed)) return;

        $old = array_intersect_key($model->getOriginal(), $changed);
        $new = $changed;

        $this->log($model, 'updated', $old, $new);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', $model->toArray(), null);
    }

    /**
     * Persist the audit log entry.
     */
    private function log(Model $model, string $event, ?array $old, ?array $new): void
    {
        // Skip logging for AuditLog itself to avoid infinite recursion
        if ($model instanceof AuditLog) return;

        AuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'user_id'        => auth()->id(),
            'event'          => $event,
            'old_values'     => $old ?? [],
            'new_values'     => $new ?? [],
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
        ]);
    }
}
