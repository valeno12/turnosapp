<template>
    <Head title="Nuevo Horario" />

    <AdminLayout title="Nuevo Horario">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Crear Horario Semanal
                    </h2>
                    <p class="text-muted-foreground">
                        Define un horario que se repetirá cada semana
                    </p>
                </div>

                <ScheduleForm
                    :services="services"
                    :resources="resources"
                    :instructors="instructors"
                    :initial-day="initialDay"
                    :initial-time="initialTime"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ScheduleForm from '@/components/admin/schedules/ScheduleForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Resource, Service, User } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    services: Service[];
    resources: Resource[];
    instructors: User[];
    initialDay?: number;
    initialTime?: string;
}

defineProps<Props>();
</script>
