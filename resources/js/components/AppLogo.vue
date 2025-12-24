<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

// Obtener tenant de Inertia (compartido globalmente)
const tenant = computed(() => page.props.tenant as any);
</script>

<template>
    <div class="flex aspect-square size-10 items-center justify-center">
        <AppLogoIcon class="size-10" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ tenant?.name || 'TurnosApp' }}
        </span>
    </div>
</template>
