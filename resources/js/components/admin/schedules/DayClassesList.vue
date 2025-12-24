<template>
    <div class="space-y-2 overflow-y-auto pr-2">
        <Collapsible
            v-for="group in groupedClasses"
            :key="group.serviceName"
            v-model:open="localOpenStates[group.serviceName]"
        >
            <CollapsibleTrigger as-child>
                <button
                    class="flex w-full items-center justify-between rounded-lg border-l-4 bg-secondary/50 p-3 text-left transition-colors hover:bg-secondary"
                    :style="{ borderLeftColor: group.color }"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="h-3 w-3 flex-shrink-0 rounded-full"
                            :style="{ backgroundColor: group.color }"
                        />
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">{{
                                group.serviceName
                            }}</span>
                            <span class="text-sm text-muted-foreground">
                                ({{ group.classes.length }})
                            </span>
                        </div>
                    </div>
                    <ChevronDown
                        :size="16"
                        class="flex-shrink-0 transition-transform"
                        :class="{
                            'rotate-180': localOpenStates[group.serviceName],
                        }"
                    />
                </button>
            </CollapsibleTrigger>

            <CollapsibleContent
                class="mt-2 space-y-2 overflow-hidden transition-all data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down"
            >
                <button
                    v-for="classItem in group.classes"
                    :key="`${classItem.schedule.id}`"
                    @click="$emit('selectClass', classItem)"
                    class="ml-4 w-[calc(100%-1rem)] rounded-md border-l-2 bg-background p-3 text-left transition-colors hover:bg-accent"
                    :class="{ 'opacity-50': classItem.isCancelled }"
                    :style="{ borderLeftColor: group.color }"
                >
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold">
                                {{
                                    classItem.schedule.start_time.substring(
                                        0,
                                        5,
                                    )
                                }}
                            </div>
                            <div class="truncate text-sm text-muted-foreground">
                                {{ classItem.schedule.resource?.name }}
                                <span v-if="classItem.schedule.instructor">
                                    • {{ classItem.schedule.instructor.name }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-shrink-0 items-center gap-2">
                            <div
                                class="rounded bg-muted px-2 py-1 text-sm font-medium"
                            >
                                {{ classItem.bookingsCount }}/{{
                                    classItem.schedule.capacity
                                }}
                            </div>
                            <div
                                v-if="classItem.isCancelled"
                                class="rounded bg-destructive/10 px-2 py-1 text-xs font-medium text-destructive"
                            >
                                Cancelada
                            </div>
                        </div>
                    </div>
                </button>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>

<script setup lang="ts">
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Schedule } from '@/types/model';
import { ChevronDown } from 'lucide-vue-next';
import { computed, reactive, watch } from 'vue';

interface ClassData {
    schedule: Schedule;
    bookingsCount: number;
    isCancelled: boolean;
    date: string;
}

interface ServiceGroup {
    serviceName: string;
    color: string;
    classes: ClassData[];
}

interface Props {
    classes: ClassData[];
    initialOpenService?: string | null;
}

const props = defineProps<Props>();

defineEmits<{
    (e: 'selectClass', classData: ClassData): void;
}>();

// Estado local de colapses
const localOpenStates = reactive<Record<string, boolean>>({});

// Agrupar clases por servicio
const groupedClasses = computed<ServiceGroup[]>(() => {
    const groups = new Map<string, ServiceGroup>();

    props.classes.forEach((classItem) => {
        const serviceName = classItem.schedule.service?.name || 'Sin servicio';

        if (!groups.has(serviceName)) {
            groups.set(serviceName, {
                serviceName,
                color: classItem.schedule.service?.color || '#3b82f6',
                classes: [],
            });
        }

        groups.get(serviceName)!.classes.push(classItem);
    });

    // Ordenar clases por hora dentro de cada grupo
    groups.forEach((group) => {
        group.classes.sort((a, b) =>
            a.schedule.start_time.localeCompare(b.schedule.start_time),
        );
    });

    return Array.from(groups.values());
});

// Inicializar estados de collapse
watch(
    () => [props.classes, props.initialOpenService] as const,
    () => {
        groupedClasses.value.forEach((group) => {
            // Si hay initialOpenService, abrir solo ese; si no, todos cerrados
            localOpenStates[group.serviceName] =
                group.serviceName === props.initialOpenService;
        });
    },
    { immediate: true },
);
</script>
