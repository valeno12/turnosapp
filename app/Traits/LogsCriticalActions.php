<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait LogsCriticalActions
{
    /**
     * Mapeo de modelo a canal de log
     */
    protected function getLogChannel(string $modelClass): string
    {
        $channelMap = [
            'Resource' => 'critical_resources',
            'Service' => 'critical_services',
            'SubscriptionPlan' => 'critical_plans',
            'User' => 'critical_users',
            'ScheduleBlock' => 'critical_bookings',
            'Booking' => 'critical_bookings',
        ];

        $modelName = class_basename($modelClass);
        
        return $channelMap[$modelName] ?? 'daily';
    }

    /**
     * Loguear acción crítica
     */
    protected function logCriticalAction(string $eventType, $model, array $additionalData = []): void
    {
        $user = Auth::user();
        $tenant = app('tenant');

        $logData = [
            'event_type' => $eventType,
            'model_type' => class_basename($model),
            'model_id' => $model->id,
            'model_name' => $model->name ?? $model->id,
            'tenant_subdomain' => $tenant->subdomain,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 200),
            'timestamp' => now()->toIso8601String(),
        ];

        // Merge con datos adicionales
        $logData = array_merge($logData, $additionalData);

        // Determinar canal dinámicamente
        $channel = $this->getLogChannel(get_class($model));

        Log::channel($channel)->info($eventType, $logData);
    }
}