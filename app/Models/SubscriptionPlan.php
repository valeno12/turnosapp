<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'classes_per_week',
        'base_price',
        'allows_makeups',
        'description',
        'is_active',
    ];

    protected $casts = [
        'classes_per_week' => 'integer',
        'base_price' => 'decimal:2',
        'allows_makeups' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
        
        static::creating(function ($plan) {
            if (!$plan->tenant_id && app()->has('tenant')) {
                $plan->tenant_id = tenant()->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'plan_service');
    }
}
