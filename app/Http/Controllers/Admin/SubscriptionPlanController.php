<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\SubscriptionPlan;
use App\Traits\LogsCriticalActions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionPlanController extends Controller
{
    use LogsCriticalActions;

    public function index(Request $request)
    {
        $query = SubscriptionPlan::with('services');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $plans = $query->paginate(10);

        return Inertia::render('Admin/SubscriptionPlans/Index', [
            'data' => $plans,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    public function create()
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/SubscriptionPlans/Create', [
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classes_per_week' => 'required|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'allows_makeups' => 'boolean',
            'description' => 'nullable|string',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
        ], [
            'service_ids.required' => 'Debes seleccionar al menos un servicio.',
            'service_ids.min' => 'Debes seleccionar al menos un servicio.',
            'service_ids.*.exists' => 'Uno de los servicios seleccionados no existe.',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => $validated['name'],
            'classes_per_week' => $validated['classes_per_week'],
            'base_price' => $validated['base_price'],
            'allows_makeups' => $validated['allows_makeups'] ?? true,
            'description' => $validated['description'] ?? null,
        ]);

        $plan->services()->sync($validated['service_ids']);

        $serviceNames = Service::whereIn('id', $validated['service_ids'])->pluck('name')->toArray();

        $this->logCriticalAction('PLAN_CREATED', $plan, [
            'classes_per_week' => $plan->classes_per_week,
            'base_price' => $plan->base_price,
            'allows_makeups' => $plan->allows_makeups,
            'services' => $serviceNames,
        ]);

        return redirect('/admin/subscription-plans')
            ->with('success', 'Plan creado exitosamente.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->load('services');
        
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/SubscriptionPlans/Edit', [
            'plan' => $subscriptionPlan,
            'services' => $services,
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classes_per_week' => 'required|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'allows_makeups' => 'boolean',
            'description' => 'nullable|string',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
        ], [
            'service_ids.required' => 'Debes seleccionar al menos un servicio.',
            'service_ids.min' => 'Debes seleccionar al menos un servicio.',
            'service_ids.*.exists' => 'Uno de los servicios seleccionados no existe.',
        ]);

        $oldData = [
            'name' => $subscriptionPlan->name,
            'classes_per_week' => $subscriptionPlan->classes_per_week,
            'base_price' => $subscriptionPlan->base_price,
            'allows_makeups' => $subscriptionPlan->allows_makeups,
            'services' => $subscriptionPlan->services->pluck('name')->toArray(),
        ];

        $subscriptionPlan->update([
            'name' => $validated['name'],
            'classes_per_week' => $validated['classes_per_week'],
            'base_price' => $validated['base_price'],
            'allows_makeups' => $validated['allows_makeups'] ?? true,
            'description' => $validated['description'] ?? null,
        ]);

        $subscriptionPlan->services()->sync($validated['service_ids']);

        $newServiceNames = Service::whereIn('id', $validated['service_ids'])->pluck('name')->toArray();

        $this->logCriticalAction('PLAN_UPDATED', $subscriptionPlan, [
            'changes' => [
                'before' => $oldData,
                'after' => [
                    'name' => $validated['name'],
                    'classes_per_week' => $validated['classes_per_week'],
                    'base_price' => $validated['base_price'],
                    'allows_makeups' => $validated['allows_makeups'] ?? true,
                    'services' => $newServiceNames,
                ],
            ],
        ]);

        return redirect('/admin/subscription-plans')
            ->with('success', 'Plan actualizado exitosamente.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // TODO Sprint 4: Validar usuarios activos

        $this->logCriticalAction('PLAN_DELETED', $subscriptionPlan, [
            'deleted_data' => [
                'name' => $subscriptionPlan->name,
                'classes_per_week' => $subscriptionPlan->classes_per_week,
                'base_price' => $subscriptionPlan->base_price,
                'services' => $subscriptionPlan->services->pluck('name')->toArray(),
            ],
        ]);

        $subscriptionPlan->delete();

        return back()->with('success', 'Plan eliminado exitosamente.');
    }
}