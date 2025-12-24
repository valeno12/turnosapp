<template>
    <div class="space-y-4">
        <!-- Header: Navegación + Filtros -->
        <div class="flex items-center justify-between">
            <!-- Navegación de mes -->
            <div class="flex items-center gap-3">
                <Button variant="outline" size="icon" @click="previousMonth">
                    <ChevronLeft :size="18" />
                </Button>
                <h3 class="text-xl font-bold capitalize">
                    {{ currentMonthName }} {{ currentYear }}
                </h3>
                <Button variant="outline" size="icon" @click="nextMonth">
                    <ChevronRight :size="18" />
                </Button>
            </div>

            <!-- Filtros + Hoy -->
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="goToToday">
                    <Calendar :size="16" class="mr-2" />
                    Hoy
                </Button>

                <ScheduleFilters
                    :services="uniqueServices"
                    :resources="uniqueResources"
                    v-model:selected-services="selectedServices"
                    v-model:selected-resources="selectedResources"
                    v-model:capacity-filter="capacityFilter"
                    @clear="clearFilters"
                />
            </div>
        </div>

        <!-- Calendario -->
        <div class="overflow-hidden rounded-lg border bg-card">
            <!-- Header días de la semana -->
            <div class="grid grid-cols-7 border-b bg-muted/50">
                <div
                    v-for="day in weekDays"
                    :key="day"
                    class="p-2 text-center text-sm font-medium text-muted-foreground"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Días del mes -->
            <div class="grid grid-cols-7">
                <div
                    v-for="(day, index) in calendarDays"
                    :key="day.date"
                    class="min-h-[120px] border-r border-b p-2"
                    :class="{
                        'bg-muted/20': !isWorkingDay(
                            new Date(day.date).getDay(),
                        ),
                        'min-h-[140px]': isWorkingDay(
                            new Date(day.date).getDay(),
                        ),
                        'bg-muted/40 text-muted-foreground':
                            !day.isCurrentMonth,
                        'border-r-0': index % 7 === 6,
                    }"
                >
                    <!-- Fecha -->
                    <div class="mb-2 flex items-center justify-between">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-medium"
                            :class="{
                                'bg-primary text-primary-foreground':
                                    day.isToday,
                                'text-foreground':
                                    !day.isToday && day.isCurrentMonth,
                                'text-muted-foreground':
                                    !day.isToday && !day.isCurrentMonth,
                            }"
                        >
                            {{ day.dayNumber }}
                        </span>
                    </div>

                    <!-- Clases del día -->
                    <!-- Clases del día -->
                    <div
                        v-if="
                            isWorkingDay(new Date(day.date).getDay()) &&
                            getFilteredClassesForDay(day.date).length > 0
                        "
                        class="space-y-1"
                    >
                        <!-- Servicios agrupados (máximo 2) -->
                        <button
                            v-for="serviceGroup in getVisibleServicesForDay(
                                day.date,
                            )"
                            :key="`${serviceGroup.serviceName}-${day.date}`"
                            @click="
                                openDayClassesWithService(
                                    day.date,
                                    serviceGroup.serviceName,
                                )
                            "
                            class="w-full rounded border-l-2 bg-secondary/50 p-1.5 text-left transition-colors hover:bg-secondary"
                            :style="{ borderLeftColor: serviceGroup.color }"
                        >
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-xs font-semibold">
                                        {{ serviceGroup.serviceName }}
                                    </div>
                                    <div
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ serviceGroup.classCount }}
                                        {{
                                            serviceGroup.classCount === 1
                                                ? 'clase'
                                                : 'clases'
                                        }}
                                    </div>
                                </div>
                                <div
                                    class="ml-1 rounded bg-muted px-1.5 py-0.5 text-xs font-medium"
                                >
                                    {{ serviceGroup.totalBookings }}/{{
                                        serviceGroup.totalCapacity
                                    }}
                                </div>
                            </div>
                        </button>

                        <!-- Botón "+X más servicios" -->
                        <button
                            v-if="getHiddenServicesCount(day.date) > 0"
                            @click="openDayClasses(day.date)"
                            class="w-full rounded bg-muted/50 px-2 py-1 text-xs font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        >
                            +{{ getHiddenServicesCount(day.date) }} más
                        </button>
                    </div>

                    <!-- Sin clases -->
                    <div
                        v-else-if="isWorkingDay(new Date(day.date).getDay())"
                        class="text-xs text-muted-foreground"
                    >
                        Sin clases
                    </div>
                </div>
            </div>
        </div>
        <DayScheduleModal
            :open="!!selectedDate"
            :date="selectedDate"
            :classes="
                selectedDate ? getFilteredClassesForDay(selectedDate) : []
            "
            :initial-open-service="selectedServiceToOpen"
            @close="
                () => {
                    selectedDate = null;
                    selectedServiceToOpen = null;
                }
            "
            @view-students="handleViewStudents"
            @manage="handleManage"
        />
    </div>
</template>

<script setup lang="ts">
import ScheduleFilters from '@/components/admin/schedules/ScheduleFilters.vue';
import { Button } from '@/components/ui/button';
import { Schedule } from '@/types/model';
import { usePage } from '@inertiajs/vue3';
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import DayScheduleModal from './DayScheduleModal.vue';

interface Props {
    schedules: Schedule[];
}

interface ClassData {
    schedule: Schedule;
    bookingsCount: number;
    isCancelled: boolean;
    date: string;
}

const props = defineProps<Props>();

const page = usePage();
const tenant = computed(() => page.props.tenant as any);

const MAX_VISIBLE_CLASSES = 2;

// Estado
const currentDate = ref(new Date());
const selectedDate = ref<string | null>(null);

// Filtros
const selectedServices = ref<string[]>([]);
const selectedResources = ref<string[]>([]);
const capacityFilter = ref<'all' | 'available' | 'full'>('all');

const clearFilters = () => {
    selectedServices.value = [];
    selectedResources.value = [];
    capacityFilter.value = 'all';
};

// Servicios y recursos únicos
const uniqueServices = computed(() => {
    const servicesMap = new Map();
    props.schedules.forEach((schedule) => {
        if (schedule.service && !servicesMap.has(schedule.service.name)) {
            servicesMap.set(schedule.service.name, schedule.service);
        }
    });
    return Array.from(servicesMap.values());
});

const uniqueResources = computed(() => {
    const resourcesSet = new Set<string>();
    props.schedules.forEach((schedule) => {
        if (schedule.resource) {
            resourcesSet.add(schedule.resource.name);
        }
    });
    return Array.from(resourcesSet).sort();
});

// Calendario
const currentYear = computed(() => currentDate.value.getFullYear());
const currentMonth = computed(() => currentDate.value.getMonth());

const currentMonthName = computed(() => {
    return currentDate.value.toLocaleDateString('es-AR', { month: 'long' });
});

const weekDays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

const isWorkingDay = (dayOfWeek: number) => {
    const workingDays = tenant.value?.working_days || [1, 2, 3, 4, 5];
    return workingDays.includes(dayOfWeek);
};

const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days = [];

    // Días del mes anterior
    const firstDayOfWeek = firstDay.getDay();
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const date = new Date(year, month - 1, prevMonthLastDay - i);
        days.push({
            date: formatDate(date),
            dayNumber: date.getDate(),
            isCurrentMonth: false,
            isToday: isToday(date),
        });
    }

    // Días del mes actual
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const date = new Date(year, month, i);
        days.push({
            date: formatDate(date),
            dayNumber: i,
            isCurrentMonth: true,
            isToday: isToday(date),
        });
    }

    // Días del mes siguiente
    const remainingDays = 7 - (days.length % 7);
    if (remainingDays < 7) {
        for (let i = 1; i <= remainingDays; i++) {
            const date = new Date(year, month + 1, i);
            days.push({
                date: formatDate(date),
                dayNumber: i,
                isCurrentMonth: false,
                isToday: isToday(date),
            });
        }
    }

    return days;
});

// Clases por día
const getClassesForDay = (dateStr: string): ClassData[] => {
    const [year, month, day] = dateStr.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    const dayOfWeek = date.getDay();

    return props.schedules
        .filter((schedule) => schedule.day_of_week === dayOfWeek)
        .map((schedule) => ({
            schedule,
            bookingsCount: 0,
            isCancelled: false,
            date: dateStr,
        }))
        .sort((a, b) =>
            a.schedule.start_time.localeCompare(b.schedule.start_time),
        );
};

const getFilteredClassesForDay = (dateStr: string): ClassData[] => {
    const classes = getClassesForDay(dateStr);

    return classes.filter((classItem) => {
        // Filtro por servicio
        if (
            selectedServices.value.length > 0 &&
            !selectedServices.value.includes(
                classItem.schedule.service?.name || '',
            )
        ) {
            return false;
        }

        // Filtro por recurso
        if (
            selectedResources.value.length > 0 &&
            !selectedResources.value.includes(
                classItem.schedule.resource?.name || '',
            )
        ) {
            return false;
        }

        // Filtro por capacidad
        if (capacityFilter.value !== 'all') {
            const isFull =
                classItem.bookingsCount >= classItem.schedule.capacity;
            if (capacityFilter.value === 'full' && !isFull) return false;
            if (capacityFilter.value === 'available' && isFull) return false;
        }

        return true;
    });
};

// Helpers
const formatDate = (date: Date): string => {
    return date.toISOString().split('T')[0];
};

const isToday = (date: Date): boolean => {
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

// Navegación
const previousMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

const handleViewStudents = (classData: ClassData) => {
    console.log('Ver alumnos:', classData);
    // TODO: Implementar vista de alumnos
};

const handleManage = (classData: ClassData) => {
    console.log('Gestionar clase:', classData);
    // TODO: Implementar gestión de clase
};

interface ServiceSummary {
    serviceName: string;
    color: string;
    classCount: number;
    totalBookings: number;
    totalCapacity: number;
}

// Obtener servicios agrupados para un día
const getServicesForDay = (dateStr: string): ServiceSummary[] => {
    const classes = getFilteredClassesForDay(dateStr);
    const servicesMap = new Map<string, ServiceSummary>();

    classes.forEach((classItem) => {
        const serviceName = classItem.schedule.service?.name || 'Sin servicio';

        if (!servicesMap.has(serviceName)) {
            servicesMap.set(serviceName, {
                serviceName,
                color: classItem.schedule.service?.color || '#3b82f6',
                classCount: 0,
                totalBookings: 0,
                totalCapacity: 0,
            });
        }

        const summary = servicesMap.get(serviceName)!;
        summary.classCount++;
        summary.totalBookings += classItem.bookingsCount;
        summary.totalCapacity += classItem.schedule.capacity;
    });

    return Array.from(servicesMap.values()).sort((a, b) =>
        a.serviceName.localeCompare(b.serviceName),
    );
};

const getVisibleServicesForDay = (dateStr: string) => {
    return getServicesForDay(dateStr).slice(0, MAX_VISIBLE_CLASSES);
};

const getHiddenServicesCount = (dateStr: string) => {
    return Math.max(0, getServicesForDay(dateStr).length - MAX_VISIBLE_CLASSES);
};

// Abrir modal con todos los servicios cerrados
const openDayClasses = (dateStr: string) => {
    selectedServiceToOpen.value = null;
    selectedDate.value = dateStr;
};

// Abrir modal con un servicio específico desplegado
const openDayClassesWithService = (dateStr: string, serviceName: string) => {
    selectedServiceToOpen.value = serviceName;
    selectedDate.value = dateStr;
};

// Agregar al state
const selectedServiceToOpen = ref<string | null>(null);
</script>
