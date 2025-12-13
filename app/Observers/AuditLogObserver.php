<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogObserver
{
    protected function log($event, $model, $old = null, $new = null)
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'event'          => $event,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    public function created($model)
    {
        $this->log(
            'created',
            $model,
            null,
            $model->getAttributes()
        );
    }

    public function updated($model)
    {
        $this->log(
            'updated',
            $model,
            $model->getOriginal(),
            $model->getDirty()
        );
    }

    public function deleted($model)
    {
        $this->log(
            'deleted',
            $model,
            $model->getAttributes(),
            null
        );
    }

    // Jika kamu pakai soft delete
    public function restored($model)
    {
        $this->log(
            'restored',
            $model,
            null,
            $model->getAttributes()
        );
    }
}
