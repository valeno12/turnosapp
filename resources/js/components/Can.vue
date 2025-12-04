<!-- resources/js/components/Can.vue -->
<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';

interface Props {
    permission?: string;
    any?: string[];
    all?: string[];
}

const props = defineProps<Props>();

const { can, canAny, canAll } = usePermissions();

const hasPermission = computed(() => {
    if (props.permission) {
        return can(props.permission);
    }

    if (props.any && props.any.length > 0) {
        return canAny(...props.any);
    }

    if (props.all && props.all.length > 0) {
        return canAll(...props.all);
    }

    return false;
});
</script>

<template>
    <slot v-if="hasPermission" />
</template>
