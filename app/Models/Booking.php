<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'schedule_id',
        'resource_id',
        'date',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'string',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
        
        static::creating(function ($booking) {
            if (!$booking->tenant_id && app()->has('tenant')) {
                $booking->tenant_id = tenant()->id;
            }
        });
    }

    // Relaciones
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }
}