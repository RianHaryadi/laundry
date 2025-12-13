<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        // Log when model is created
        static::created(function ($model) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'created',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'old_values' => null,
                'new_values' => $model->toArray(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        // Log when model is updated
        static::updated(function ($model) {
            $changes = $model->getChanges();
            
            // Remove updated_at from changes
            unset($changes['updated_at']);
            
            if (!empty($changes)) {
                $original = [];
                foreach (array_keys($changes) as $key) {
                    $original[$key] = $model->getOriginal($key);
                }

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'event' => 'updated',
                    'auditable_type' => get_class($model),
                    'auditable_id' => $model->id,
                    'old_values' => $original,
                    'new_values' => $changes,
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                ]);
            }
        });

        // Log when model is deleted
        static::deleted(function ($model) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'old_values' => $model->toArray(),
                'new_values' => null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });
    }
}