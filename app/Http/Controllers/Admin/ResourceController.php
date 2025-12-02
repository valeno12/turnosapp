<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Traits\LogsCriticalActions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResourceController extends Controller
{
    use LogsCriticalActions;

    public function index(Request $request)
    {
        $query = Resource::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $resources = $query->paginate(10);

        return Inertia::render('Admin/Resources/Index', [
            'data' => $resources,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Resources/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'is_self_assignable' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $resource = Resource::create($validated);

        $this->logCriticalAction('RESOURCE_CREATED', $resource, [
            'capacity' => $resource->capacity,
            'is_self_assignable' => $resource->is_self_assignable,
        ]);

        return redirect('/admin/resources')
            ->with('success', 'Recurso creado exitosamente.');
    }

    public function edit(Resource $resource)
    {
        return Inertia::render('Admin/Resources/Edit', [
            'resource' => $resource,
        ]);
    }

    public function update(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'is_self_assignable' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $oldData = $resource->only(['name', 'capacity', 'is_self_assignable', 'notes']);
        $resource->update($validated);

        $this->logCriticalAction('RESOURCE_UPDATED', $resource, [
            'changes' => [
                'before' => $oldData,
                'after' => $validated,
            ],
        ]);

        return redirect('/admin/resources')
            ->with('success', 'Recurso actualizado exitosamente.');
    }

    public function destroy(Resource $resource)
    {
        // TODO Sprint 3: Validar clases futuras
        
        $this->logCriticalAction('RESOURCE_DELETED', $resource, [
            'deleted_data' => $resource->only(['name', 'capacity', 'is_self_assignable', 'notes']),
        ]);

        $resource->delete();

        return back()->with('success', 'Recurso eliminado exitosamente.');
    }
}