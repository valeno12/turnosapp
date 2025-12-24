<template>
    <div ref="exportGridRef" class="overflow-auto rounded-lg border bg-card">
        <div class="min-w-[1200px]">
            <!-- Header con días -->
            <div
                class="sticky top-0 z-10 grid border-b bg-muted/50"
                :style="gridStyle"
            >
                <div class="border-r p-3 text-sm font-semibold">Horario</div>
                <div
                    v-for="day in activeDays"
                    :key="day.value"
                    class="border-r p-3 text-center text-sm font-semibold last:border-r-0"
                >
                    {{ day.label }}
                </div>
            </div>

            <!-- Grilla de horarios -->
            <div>
                <div
                    v-for="slot in isExporting ? nonEmptySlots : timeSlots"
                    :key="slot"
                    class="grid border-b transition-all duration-200"
                    :class="{
                        'hover:bg-muted/30': !isCollapsed(slot) && !isExporting,
                    }"
                    :style="gridStyle"
                >
                    <!-- Hora con botón de colapsar -->
                    <div
                        class="group flex items-center justify-between gap-2 border-r p-3 text-sm font-medium text-muted-foreground"
                    >
                        <span>{{ slot }}</span>
                        <button
                            v-if="!isExporting"
                            @click="toggleCollapse(slot)"
                            class="rounded p-1 opacity-0 transition-opacity group-hover:opacity-100 hover:bg-accent hover:opacity-100"
                            :title="isCollapsed(slot) ? 'Expandir' : 'Colapsar'"
                        >
                            <ChevronDown
                                :size="14"
                                class="transition-transform"
                                :class="{ 'rotate-180': isCollapsed(slot) }"
                            />
                        </button>
                    </div>

                    <!-- Días -->
                    <div
                        v-for="day in activeDays"
                        :key="`${day.value}-${slot}`"
                        class="group relative border-r border-gray-100 transition-all duration-200 last:border-r-0"
                        :class="
                            isCollapsed(slot) && !isExporting ? 'h-8' : 'h-24'
                        "
                    >
                        <!-- Solo mostrar contenido si NO está colapsado o si está exportando -->
                        <template v-if="!isCollapsed(slot) || isExporting">
                            <ScheduleBlock
                                v-for="(schedule, index) in getSchedulesForSlot(
                                    day.value,
                                    slot,
                                )"
                                :key="schedule.id"
                                :schedule="schedule"
                                :index="index"
                                :total-conflicts="
                                    getSchedulesForSlot(day.value, slot).length
                                "
                                @click="!isExporting && $emit('edit', schedule)"
                            />

                            <button
                                v-if="!isExporting"
                                @click="
                                    $emit('create', {
                                        day: day.value,
                                        time: slot,
                                    })
                                "
                                class="absolute top-1 right-1 z-50 rounded-md bg-primary/10 p-1.5 text-primary opacity-0 transition-opacity group-hover:opacity-100 hover:bg-primary/20"
                                title="Agregar horario"
                            >
                                <Plus :size="16" />
                            </button>
                        </template>

                        <!-- Indicador de horarios cuando está colapsado (solo si NO está exportando) -->
                        <div
                            v-else-if="
                                getSchedulesForSlot(day.value, slot).length > 0
                            "
                            class="absolute inset-0 flex items-center justify-center"
                        >
                            <div
                                class="h-1 w-8 rounded-full bg-primary/50"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Schedule } from '@/types/model';
import { usePage } from '@inertiajs/vue3';
import { ChevronDown, Plus } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import ScheduleBlock from './ScheduleBlock.vue';

interface Props {
    schedules: Schedule[];
}

const props = defineProps<Props>();

defineEmits<{
    (e: 'edit', schedule: Schedule): void;
    (e: 'create', data: { day: number; time: string }): void;
}>();

const page = usePage();
const tenant = computed(() => page.props.tenant as any);

// Estado de filas colapsadas
const collapsedSlots = ref<Set<string>>(new Set());

// Cargar estado desde localStorage
onMounted(() => {
    const saved = localStorage.getItem('schedule-collapsed-slots');
    if (saved) {
        collapsedSlots.value = new Set(JSON.parse(saved));
    }
});

// Toggle collapse
const toggleCollapse = (slot: string) => {
    if (collapsedSlots.value.has(slot)) {
        collapsedSlots.value.delete(slot);
    } else {
        collapsedSlots.value.add(slot);
    }

    // Guardar en localStorage
    localStorage.setItem(
        'schedule-collapsed-slots',
        JSON.stringify([...collapsedSlots.value]),
    );
};

// Verificar si está colapsado
const isCollapsed = (slot: string) => {
    return collapsedSlots.value.has(slot);
};

// Definición de días
const allDays = [
    { value: 1, label: 'Lunes' },
    { value: 2, label: 'Martes' },
    { value: 3, label: 'Miércoles' },
    { value: 4, label: 'Jueves' },
    { value: 5, label: 'Viernes' },
    { value: 6, label: 'Sábado' },
    { value: 0, label: 'Domingo' },
];

// Filtrar días laborables según configuración del tenant
const activeDays = computed(() => {
    const workingDays = tenant.value?.working_days || [1, 2, 3, 4, 5];
    return allDays.filter((day) => workingDays.includes(day.value));
});

// Estilo dinámico de columnas (Hora + Días activos)
const gridStyle = computed(() => ({
    gridTemplateColumns: `100px repeat(${activeDays.value.length}, 1fr)`,
}));

// Generar slots de tiempo (Filas)
const timeSlots = computed(() => {
    const slots: string[] = [];
    const startTime = tenant.value?.schedule_start_time || '07:00';
    const endTime = tenant.value?.schedule_end_time || '21:00';

    const [startHour] = startTime.split(':').map(Number);
    const [endHour] = endTime.split(':').map(Number);

    for (let hour = startHour; hour <= endHour; hour++) {
        slots.push(`${String(hour).padStart(2, '0')}:00`);
    }

    return slots;
});

// Filtra los horarios que pertenecen a este bloque de hora
const getSchedulesForSlot = (dayOfWeek: number, timeSlot: string) => {
    return props.schedules.filter((schedule) => {
        // 1. Coincidir día
        if (schedule.day_of_week !== dayOfWeek) return false;

        // 2. Coincidir HORA (ignorando minutos)
        // timeSlot es "09:00", schedule.start_time es "09:30:00"
        const slotHour = parseInt(timeSlot.split(':')[0]);
        const scheduleHour = parseInt(schedule.start_time.split(':')[0]);

        return slotHour === scheduleHour;
    });
};

// Ref para el contenedor de exportación
const exportGridRef = ref<HTMLElement | null>(null);
const isExporting = ref(false);

// Función para exportar
const exportAsImage = async () => {
    // isExporting.value = true;
    // await nextTick();
    // if (!exportGridRef.value) return;
    // try {
    //     const dataUrl = await domtoimage.toPng(exportGridRef.value, {
    //         quality: 1,
    //         bgcolor: '#ffffff',
    //     });
    //     const link = document.createElement('a');
    //     link.download = `horarios-${new Date().toISOString().split('T')[0]}.png`;
    //     link.href = dataUrl;
    //     link.click();
    //     toast.success('¡Imagen exportada!');
    // } catch (error) {
    //     console.error(error);
    //     toast.error('Error al exportar');
    // } finally {
    //     isExporting.value = false;
    // }
};

// Slots que tienen al menos un horario en algún día
const nonEmptySlots = computed(() => {
    return timeSlots.value.filter((slot) => {
        return activeDays.value.some(
            (day) => getSchedulesForSlot(day.value, slot).length > 0,
        );
    });
});

defineExpose({ exportAsImage });
</script>
