<template>
    <Head :title="`Editar: ${schedule.service?.name}`" />

    <AdminLayout :title="`Editar: ${schedule.service?.name}`">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Editar Horario
                    </h2>
                    <p class="text-muted-foreground">
                        Modifica los datos del horario semanal
                    </p>
                </div>

                <ScheduleForm
                    :schedule="schedule"
                    :services="services"
                    :resources="resources"
                    :instructors="instructors"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ScheduleForm from '@/components/admin/schedules/ScheduleForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Resource, Schedule, Service, User } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    schedule: Schedule;
    services: Service[];
    resources: Resource[];
    instructors: User[];
}

defineProps<Props>();
</script>
