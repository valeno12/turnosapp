<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\LogsCriticalActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    use LogsCriticalActions;

    public function edit()
    {
        $tenant = tenant();

        return Inertia::render('Admin/Settings/Edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request)
    {
        $tenant = tenant();
        
        $validated = $request->validate([
            'primary_color' => 'required|string|max:7',
            'cancellation_hours' => 'required|integer|min:0|max:168',
            'schedule_change_policy' => 'required|in:end_of_month,anytime',
            'schedule_change_cutoff_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:30',
                Rule::requiredIf($request->schedule_change_policy === 'end_of_month'),
            ],
            'pricing_rules' => 'required|array|min:1|max:5',
            'pricing_rules.*.day_start' => 'required|integer|min:1|max:31',
            'pricing_rules.*.day_end' => 'required|integer|min:1|max:31|gte:pricing_rules.*.day_start',
            'pricing_rules.*.multiplier' => 'required|numeric|min:1|max:2',
            'logo' => 'nullable|image|max:2048',
        ], [
            'primary_color.required' => 'El color principal es obligatorio',
            'cancellation_hours.required' => 'Las horas de cancelación son obligatorias',
            'cancellation_hours.min' => 'Las horas deben ser al menos 0',
            'cancellation_hours.max' => 'Las horas no pueden superar 168',
            'schedule_change_policy.required' => 'La política de cambio es obligatoria',
            'schedule_change_cutoff_days.required' => 'Los días de anticipación son obligatorios para esta política',
            'pricing_rules.required' => 'Debe configurar al menos un período de recargo',
            'pricing_rules.*.day_start.required' => 'El día inicio es obligatorio',
            'pricing_rules.*.day_end.required' => 'El día fin es obligatorio',
            'pricing_rules.*.multiplier.required' => 'El multiplicador es obligatorio',
        ]);

        // Validación custom: días no se pueden solapar
        $rules = $validated['pricing_rules'];
        for ($i = 0; $i < count($rules); $i++) {
            if ($rules[$i]['day_start'] > $rules[$i]['day_end']) {
                throw ValidationException::withMessages([
                    "pricing_rules.$i.day_start" => 'El día inicio debe ser menor o igual al día fin.',
                ]);
            }
            
            for ($j = $i + 1; $j < count($rules); $j++) {
                $overlap = (
                    ($rules[$i]['day_start'] <= $rules[$j]['day_end'] && $rules[$i]['day_end'] >= $rules[$j]['day_start']) ||
                    ($rules[$j]['day_start'] <= $rules[$i]['day_end'] && $rules[$j]['day_end'] >= $rules[$i]['day_start'])
                );
                
                if ($overlap) {
                    throw ValidationException::withMessages([
                        "pricing_rules.$j" => 'Los períodos no pueden solaparse.',
                    ]);
                }
            }
        }

        $oldData = $tenant->toArray();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior del tenant
            if ($tenant->logo_url) {
                Storage::disk('public')->delete($tenant->logo_url);
            }
            
            // Guardar en carpeta del tenant
            $path = $request->file('logo')->store("tenants/{$tenant->id}/logos", 'public');
            $validated['logo_url'] = $path;
            unset($validated['logo']);
        }

        // Si cambió a 'anytime', poner cutoff_days en null
        if ($validated['schedule_change_policy'] === 'anytime') {
            $validated['schedule_change_cutoff_days'] = null;
        }

        $tenant->update($validated);

        $this->logCriticalAction('SETTINGS_UPDATED', $tenant, [
            'changes' => [
                'before' => $oldData,
                'after' => $tenant->fresh()->toArray(),
            ],
        ]);

        return back()->with('success', 'Configuración actualizada exitosamente.');
    }
}