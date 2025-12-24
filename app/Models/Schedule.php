<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'service_id',
        'resource_id',
        'user_id',
        'day_of_week',
        'start_time',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
        
        static::creating(function ($schedule) {
            if (!$schedule->tenant_id && app()->has('tenant')) {
                $schedule->tenant_id = tenant()->id;
            }
        });
    }

    // Relaciones
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['service', 'resource', 'instructor']);
    }

    // Helpers
    public function getDayName(): string
    {
        $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return $days[$this->day_of_week] ?? '';
    }

    public function getFormattedTime(): string
    {
        return $this->start_time->format('H:i');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ScheduleException::class);
    }

    // Helper: Verificar si está cancelado en una fecha específica
    public function isCancelledOn($date): bool
    {
        return $this->exceptions()
            ->whereDate('date', $date)
            ->exists();
    }

    // Helper: Obtener bookings confirmados para una fecha
    public function getBookingsForDate($date)
    {
        return $this->bookings()
            ->with(['student', 'resource'])
            ->whereDate('date', $date)
            ->confirmed()
            ->get();
    }
}