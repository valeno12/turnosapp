<?php

use App\Models\Tenant;

if (!function_exists('tenant')) {
    /**
     * Obtiene el tenant actual
     */
    function tenant(): ?Tenant
    {
        return app('tenant');
    }
}