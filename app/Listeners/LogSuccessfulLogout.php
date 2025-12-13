<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            AuditLog::create([
                'user_id' => $event->user->id,
                'event' => 'logout',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        }
    }
}