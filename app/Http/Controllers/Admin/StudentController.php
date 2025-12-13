<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Traits\LogsCriticalActions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    use LogsCriticalActions;

    public function index(Request $request)
    {
        $query = Student::query();

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('email', 'ilike', '%' . $request->search . '%')
                  ->orWhere('phone', 'ilike', '%' . $request->search . '%');
            });
        }

        // Filtro activo/inactivo
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // Ordenamiento
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $students = $query->paginate(10);

        return Inertia::render('Admin/Students/Index', [
            'data' => $students,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', 'all'),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Students/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:students,email,NULL,id,tenant_id,' . $request->user()->tenant_id,
            'phone' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'health_notes' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $student = Student::create($validated);

        $this->logCriticalAction('STUDENT_CREATED', $student, [
            'name' => $student->name,
            'email' => $student->email,
            'phone' => $student->phone,
        ]);

        return redirect('/admin/students')
            ->with('success', 'Alumno creado exitosamente.');
    }

    public function edit(Student $student)
    {
        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:students,email,' . $student->id . ',id,tenant_id,' . $request->user()->tenant_id,
            'phone' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'health_notes' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $oldData = $student->toArray();

        $student->update($validated);

        $this->logCriticalAction('STUDENT_UPDATED', $student, [
            'changes' => [
                'before' => $oldData,
                'after' => $student->fresh()->toArray(),
            ],
        ]);

        return redirect('/admin/students')
            ->with('success', 'Alumno actualizado exitosamente.');
    }

    public function destroy(Student $student)
    {
        // TODO Sprint siguiente: Validar suscripciones activas, clases futuras, etc.

        $this->logCriticalAction('STUDENT_DELETED', $student, [
            'deleted_student' => [
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
            ],
        ]);

        $student->delete();

        return back()->with('success', 'Alumno eliminado exitosamente.');
    }
}