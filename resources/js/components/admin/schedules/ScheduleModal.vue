<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-h-[90vh] max-w-3xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>
                    {{ schedule ? 'Editar Horario' : 'Nuevo Horario' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        schedule
                            ? 'Modifica los datos del horario semanal'
                            : 'Define un horario que se repetirá cada semana'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
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
                        💡 Capacidad máxima: {{ selectedResource.capacity }}
                    </p>
                </FormField>

                <!-- Instructor -->
                <FormField
                    id="user_id"
                    label="Instructor"
                    hint="Opcional"
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
                <div class="grid gap-4 md:grid-cols-2">
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

                <!-- Sección de Alumnos (preparada para el futuro) -->
                <div v-if="schedule" class="rounded-lg border p-4">
                    <h3 class="mb-2 font-semibold">Alumnos Inscritos</h3>
                    <p class="text-sm text-muted-foreground">
                        La gestión de alumnos estará disponible próximamente
                    </p>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="handleClose"
                        :disabled="form.processing"
                    >
                        Cancelar
                    </Button>
                    <Button
                        v-if="schedule"
                        type="button"
                        variant="destructive"
                        @click="handleDelete"
                        :disabled="form.processing"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Eliminar
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ schedule ? 'Guardar' : 'Crear' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import FormField from '@/components/shared/FormField.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Resource, Schedule, Service, User } from '@/types/model';
import { router, useForm } from '@inertiajs/vue3';
import { Save, Trash2 } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    open: boolean;
    schedule?: Schedule | null;
    services: Service[];
    resources: Resource[];
    instructors: User[];
    initialDay?: number;
    initialTime?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const form = useForm({
    service_id: '',
    resource_id: '',
    user_id: '',
    day_of_week: '',
    start_time: '',
    capacity: 1,
});

// Cuando se abre el modal, resetear el form
watch(
    () => [props.open, props.schedule] as const,
    ([isOpen, newSchedule]) => {
        if (!isOpen) return; // Solo actuar cuando el modal se abre

        if (newSchedule) {
            // MODO EDITAR
            form.service_id = newSchedule.service_id?.toString() || '';
            form.resource_id = newSchedule.resource_id?.toString() || '';
            form.user_id = newSchedule.user_id?.toString() || '';
            form.day_of_week = newSchedule.day_of_week?.toString() || '';
            form.start_time = newSchedule.start_time?.substring(0, 5) || '';
            form.capacity = newSchedule.capacity || 1;
        } else {
            // MODO CREAR
            form.reset();
            form.service_id = '';
            form.resource_id = '';
            form.user_id = '';
            form.day_of_week = props.initialDay?.toString() || '';
            form.start_time = props.initialTime || '';
            form.capacity = 1;
        }
        form.clearErrors();
    },
    { immediate: true },
);
// Cuando cambia el recurso, autosetear capacidad
watch(
    () => form.resource_id,
    (newResourceId) => {
        if (newResourceId) {
            const resource = props.resources.find(
                (r) => r.id === Number(newResourceId),
            );
            if (resource) {
                form.capacity = resource.capacity;
            }
        }
    },
);

const selectedResource = computed(() => {
    if (!form.resource_id) return null;
    return props.resources.find((r) => r.id === Number(form.resource_id));
});

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
            emit('close');
        },
        onError: () => {
            toast.error('Error al guardar', {
                description: 'Por favor, revisa los campos marcados en rojo.',
            });
        },
    });
};

const handleDelete = () => {
    if (!props.schedule) return;

    if (
        confirm(
            `¿Estás seguro de eliminar este horario de ${props.schedule.service?.name}?`,
        )
    ) {
        router.delete(`/admin/schedules/${props.schedule.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Horario eliminado', {
                    description: 'El horario se ha eliminado correctamente.',
                });
                emit('close');
            },
        });
    }
};

const handleClose = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>
