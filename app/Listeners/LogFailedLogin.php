<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Request;

class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        AuditLog::create([
            'user_id' => null,
            'event' => 'failed_login',
            'auditable_type' => null,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => [
                'email' => $event->credentials['email'] ?? 'Unknown',
                'attempted_at' => now()->toDateTimeString(),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}