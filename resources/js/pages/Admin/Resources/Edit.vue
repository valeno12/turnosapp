<template>
    <Head :title="`Editar: ${resource.name}`" />

    <AdminLayout :title="`Editar: ${resource.name}`">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Editar Recurso
                    </h2>
                    <p class="text-muted-foreground">
                        Modifica los datos del recurso "{{ resource.name }}"
                    </p>
                </div>

                <ResourceForm :resource="resource" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ResourceForm from '@/components/admin/resources/ResourceForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Resource } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    resource: Resource;
}

defineProps<Props>();
</script>
