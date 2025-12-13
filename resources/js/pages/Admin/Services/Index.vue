<template>
    <Head title="Servicios" />

    <AdminLayout title="Servicios">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <DataTable
                title="Servicios"
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
                        Nuevo Servicio
                    </Button>
                </template>

                <template #cell-color="{ item }">
                    <div class="flex items-center gap-2">
                        <div
                            class="h-6 w-6 rounded border"
                            :style="{ backgroundColor: item.color }"
                        ></div>
                        <span class="text-sm text-muted-foreground">{{
                            item.color
                        }}</span>
                    </div>
                </template>

                <template #cell-duration_minutes="{ item }">
                    <div class="flex items-center gap-2">
                        <Clock class="h-4 w-4 text-muted-foreground" />
                        <span>{{ item.duration_minutes }} min</span>
                    </div>
                </template>

                <template #cell-is_active="{ item }">
                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </template>

                <template #cell-acciones="{ item }">
                    <Can permission="manage_services">
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
                    </Can>
                </template>
            </DataTable>
        </div>

        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Estás seguro?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Se eliminará <strong>{{ serviceToDelete?.name }}</strong
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
import Can from '@/components/Can.vue';
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
import { Service } from '@/types/model';
import { LaravelPagination } from '@/types/pagination';
import { Head, router } from '@inertiajs/vue3';
import { Clock, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    data: LaravelPagination<Service>;
    filters: DataTableFilters;
}

const props = defineProps<Props>();

const showDeleteDialog = ref(false);
const serviceToDelete = ref<Service | null>(null);

const { search, sortBy, sortOrder, isLoading, goToPage } = useDataTable(
    '/admin/services',
    props.filters,
);

const columns: TableColumn[] = [
    { key: 'name', label: 'Nombre', sortable: true },
    { key: 'duration_minutes', label: 'Duración', sortable: true },
    { key: 'color', label: 'Color', sortable: false },
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
    router.visit('/admin/services/create');
};

const handleEdit = (id: number): void => {
    router.visit(`/admin/services/${id}/edit`);
};

const confirmDelete = (service: Service): void => {
    serviceToDelete.value = service;
    showDeleteDialog.value = true;
};

const executeDelete = (): void => {
    if (!serviceToDelete.value) return;

    const service = serviceToDelete.value;

    router.delete(`/admin/services/${service.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Servicio eliminado!', {
                description: `${service.name} ha sido eliminado exitosamente.`,
            });
            showDeleteDialog.value = false;
            serviceToDelete.value = null;
        },
        onError: (errors) => {
            toast.error('Error al eliminar', {
                description:
                    'No se pudo eliminar el servicio. Por favor, intenta nuevamente.',
            });
            console.error(errors);
            showDeleteDialog.value = false;
        },
    });
};

const cancelDelete = (): void => {
    showDeleteDialog.value = false;
    serviceToDelete.value = null;
};
</script>
