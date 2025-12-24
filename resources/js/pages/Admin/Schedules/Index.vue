<template>
    <Head title="Horarios" />

    <AdminLayout title="Horarios">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Horarios</h2>
                    <p class="text-sm text-muted-foreground">
                        Gestiona horarios semanales y reservas mensuales
                    </p>
                </div>

                <Button
                    v-if="activeTab === 'weekly'"
                    @click="handleExport"
                    variant="outline"
                    :disabled="isExporting"
                >
                    <Camera class="mr-2 h-4 w-4" />
                    Exportar para Instagram
                </Button>
            </div>

            <!-- Tabs -->
            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full max-w-md grid-cols-2">
                    <TabsTrigger value="weekly">Vista Semanal</TabsTrigger>
                    <TabsTrigger value="monthly">Vista Mensual</TabsTrigger>
                </TabsList>

                <TabsContent value="weekly" class="mt-4">
                    <p class="mb-4 text-sm text-muted-foreground">
                        Haz click en un espacio vacío para crear un horario, o
                        en un bloque existente para editarlo
                    </p>
                    <ScheduleGrid
                        ref="gridRef"
                        :schedules="data.data"
                        @edit="handleEdit"
                        @create="handleCreate"
                    />
                </TabsContent>

                <TabsContent value="monthly" class="mt-4">
                    <MonthlyView :schedules="data.data" />
                </TabsContent>
            </Tabs>
        </div>

        <!-- Modal -->
        <ScheduleModal
            :open="modalOpen"
            :schedule="selectedSchedule"
            :services="services"
            :resources="resources"
            :instructors="instructors"
            :initial-day="initialDay"
            :initial-time="initialTime"
            @close="handleCloseModal"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import MonthlyView from '@/components/admin/schedules/MonthlyView.vue';
import ScheduleGrid from '@/components/admin/schedules/ScheduleGrid.vue';
import ScheduleModal from '@/components/admin/schedules/ScheduleModal.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Resource, Schedule, Service, User } from '@/types/model';
import { Head, router } from '@inertiajs/vue3';
import { Camera } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    data: {
        data: Schedule[];
    };
    services: Service[];
    resources: Resource[];
    instructors: User[];
}

defineProps<Props>();

const activeTab = ref('weekly');
const modalOpen = ref(false);
const selectedSchedule = ref<Schedule | null>(null);
const initialDay = ref<number | undefined>(undefined);
const initialTime = ref<string | undefined>(undefined);
const gridRef = ref<any>(null);
const isExporting = ref(false);

const handleCreate = (data: { day: number; time: string }) => {
    selectedSchedule.value = null;
    initialDay.value = data.day;
    initialTime.value = data.time;
    modalOpen.value = true;
};

const handleEdit = (schedule: Schedule) => {
    selectedSchedule.value = schedule;
    initialDay.value = undefined;
    initialTime.value = undefined;
    modalOpen.value = true;
};

const handleCloseModal = () => {
    modalOpen.value = false;
    selectedSchedule.value = null;
    initialDay.value = undefined;
    initialTime.value = undefined;

    // Recargar datos
    router.reload({ only: ['data'] });
};

const handleExport = async () => {
    if (gridRef.value?.exportAsImage) {
        isExporting.value = true;
        await gridRef.value.exportAsImage();
        isExporting.value = false;
    }
};
</script>
