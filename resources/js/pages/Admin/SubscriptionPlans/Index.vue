<template>
    <Head title="Planes de Suscripción" />

    <AdminLayout title="Planes de Suscripción">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <DataTable
                title="Planes de Suscripción"
                :columns="columns"
                :data="data"
                v-model:search="search"
                v-model:sort-by="sortBy"
                v-model:sort-order="sortOrder"
                :is-loading="isLoading"
                @change-page="goToPage"
            >
                <template #actions>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Plan
                    </Button>
                </template>

                <template #cell-classes_per_week="{ item }">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <span>{{
                            item.classes_per_week === 0
                                ? 'Ilimitado'
                                : `${item.classes_per_week} por semana`
                        }}</span>
                    </div>
                </template>

                <template #cell-base_price="{ item }">
                    <div class="flex items-center gap-2">
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                        <span
                            >${{
                                parseFloat(item.base_price).toLocaleString(
                                    'es-AR',
                                )
                            }}</span
                        >
                    </div>
                </template>

                <template #cell-services="{ item }">
                    <div class="flex flex-wrap gap-1">
                        <Badge
                            v-for="service in item.services"
                            :key="service.id"
                            variant="outline"
                            class="gap-1"
                        >
                            <div
                                class="h-2 w-2 rounded-full"
                                :style="{ backgroundColor: service.color }"
                            ></div>
                            {{ service.name }}
                        </Badge>
                        <span
                            v-if="!item.services || item.services.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            Sin servicios
                        </span>
                    </div>
                </template>

                <template #cell-allows_makeups="{ item }">
                    <Badge
                        :variant="item.allows_makeups ? 'default' : 'secondary'"
                    >
                        {{ item.allows_makeups ? 'Sí' : 'No' }}
                    </Badge>
                </template>

                <template #cell-is_active="{ item }">
                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </template>

                <template #cell-acciones="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="handleEdit(item.id)"
                            title="Editar"
                        >
                            <Pencil class="h-4 w-4" />
                        </Button>

                        <Button
                            variant="destructive"
                            size="sm"
                            @click="confirmDelete(item)"
                            title="Eliminar"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Estás seguro?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Se eliminará <strong>{{ planToDelete?.name }}</strong
                        >. Esta acción no se puede deshacer.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelDelete">
                        Cancelar
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="executeDelete"
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                    >
                        Eliminar
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>

<script setup lang="ts">
import type { TableColumn } from '@/components/shared/DataTable.vue';
import DataTable from '@/components/shared/DataTable.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    useDataTable,
    type DataTableFilters,
} from '@/composables/useDataTable';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { SubscriptionPlan } from '@/types/model';
import { LaravelPagination } from '@/types/pagination';
import { Head, router } from '@inertiajs/vue3';
import { Calendar, DollarSign, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    data: LaravelPagination<SubscriptionPlan>;
    filters: DataTableFilters;
}

const props = defineProps<Props>();

const showDeleteDialog = ref(false);
const planToDelete = ref<SubscriptionPlan | null>(null);

const { search, sortBy, sortOrder, isLoading, goToPage } = useDataTable(
    '/admin/subscription-plans',
    props.filters,
);

const columns: TableColumn[] = [
    { key: 'name', label: 'Nombre', sortable: true },
    { key: 'classes_per_week', label: 'Frecuencia', sortable: true },
    { key: 'base_price', label: 'Precio', sortable: true },
    { key: 'services', label: 'Servicios', sortable: false },
    { key: 'allows_makeups', label: 'Recuperos', sortable: false },
    { key: 'is_active', label: 'Estado', sortable: false },
    {
        key: 'acciones',
        label: 'Acciones',
        sortable: false,
        headerClass: 'text-right',
        cellClass: 'text-right',
    },
];

const handleCreate = (): void => {
    router.visit('/admin/subscription-plans/create');
};

const handleEdit = (id: number): void => {
    router.visit(`/admin/subscription-plans/${id}/edit`);
};

const confirmDelete = (plan: SubscriptionPlan): void => {
    planToDelete.value = plan;
    showDeleteDialog.value = true;
};

const executeDelete = (): void => {
    if (!planToDelete.value) return;

    const plan = planToDelete.value;

    router.delete(`/admin/subscription-plans/${plan.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Plan eliminado!', {
                description: `${plan.name} ha sido eliminado exitosamente.`,
            });
            showDeleteDialog.value = false;
            planToDelete.value = null;
        },
        onError: (errors) => {
            toast.error('Error al eliminar', {
                description:
                    'No se pudo eliminar el plan. Por favor, intenta nuevamente.',
            });
            console.error(errors);
            showDeleteDialog.value = false;
        },
    });
};

const cancelDelete = (): void => {
    showDeleteDialog.value = false;
    planToDelete.value = null;
};
</script>
