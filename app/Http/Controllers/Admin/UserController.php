<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Traits\LogsCriticalActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    use LogsCriticalActions;

    public function index(Request $request)
    {
        $query = User::with('permissions:id,name,description,group');

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('email', 'ilike', '%' . $request->search . '%');
            });
        }

        // Filtro por rol
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Ordenamiento
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'data' => $users,
            'filters' => [
                'search' => $request->input('search', ''),
                'role' => $request->input('role', 'all'),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    public function create()
    {
        // Traer todos los permisos agrupados
        $allPermissions = Permission::orderBy('group')->orderBy('name')->get();
        $permissionsByGroup = $allPermissions->groupBy('group');

        return Inertia::render('Admin/Users/Create', [
            'permissionsByGroup' => $permissionsByGroup,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,id,tenant_id,' . $request->user()->tenant_id,
            'password' => ['required', Password::defaults()],
            'role' => 'required|in:admin,instructor',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Asignar permisos si no es admin
        if ($user->role !== 'admin' && !empty($validated['permission_ids'])) {
            $permissionNames = Permission::whereIn('id', $validated['permission_ids'])
                ->pluck('name')
                ->toArray();
            
            $user->syncPermissions($permissionNames, $request->user());
        }

        $this->logCriticalAction('USER_CREATED', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'permissions' => $user->permissions->pluck('name')->toArray(),
        ]);

        return redirect('/admin/users')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(Request $request, User $user)
    {
        // No se puede editar el propio usuario desde aquí
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No podés editar tu propio usuario desde aquí. Usá la sección de perfil.');
        }

        // Admin no puede editar a otro admin
        if ($user->role === 'admin') {
            return back()->with('error', 'No se pueden editar administradores desde esta sección.');
        }

        $user->load('permissions:id,name,description,group');
        
        // Traer todos los permisos agrupados
        $allPermissions = Permission::orderBy('group')->orderBy('name')->get();
        $permissionsByGroup = $allPermissions->groupBy('group');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'permissionsByGroup' => $permissionsByGroup,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Validaciones de seguridad
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No podés editar tu propio usuario desde aquí.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'No se pueden editar administradores.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id . ',id,tenant_id,' . $request->user()->tenant_id,
            'password' => ['nullable', Password::defaults()],
            'role' => 'required|in:admin,instructor',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'permissions' => $user->permissions->pluck('name')->toArray(),
        ];

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // Actualizar password solo si se envió
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Sincronizar permisos
        $permissionNames = Permission::whereIn('id', $validated['permission_ids'] ?? [])
            ->pluck('name')
            ->toArray();
        
        $user->syncPermissions($permissionNames, $request->user());

        $newData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'permissions' => $user->fresh()->permissions->pluck('name')->toArray(),
        ];

        $this->logCriticalAction('USER_UPDATED', $user, [
            'changes' => [
                'before' => $oldData,
                'after' => $newData,
            ],
        ]);

        return redirect('/admin/users')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(Request $request, User $user)
    {
        // No se puede eliminar uno mismo
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No podés eliminar tu propia cuenta.');
        }

        // No se puede eliminar admin
        if ($user->role === 'admin') {
            return back()->with('error', 'No se pueden eliminar administradores.');
        }

        // TODO Sprint 4: Validar suscripciones activas, clases futuras, etc.

        $this->logCriticalAction('USER_DELETED', $user, [
            'deleted_user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions->pluck('name')->toArray(),
            ],
        ]);

        $user->delete();

        return back()->with('success', 'Usuario eliminado exitosamente.');
    }
}