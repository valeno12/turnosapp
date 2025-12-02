<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    title: string;
    breadcrumbs?: BreadcrumbItemType[];
}

const props = defineProps<Props>();

const computedBreadcrumbs = props.breadcrumbs || [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: props.title, href: '#' },
];
</script>

<template>
    <AppLayout :breadcrumbs="computedBreadcrumbs">
        <div class="container mx-auto py-6">
            <slot />
        </div>
    </AppLayout>
    <Toaster position="top-right" richColors />
</template>
