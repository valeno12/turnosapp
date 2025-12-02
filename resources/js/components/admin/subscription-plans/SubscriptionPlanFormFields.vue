<template>
    <!-- Nombre -->
    <FormField
        id="name"
        label="Nombre"
        :error="form.errors.name"
        required
        hint="Ej: Plan 2 veces por semana, Full Pass"
    >
        <Input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Nombre del plan"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.name }"
        />
    </FormField>

    <!-- Clases por Semana y Precio en 2 columnas -->
    <div class="grid gap-4 sm:grid-cols-2">
        <FormField
            id="classes_per_week"
            label="Clases por Semana"
            :error="form.errors.classes_per_week"
            required
            hint="0 = ilimitado"
        >
            <Input
                id="classes_per_week"
                v-model.number="form.classes_per_week"
                type="number"
                min="0"
                placeholder="2"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.classes_per_week }"
            />
        </FormField>

        <FormField
            id="base_price"
            label="Precio Base Mensual"
            :error="form.errors.base_price"
            required
            hint="En pesos argentinos"
        >
            <Input
                id="base_price"
                v-model.number="form.base_price"
                type="number"
                min="0"
                step="0.01"
                placeholder="12000"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.base_price }"
            />
        </FormField>
    </div>

    <!-- Permite Recuperos -->
    <FormField
        id="allows_makeups"
        label="Recuperos"
        :error="form.errors.allows_makeups"
    >
        <div class="flex items-center space-x-2">
            <Checkbox
                id="allows_makeups"
                :checked="form.allows_makeups"
                @update:checked="form.allows_makeups = $event"
                :disabled="form.processing"
            />
            <Label for="allows_makeups" class="cursor-pointer font-normal">
                Permitir recuperar clases canceladas
            </Label>
        </div>
    </FormField>

    <!-- Servicios Incluidos -->
    <FormField
        id="service_ids"
        label="Servicios Incluidos"
        :error="form.errors.service_ids"
        required
        hint="Selecciona los tipos de clases que incluye este plan"
    >
        <div class="space-y-2 rounded-md border p-4">
            <div
                v-if="availableServices.length === 0"
                class="text-sm text-muted-foreground"
            >
                No hay servicios disponibles.
                <a href="/admin/services/create" class="underline"
                    >Crear servicio</a
                >
            </div>

            <div
                v-for="service in availableServices"
                :key="service.id"
                class="flex items-center space-x-2 rounded-md p-2 hover:bg-muted/50"
            >
                <input
                    type="checkbox"
                    :id="`service-${service.id}`"
                    :checked="form?.service_ids?.includes(service.id)"
                    @change="
                        toggleService(
                            service.id,
                            ($event.target as HTMLInputElement).checked,
                        )
                    "
                    :disabled="form?.processing"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
                <Label
                    :for="`service-${service.id}`"
                    class="flex flex-1 cursor-pointer items-center gap-2"
                >
                    <div
                        class="h-4 w-4 rounded-full border"
                        :style="{ backgroundColor: service.color }"
                    ></div>
                    <span>{{ service.name }}</span>
                    <span class="text-sm text-muted-foreground"
                        >({{ service.duration_minutes }} min)</span
                    >
                </Label>
            </div>
        </div>
    </FormField>

    <!-- Descripción -->
    <FormField
        id="description"
        label="Descripción"
        :error="form.errors.description"
        hint="Información adicional sobre el plan (opcional)"
    >
        <Textarea
            id="description"
            v-model="form.description"
            placeholder="Ej: Ideal para quienes buscan una rutina regular de entrenamiento"
            rows="3"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.description }"
        />
    </FormField>
</template>

<script setup lang="ts">
import FormField from '@/components/shared/FormField.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Service } from '@/types/model';
import { inject } from 'vue';

interface Props {
    availableServices: Service[];
}

defineProps<Props>();

const form = inject<any>('subscriptionPlanForm');

const toggleService = (serviceId: number, checked: boolean | string) => {
    // Shadcn a veces envía string "true"/"false"
    const isChecked = checked === true || checked === 'true';

    if (isChecked) {
        if (!form.service_ids.includes(serviceId)) {
            form.service_ids.push(serviceId);
        }
    } else {
        const index = form.service_ids.indexOf(serviceId);
        if (index > -1) {
            form.service_ids.splice(index, 1);
        }
    }
};
</script>
