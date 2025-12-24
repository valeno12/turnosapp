<template>
    <form @submit.prevent="submit" class="space-y-6">
        <Card>
            <CardHeader>
                <CardTitle>Información del Horario</CardTitle>
                <CardDescription>
                    Define qué clase, dónde y cuándo se dictará
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Servicio -->
                <FormField
                    id="service_id"
                    label="Servicio / Tipo de Clase"
                    :error="form.errors.service_id"
                    required
                >
                    <select
                        v-model="form.service_id"
                        :disabled="form.processing"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Selecciona un servicio...</option>
                        <option
                            v-for="service in services"
                            :key="service.id"
                            :value="service.id"
                        >
                            {{ service.name }} ({{ service.duration_minutes }}
                            min)
                        </option>
                    </select>
                </FormField>

                <!-- Recurso -->
                <FormField
                    id="resource_id"
                    label="Recurso / Espacio"
                    :error="form.errors.resource_id"
                    required
                >
                    <select
                        v-model="form.resource_id"
                        :disabled="form.processing"
                        @change="handleResourceChange"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Selecciona un recurso...</option>
                        <option
                            v-for="resource in resources"
                            :key="resource.id"
                            :value="resource.id"
                        >
                            {{ resource.name }} (cap. máx:
                            {{ resource.capacity }})
                        </option>
                    </select>
                    <p
                        v-if="selectedResource"
                        class="mt-2 text-sm text-muted-foreground"
                    >
                        💡 Capacidad máxima del recurso:
                        {{ selectedResource.capacity }}
                    </p>
                </FormField>

                <!-- Instructor (opcional) -->
                <FormField
                    id="user_id"
                    label="Instructor"
                    hint="Opcional - Deja vacío si no quieres asignar instructor"
                    :error="form.errors.user_id"
                >
                    <select
                        v-model="form.user_id"
                        :disabled="form.processing"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Sin instructor asignado</option>
                        <option
                            v-for="instructor in instructors"
                            :key="instructor.id"
                            :value="instructor.id"
                        >
                            {{ instructor.name }}
                        </option>
                    </select>
                </FormField>

                <Separator />

                <!-- Día y Hora -->
                <div class="grid gap-6 md:grid-cols-2">
                    <FormField
                        id="day_of_week"
                        label="Día de la semana"
                        :error="form.errors.day_of_week"
                        required
                    >
                        <select
                            v-model="form.day_of_week"
                            :disabled="form.processing"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">Selecciona un día...</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="0">Domingo</option>
                        </select>
                    </FormField>

                    <FormField
                        id="start_time"
                        label="Hora de inicio"
                        :error="form.errors.start_time"
                        required
                    >
                        <Input
                            id="start_time"
                            v-model="form.start_time"
                            type="time"
                            :disabled="form.processing"
                        />
                    </FormField>
                </div>

                <Separator />

                <!-- Capacidad -->
                <FormField
                    id="capacity"
                    label="Capacidad del turno"
                    hint="Puede ser menor a la capacidad del recurso"
                    :error="form.errors.capacity"
                    required
                >
                    <div class="flex items-center gap-3">
                        <Input
                            id="capacity"
                            v-model.number="form.capacity"
                            type="number"
                            min="1"
                            :max="selectedResource?.capacity"
                            :disabled="form.processing || !form.resource_id"
                            class="w-32 text-center text-lg font-semibold"
                        />
                        <span class="text-sm font-medium text-muted-foreground">
                            personas
                        </span>
                    </div>
                </FormField>
            </CardContent>
        </Card>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-4">
            <Button
                type="button"
                variant="outline"
                size="lg"
                @click="cancel"
                :disabled="form.processing"
            >
                Cancelar
            </Button>
            <Button type="submit" size="lg" :disabled="form.processing">
                <Save class="mr-2 h-4 w-4" />
                {{ schedule ? 'Guardar Cambios' : 'Crear Horario' }}
            </Button>
        </div>
    </form>
</template>

<script setup lang="ts">
import FormField from '@/components/shared/FormField.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import admin from '@/routes/admin';
import { Resource, Schedule, Service, User } from '@/types/model';
import { router, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    schedule?: Schedule;
    services: Service[];
    resources: Resource[];
    instructors: User[];
    initialDay?: number;
    initialTime?: string;
}

const props = defineProps<Props>();

const form = useForm({
    service_id: props.schedule?.service_id || '',
    resource_id: props.schedule?.resource_id || '',
    user_id: props.schedule?.user_id || '',
    day_of_week:
        props.schedule?.day_of_week?.toString() ||
        props.initialDay?.toString() ||
        '',
    start_time: props.schedule?.start_time || props.initialTime || '',
    capacity: props.schedule?.capacity || 1,
});

// Recurso seleccionado (para mostrar capacidad máxima)
const selectedResource = computed(() => {
    if (!form.resource_id) return null;
    return props.resources.find((r) => r.id === Number(form.resource_id));
});

// Cuando cambia el recurso, ajustar capacidad si excede
const handleResourceChange = () => {
    if (
        selectedResource.value &&
        form.capacity > selectedResource.value.capacity
    ) {
        form.capacity = selectedResource.value.capacity;
    }
};

const submit = () => {
    const url = props.schedule
        ? `/admin/schedules/${props.schedule.id}`
        : '/admin/schedules';

    const method = props.schedule ? 'put' : 'post';

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(
                props.schedule ? '¡Horario actualizado!' : '¡Horario creado!',
                {
                    description: props.schedule
                        ? 'Los cambios se han guardado exitosamente.'
                        : 'El horario semanal se ha creado correctamente.',
                },
            );
        },
        onError: () => {
            toast.error('Error al guardar', {
                description: 'Por favor, revisa los campos marcados en rojo.',
            });
        },
    });
};

const cancel = () => {
    router.visit(admin.schedules.index());
};
</script>
