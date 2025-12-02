<template>
    <Head :title="`Editar: ${plan.name}`" />

    <AdminLayout :title="`Editar: ${plan.name}`">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Editar Plan de Suscripción
                    </h2>
                    <p class="text-muted-foreground">
                        Modifica los datos del plan "{{ plan.name }}"
                    </p>
                </div>

                <SubscriptionPlanForm
                    :plan="plan"
                    :available-services="services"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import SubscriptionPlanForm from '@/components/admin/subscription-plans/SubscriptionPlanForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Service, SubscriptionPlan } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    plan: SubscriptionPlan;
    services: Service[];
}

defineProps<Props>();
</script>
