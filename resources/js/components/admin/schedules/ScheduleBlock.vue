<template>
    <div
        class="group absolute cursor-pointer rounded-md border-l-4 bg-white px-2 py-1 text-xs text-gray-900 transition-all hover:z-50 hover:shadow-md dark:bg-gray-800 dark:text-gray-100"
        :class="blockClasses"
        :style="blockStyle"
        @click="$emit('click')"
        :title="schedule.service?.name"
    >
        <div class="truncate font-bold shadow-sm">
            {{ schedule.service?.name || 'Sin servicio' }}
        </div>

        <div
            v-if="schedule.resource"
            class="mt-0.5 truncate text-[10px] font-medium opacity-80"
        >
            {{ schedule.resource.name }}
        </div>

        <div class="mt-1 flex items-center justify-between opacity-90">
            <span
                class="rounded bg-gray-100 px-1 font-mono text-[10px] dark:bg-gray-700"
            >
                {{ schedule.start_time.substring(0, 5) }}
            </span>

            <div class="flex items-center gap-1">
                <span
                    class="flex items-center gap-0.5 text-[10px] font-semibold"
                    :class="{
                        'text-red-600 dark:text-red-400': isFull,
                        'text-amber-600 dark:text-amber-400':
                            isAlmostFull && !isFull,
                    }"
                    title="Inscriptos / Capacidad"
                >
                    <!-- <span class="opacity-70">👥</span>
                    {{ currentCapacity }}/{{ schedule.capacity }} -->
                </span>
            </div>
        </div>

        <div
            v-if="schedule.instructor"
            class="mt-1 truncate text-[10px] opacity-75"
        >
            👤 {{ schedule.instructor.name }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { Schedule } from '@/types/model';
import { computed } from 'vue';

interface Props {
    schedule: Schedule;
    index: number;
    totalConflicts: number;
}

const props = defineProps<Props>();

defineEmits<{
    (e: 'click'): void;
}>();

// Lógica de Capacidad (se asume que schedule.enrollments existe o se ajustará)
// const currentCapacity = computed(() => {
//     // TODO: Asegurarse que el modelo Schedule incluya la relación 'enrollments'
//     return props.schedule.enrollments?.length || 0;
// });

const availableSpots = computed(() => {
    // return props.schedule.capacity - currentCapacity.value;
    return props.schedule.capacity; // Placeholder hasta que se implemente la lógica de inscripciones
});

const isFull = computed(() => availableSpots.value <= 0);

const isAlmostFull = computed(() => {
    const percentAvailable =
        (availableSpots.value / props.schedule.capacity) * 100;
    return percentAvailable > 0 && percentAvailable <= 20 && !isFull.value;
});

// Determinar el color del borde izquierdo basado en estado o servicio
const statusColor = computed(() => {
    if (isFull.value) return '#ef4444'; // Tailwind red-500
    if (isAlmostFull.value) return '#f59e0b'; // Tailwind amber-500
    return props.schedule.service?.color || '#3b82f6'; // Color del servicio o azul default
});

// Cálculo de estilos dinámicos para posición y dimensiones
const blockStyle = computed(() => {
    const duration = props.schedule.service?.duration_minutes || 60;
    const height = `calc(${(duration / 60) * 100}% - 2px)`;

    const minutes = parseInt(props.schedule.start_time.split(':')[1]);
    const top = `${(minutes / 60) * 100}%`;

    const widthPercent = 100 / props.totalConflicts;
    const width = `calc(${widthPercent}% - 4px)`;

    const leftPercent = widthPercent * props.index;
    const left = `calc(${leftPercent}% + 2px)`;

    return {
        height,
        top,
        width,
        left,
        borderLeftColor: statusColor.value, // Solo aplicamos color al borde
        zIndex: 10 + props.index,
    };
});

// Clases estáticas adicionales
const blockClasses = computed(() => {
    return 'shadow-sm ring-1 ring-black/5 dark:ring-white/10';
});
</script>
