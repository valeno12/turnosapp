<template>
    <div class="flex items-center gap-2">
        <!-- Filtro por servicio -->
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm" class="gap-2">
                    <Filter :size="16" />
                    Servicios
                    <span
                        v-if="selectedServices.length > 0"
                        class="ml-1 rounded bg-primary px-1.5 py-0.5 text-xs text-primary-foreground"
                    >
                        {{ selectedServices.length }}
                    </span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" class="w-48">
                <DropdownMenuLabel>Tipo de clase</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                    v-for="service in services"
                    :key="service.name"
                    :checked="selectedServices.includes(service.name)"
                    @update:checked="
                        (checked: boolean) =>
                            toggleService(service.name, checked)
                    "
                >
                    <div class="flex items-center gap-2">
                        <div
                            class="h-3 w-3 rounded-full"
                            :style="{ backgroundColor: service.color }"
                        />
                        {{ service.name }}
                    </div>
                </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <!-- Filtro por recurso -->
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm" class="gap-2">
                    <Filter :size="16" />
                    Recursos
                    <span
                        v-if="selectedResources.length > 0"
                        class="ml-1 rounded bg-primary px-1.5 py-0.5 text-xs text-primary-foreground"
                    >
                        {{ selectedResources.length }}
                    </span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" class="w-40">
                <DropdownMenuLabel>Sala / Espacio</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                    v-for="resource in resources"
                    :key="resource"
                    :checked="selectedResources.includes(resource)"
                    @update:checked="
                        (checked: boolean) => toggleResource(resource, checked)
                    "
                >
                    {{ resource }}
                </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <!-- Filtro por capacidad -->
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm" class="gap-2">
                    <Filter :size="16" />
                    Cupos
                    <span
                        v-if="capacityFilter !== 'all'"
                        class="ml-1 rounded bg-primary px-1.5 py-0.5 text-xs text-primary-foreground"
                    >
                        1
                    </span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" class="w-48">
                <DropdownMenuLabel>Estado de cupos</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                    :checked="capacityFilter === 'all'"
                    @update:checked="
                        () => $emit('update:capacityFilter', 'all')
                    "
                >
                    Todas
                </DropdownMenuCheckboxItem>
                <DropdownMenuCheckboxItem
                    :checked="capacityFilter === 'available'"
                    @update:checked="
                        () => $emit('update:capacityFilter', 'available')
                    "
                >
                    Con cupos disponibles
                </DropdownMenuCheckboxItem>
                <DropdownMenuCheckboxItem
                    :checked="capacityFilter === 'full'"
                    @update:checked="
                        () => $emit('update:capacityFilter', 'full')
                    "
                >
                    Llenas
                </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <!-- Limpiar filtros -->
        <Button
            v-if="hasActiveFilters"
            variant="ghost"
            size="sm"
            @click="$emit('clear')"
            class="gap-2"
        >
            <X :size="16" />
            Limpiar
        </Button>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Service } from '@/types/model';
import { Filter, X } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    services: Service[];
    resources: string[];
    selectedServices: string[];
    selectedResources: string[];
    capacityFilter: 'all' | 'available' | 'full';
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:selectedServices', value: string[]): void;
    (e: 'update:selectedResources', value: string[]): void;
    (e: 'update:capacityFilter', value: 'all' | 'available' | 'full'): void;
    (e: 'clear'): void;
}>();

const hasActiveFilters = computed(
    () =>
        props.selectedServices.length > 0 ||
        props.selectedResources.length > 0 ||
        props.capacityFilter !== 'all',
);

const toggleService = (serviceName: string, checked: boolean) => {
    if (checked) {
        emit('update:selectedServices', [
            ...props.selectedServices,
            serviceName,
        ]);
    } else {
        emit(
            'update:selectedServices',
            props.selectedServices.filter((s) => s !== serviceName),
        );
    }
};

const toggleResource = (resourceName: string, checked: boolean) => {
    if (checked) {
        emit('update:selectedResources', [
            ...props.selectedResources,
            resourceName,
        ]);
    } else {
        emit(
            'update:selectedResources',
            props.selectedResources.filter((r) => r !== resourceName),
        );
    }
};
</script>
