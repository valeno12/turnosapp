<template>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-muted-foreground">Horario</div>
                <div class="font-medium">
                    {{ classData.schedule.start_time.substring(0, 5) }}
                </div>
            </div>
            <div>
                <div class="text-sm text-muted-foreground">Duración</div>
                <div class="font-medium">
                    {{ classData.schedule.service?.duration_minutes }} min
                </div>
            </div>
            <div>
                <div class="text-sm text-muted-foreground">Sala</div>
                <div class="font-medium">
                    {{ classData.schedule.resource?.name }}
                </div>
            </div>
            <div>
                <div class="text-sm text-muted-foreground">Capacidad</div>
                <div class="font-medium">
                    {{ classData.bookingsCount }}/{{
                        classData.schedule.capacity
                    }}
                </div>
            </div>
            <div v-if="classData.schedule.instructor" class="col-span-2">
                <div class="text-sm text-muted-foreground">Instructor</div>
                <div class="font-medium">
                    {{ classData.schedule.instructor.name }}
                </div>
            </div>
            <div class="col-span-2">
                <div class="text-sm text-muted-foreground">Fecha</div>
                <div class="font-medium">
                    {{ formatDateLong(classData.date) }}
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <Button class="flex-1" @click="$emit('viewStudents')">
                Ver Alumnos
            </Button>
            <Button variant="outline" class="flex-1" @click="$emit('manage')">
                Gestionar Clase
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Schedule } from '@/types/model';

interface ClassData {
    schedule: Schedule;
    bookingsCount: number;
    isCancelled: boolean;
    date: string;
}

interface Props {
    classData: ClassData;
}

defineProps<Props>();

defineEmits<{
    (e: 'viewStudents'): void;
    (e: 'manage'): void;
}>();

const formatDateLong = (dateStr: string): string => {
    const [year, month, day] = dateStr.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('es-AR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });
};
</script>
