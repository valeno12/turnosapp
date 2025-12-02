<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\LogsCriticalActions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    use LogsCriticalActions;

    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $services = $query->paginate(10);

        return Inertia::render('Admin/Services/Index', [
            'data' => $services,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Services/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
        ]);

        $service = Service::create($validated);

        $this->logCriticalAction('SERVICE_CREATED', $service, [
            'duration_minutes' => $service->duration_minutes,
            'color' => $service->color,
        ]);

        return redirect('/admin/services')
            ->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Service $service)
    {
        return Inertia::render('Admin/Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
        ]);

        $oldData = $service->only(['name', 'duration_minutes', 'color', 'description']);
        $service->update($validated);

        $this->logCriticalAction('SERVICE_UPDATED', $service, [
            'changes' => [
                'before' => $oldData,
                'after' => $validated,
            ],
        ]);

        return redirect('/admin/services')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(Service $service)
    {
        // Validar si está vinculado a planes activos
        $activePlans = $service->subscriptionPlans()
            ->where('is_active', true)
            ->get(['subscription_plans.id', 'subscription_plans.name']);

        if ($activePlans->count() > 0) {
            $planNames = $activePlans->pluck('name')->join(', ');
            return back()->with('error', "No se puede eliminar el servicio porque está vinculado a los siguientes planes activos: {$planNames}");
        }

        // TODO Sprint 3: Validar clases futuras

        $this->logCriticalAction('SERVICE_DELETED', $service, [
            'deleted_data' => $service->only(['name', 'duration_minutes', 'color', 'description']),
            'was_in_active_plans' => false,
        ]);

        $service->delete();

        return back()->with('success', 'Servicio eliminado exitosamente.');
    }
}