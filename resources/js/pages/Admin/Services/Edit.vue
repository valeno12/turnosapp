<template>
    <Head :title="`Editar: ${service.name}`" />

    <AdminLayout :title="`Editar: ${service.name}`">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Editar Servicio
                    </h2>
                    <p class="text-muted-foreground">
                        Modifica los datos del servicio "{{ service.name }}"
                    </p>
                </div>

                <ServiceForm :service="service" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ServiceForm from '@/components/admin/services/ServiceForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Service } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    service: Service;
}

defineProps<Props>();
</script>
