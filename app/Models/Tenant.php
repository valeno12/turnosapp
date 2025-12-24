<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'logo_url',
        'primary_color',
        'cancellation_hours',
        'schedule_change_policy',
        'schedule_change_cutoff_days',
        'pricing_rules',
        'is_active',
        'schedule_start_time',
        'schedule_end_time',
        'working_days',
    ];

    protected $casts = [
        'pricing_rules' => 'array',
        'is_active' => 'boolean',
        'cancellation_hours' => 'integer',
        'schedule_change_cutoff_days' => 'integer',
        'schedule_start_time' => 'datetime:H:i',
        'schedule_end_time' => 'datetime:H:i',
        'working_days' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
