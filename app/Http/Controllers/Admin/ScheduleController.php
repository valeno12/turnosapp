<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\Resource;
use App\Models\User;
use App\Traits\LogsCriticalActions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    use LogsCriticalActions;

    public function index(Request $request)
    {
        $schedules = Schedule::query()
            ->withRelations()
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Admin/Schedules/Index', [
            'data' => [
                'data' => $schedules,
            ],
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'resources' => Resource::where('is_active', true)->orderBy('name')->get(),
            'instructors' => User::where('role', 'instructor')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Schedules/Create', [
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'resources' => Resource::where('is_active', true)->orderBy('name')->get(),
            'instructors' => User::where('role', 'instructor')->orderBy('name')->get(),
            'initialDay' => $request->input('day'),
            'initialTime' => $request->input('time'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'resource_id' => 'required|exists:resources,id',
            'user_id' => 'nullable|exists:users,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
        ], [
            'service_id.required' => 'Debes seleccionar un servicio',
            'resource_id.required' => 'Debes seleccionar un recurso',
            'day_of_week.required' => 'Debes seleccionar un día',
            'start_time.required' => 'Debes ingresar una hora',
            'capacity.required' => 'Debes ingresar la capacidad',
        ]);

        // Obtener recurso y servicio
        $resource = Resource::find($validated['resource_id']);
        $service = Service::find($validated['service_id']);

        // Validar capacidad <= capacidad del recurso
        if ($validated['capacity'] > $resource->capacity) {
            return back()->withErrors([
                'capacity' => "La capacidad no puede superar {$resource->capacity} (capacidad del recurso {$resource->name})"
            ]);
        }

        // Validar conflicto de recurso (considerando duración)
        $newStart = strtotime($validated['start_time']);
        $newEnd = $newStart + ($service->duration_minutes * 60);

        $resourceConflict = Schedule::with('service')
            ->where('resource_id', $validated['resource_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->get()
            ->first(function ($existingSchedule) use ($newStart, $newEnd) {
                $existingStart = strtotime($existingSchedule->start_time);
                $existingEnd = $existingStart + (($existingSchedule->service->duration_minutes ?? 60) * 60);
                
                // Verificar solapamiento: nuevo empieza antes que existente termine Y nuevo termina después que existente empiece
                return ($newStart < $existingEnd && $newEnd > $existingStart);
            });

        if ($resourceConflict) {
            return back()->withErrors([
                'resource_id' => "Este recurso ya está ocupado en ese horario (se solapa con {$resourceConflict->service->name} a las {$resourceConflict->start_time})"
            ]);
        }

        // Validar conflicto de instructor (solo si tiene instructor)
        if (!empty($validated['user_id'])) {
            $instructorConflict = Schedule::with('service')
                ->where('user_id', $validated['user_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->get()
                ->first(function ($existingSchedule) use ($newStart, $newEnd) {
                    $existingStart = strtotime($existingSchedule->start_time);
                    $existingEnd = $existingStart + (($existingSchedule->service->duration_minutes ?? 60) * 60);
                    
                    return ($newStart < $existingEnd && $newEnd > $existingStart);
                });

            if ($instructorConflict) {
                return back()->withErrors([
                    'user_id' => "Este instructor ya tiene una clase asignada en ese horario (se solapa con {$instructorConflict->service->name} a las {$instructorConflict->start_time})"
                ]);
            }
        }

        $schedule = Schedule::create($validated);

        $this->logCriticalAction('SCHEDULE_CREATED', $schedule, [
            'service' => $schedule->service->name,
            'resource' => $schedule->resource->name,
            'instructor' => $schedule->instructor?->name,
            'day' => $schedule->getDayName(),
            'time' => $schedule->getFormattedTime(),
        ]);

        return redirect('/admin/schedules')
            ->with('success', 'Horario creado exitosamente.');
    }

    public function edit(Schedule $schedule)
    {
        $schedule->load(['service', 'resource', 'instructor']);

        return Inertia::render('Admin/Schedules/Edit', [
            'schedule' => $schedule,
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'resources' => Resource::where('is_active', true)->orderBy('name')->get(),
            'instructors' => User::where('role', 'instructor')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'resource_id' => 'required|exists:resources,id',
            'user_id' => 'nullable|exists:users,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
        ], [
            'service_id.required' => 'Debes seleccionar un servicio',
            'resource_id.required' => 'Debes seleccionar un recurso',
            'day_of_week.required' => 'Debes seleccionar un día',
            'start_time.required' => 'Debes ingresar una hora',
            'capacity.required' => 'Debes ingresar la capacidad',
        ]);

        // Obtener recurso y servicio
        $resource = Resource::find($validated['resource_id']);
        $service = Service::find($validated['service_id']);

        // Validar capacidad <= capacidad del recurso
        if ($validated['capacity'] > $resource->capacity) {
            return back()->withErrors([
                'capacity' => "La capacidad no puede superar {$resource->capacity} (capacidad del recurso {$resource->name})"
            ]);
        }

        // Validar conflicto de recurso (excluyendo el schedule actual)
        $newStart = strtotime($validated['start_time']);
        $newEnd = $newStart + ($service->duration_minutes * 60);

        $resourceConflict = Schedule::with('service')
            ->where('resource_id', $validated['resource_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('id', '!=', $schedule->id)
            ->get()
            ->first(function ($existingSchedule) use ($newStart, $newEnd) {
                $existingStart = strtotime($existingSchedule->start_time);
                $existingEnd = $existingStart + (($existingSchedule->service->duration_minutes ?? 60) * 60);
                
                return ($newStart < $existingEnd && $newEnd > $existingStart);
            });

        if ($resourceConflict) {
            return back()->withErrors([
                'resource_id' => "Este recurso ya está ocupado en ese horario (se solapa con {$resourceConflict->service->name} a las {$resourceConflict->start_time})"
            ]);
        }

        // Validar conflicto de instructor (solo si tiene instructor)
        if (!empty($validated['user_id'])) {
            $instructorConflict = Schedule::with('service')
                ->where('user_id', $validated['user_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('id', '!=', $schedule->id)
                ->get()
                ->first(function ($existingSchedule) use ($newStart, $newEnd) {
                    $existingStart = strtotime($existingSchedule->start_time);
                    $existingEnd = $existingStart + (($existingSchedule->service->duration_minutes ?? 60) * 60);
                    
                    return ($newStart < $existingEnd && $newEnd > $existingStart);
                });

            if ($instructorConflict) {
                return back()->withErrors([
                    'user_id' => "Este instructor ya tiene una clase asignada en ese horario (se solapa con {$instructorConflict->service->name} a las {$instructorConflict->start_time})"
                ]);
            }
        }

        $oldData = $schedule->toArray();
        $schedule->update($validated);

        $this->logCriticalAction('SCHEDULE_UPDATED', $schedule, [
            'changes' => [
                'before' => $oldData,
                'after' => $schedule->fresh()->toArray(),
            ],
        ]);

        return redirect('/admin/schedules')
            ->with('success', 'Horario actualizado exitosamente.');
    }

    public function destroy(Schedule $schedule)
    {
        $this->logCriticalAction('SCHEDULE_DELETED', $schedule, [
            'service' => $schedule->service->name,
            'resource' => $schedule->resource->name,
            'day' => $schedule->getDayName(),
            'time' => $schedule->getFormattedTime(),
        ]);

        $schedule->delete();

        return redirect('/admin/schedules')
            ->with('success', 'Horario eliminado exitosamente.');
    }
}