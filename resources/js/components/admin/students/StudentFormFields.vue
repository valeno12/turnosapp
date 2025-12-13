<script setup lang="ts">
import FormField from '@/components/shared/FormField.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { es } from 'date-fns/locale';
import { inject } from 'vue';
interface Props {
    isEditing?: boolean;
}

defineProps<Props>();

const form = inject<any>('studentForm');
</script>

<template>
    <!-- Nombre y Fecha de Nacimiento en 2 columnas -->
    <div class="grid gap-4 sm:grid-cols-2">
        <FormField
            id="name"
            label="Nombre Completo"
            :error="form.errors.name"
            required
        >
            <Input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="Juan Pérez"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.name }"
            />
        </FormField>

        <FormField
            id="birth_date"
            label="Fecha de Nacimiento"
            :error="form.errors.birth_date"
            hint="Opcional"
        >
            <VueDatePicker
                v-model="form.birth_date"
                :enable-time-picker="false"
                :formats="{ input: 'dd/MM/yyyy' }"
                placeholder="Seleccionar fecha"
                :disabled="form.processing"
                auto-apply
                :year-range="[1920, new Date().getFullYear()]"
                :locale="es"
            />
        </FormField>
    </div>

    <!-- Email y Teléfono en 2 columnas -->
    <div class="grid gap-4 sm:grid-cols-2">
        <FormField
            id="email"
            label="Email"
            :error="form.errors.email"
            hint="Opcional"
        >
            <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="juan@example.com"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.email }"
            />
        </FormField>

        <FormField
            id="phone"
            label="Teléfono"
            :error="form.errors.phone"
            hint="Opcional"
        >
            <Input
                id="phone"
                v-model="form.phone"
                type="tel"
                placeholder="+54 9 11 1234-5678"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.phone }"
            />
        </FormField>
    </div>

    <Separator />

    <!-- Notas de Salud -->
    <FormField
        id="health_notes"
        label="Notas de Salud"
        :error="form.errors.health_notes"
        hint="Lesiones, condiciones médicas, restricciones (opcional)"
    >
        <Textarea
            id="health_notes"
            v-model="form.health_notes"
            placeholder="Ej: Problemas de rodilla, no puede hacer flexiones"
            rows="3"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.health_notes }"
        />
    </FormField>

    <Separator />

    <!-- Contacto de Emergencia -->
    <div class="grid gap-4 sm:grid-cols-2">
        <FormField
            id="emergency_contact"
            label="Contacto de Emergencia"
            :error="form.errors.emergency_contact"
            hint="Nombre"
        >
            <Input
                id="emergency_contact"
                v-model="form.emergency_contact"
                type="text"
                placeholder="María Pérez"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.emergency_contact }"
            />
        </FormField>

        <FormField
            id="emergency_phone"
            label="Teléfono de Emergencia"
            :error="form.errors.emergency_phone"
            hint="Teléfono del contacto"
        >
            <Input
                id="emergency_phone"
                v-model="form.emergency_phone"
                type="tel"
                placeholder="+54 9 11 8765-4321"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.emergency_phone }"
            />
        </FormField>
    </div>

    <Separator />

    <!-- Estado Activo -->
    <FormField id="is_active" label="Estado" :error="form.errors.is_active">
        <div class="flex items-center space-x-2">
            <input
                type="checkbox"
                id="is_active"
                v-model="form.is_active"
                :disabled="form.processing"
                class="h-4 w-4 shrink-0 rounded-sm border border-primary shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            />
            <Label for="is_active" class="cursor-pointer font-normal">
                Alumno activo
            </Label>
        </div>
    </FormField>
</template>
