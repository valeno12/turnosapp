<template>
    <Head title="Crear Plan" />

    <AdminLayout title="Nuevo Plan de Suscripción">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Nuevo Plan de Suscripción
                    </h2>
                    <p class="text-muted-foreground">
                        Completa el formulario para agregar un nuevo plan
                        mensual
                    </p>
                </div>

                <SubscriptionPlanForm :available-services="services" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import SubscriptionPlanForm from '@/components/admin/subscription-plans/SubscriptionPlanForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Service } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    services: Service[];
}

defineProps<Props>();
</script>
