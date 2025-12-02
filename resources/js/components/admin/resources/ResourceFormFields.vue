<template>
    <!-- Nombre -->
    <FormField
        id="name"
        label="Nombre"
        :error="form.errors.name"
        required
        hint="Ej: Sala 1, 4 Camillas Reformer"
    >
        <Input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Nombre del recurso"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.name }"
        />
    </FormField>

    <!-- Capacidad -->
    <FormField
        id="capacity"
        label="Capacidad"
        :error="form.errors.capacity"
        required
        hint="Cantidad máxima de personas"
    >
        <Input
            id="capacity"
            v-model.number="form.capacity"
            type="number"
            min="1"
            placeholder="8"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.capacity }"
        />
    </FormField>

    <!-- Autoasignable -->
    <FormField
        id="is_self_assignable"
        label="Tipo de Asignación"
        :error="form.errors.is_self_assignable"
    >
        <div class="flex items-center space-x-2">
            <Checkbox
                id="is_self_assignable"
                :checked="form.is_self_assignable"
                @update:checked="form.is_self_assignable = $event"
                :disabled="form.processing"
            />
            <Label for="is_self_assignable" class="cursor-pointer font-normal">
                Los alumnos pueden elegir este recurso desde su app
            </Label>
        </div>
    </FormField>

    <!-- Notas -->
    <FormField
        id="notes"
        label="Notas"
        :error="form.errors.notes"
        hint="Información adicional para el equipo (opcional)"
    >
        <Textarea
            id="notes"
            v-model="form.notes"
            placeholder="Ej: Solo para personas de contextura pequeña"
            rows="3"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.notes }"
        />
    </FormField>
</template>

<script setup lang="ts">
import FormField from '@/components/shared/FormField.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { inject } from 'vue';

const form = inject<any>('resourceForm');
</script>
