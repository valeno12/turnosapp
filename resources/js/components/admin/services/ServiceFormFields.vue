<template>
    <!-- Nombre -->
    <FormField
        id="name"
        label="Nombre"
        :error="form.errors.name"
        required
        hint="Ej: Pilates, Yoga, Funcional"
    >
        <Input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Nombre del servicio"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.name }"
        />
    </FormField>

    <!-- Duración y Color en 2 columnas -->
    <div class="grid gap-4 sm:grid-cols-2">
        <FormField
            id="duration_minutes"
            label="Duración"
            :error="form.errors.duration_minutes"
            required
            hint="Minutos por clase"
        >
            <Input
                id="duration_minutes"
                v-model.number="form.duration_minutes"
                type="number"
                min="15"
                max="300"
                placeholder="60"
                :disabled="form.processing"
                :class="{ 'border-destructive': form.errors.duration_minutes }"
            />
        </FormField>

        <FormField
            id="color"
            label="Color"
            :error="form.errors.color"
            required
            hint="Para la agenda visual"
        >
            <div class="flex gap-2">
                <Input
                    id="color"
                    v-model="form.color"
                    type="color"
                    :disabled="form.processing"
                    class="h-10 w-20 cursor-pointer"
                    :class="{ 'border-destructive': form.errors.color }"
                />
                <Input
                    v-model="form.color"
                    type="text"
                    placeholder="#3b82f6"
                    :disabled="form.processing"
                    class="flex-1"
                    :class="{ 'border-destructive': form.errors.color }"
                />
            </div>
        </FormField>
    </div>

    <!-- Descripción -->
    <FormField
        id="description"
        label="Descripción"
        :error="form.errors.description"
        hint="Información adicional sobre el servicio (opcional)"
    >
        <Textarea
            id="description"
            v-model="form.description"
            placeholder="Ej: Clase de Pilates con reformer para todos los niveles"
            rows="3"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.description }"
        />
    </FormField>
</template>

<script setup lang="ts">
import FormField from '@/components/shared/FormField.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { inject } from 'vue';

const form = inject<any>('serviceForm');
</script>
