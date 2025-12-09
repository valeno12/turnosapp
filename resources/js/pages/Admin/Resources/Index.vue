<template>
    <Head title="Recursos" />

    <AdminLayout title="Recursos">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <DataTable
                title="Recursos"
                :columns="columns"
                :data="data"
                v-model:search="search"
                v-model:sort-by="sortBy"
                v-model:sort-order="sortOrder"
                :is-loading="isLoading"
                @change-page="goToPage"
            >
                <!-- Botón de crear en el header -->
                <template #actions>
                    <Can permission="manage_resources">
                        <Button @click="handleCreate">
                            <Plus class="mr-2 h-4 w-4" />
                            Nuevo Recurso
                        </Button>
                    </Can>
                </template>

                <!-- Columna de capacidad personalizada -->
                <template #cell-capacity="{ item }">
                    <div class="flex items-center gap-2">
                        <Users class="h-4 w-4 text-muted-foreground" />
                        <span>{{ item.capacity }} personas</span>
                    </div>
                </template>

                <!-- Columna de tipo -->
                <template #cell-is_self_assignable="{ item }">
                    <Badge
                        :variant="
                            item.is_self_assignable ? 'default' : 'secondary'
                        "
                    >
                        {{
                            item.is_self_assignable
                                ? 'Autoasignable'
                                : 'Solo Admin'
                        }}
                    </Badge>
                </template>

                <!-- Columna de estado -->
                <template #cell-is_active="{ item }">
                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </template>

                <!-- Columna de acciones personalizada -->
                <template #cell-acciones="{ item }">
                    <Can permission="manage_resources">
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

        <!-- Diálogo de confirmación de eliminación -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Estás seguro?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Se eliminará
                        <strong>{{ resourceToDelete?.name }}</strong
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
import { Resource } from '@/types/model';
import { LaravelPagination } from '@/types/pagination';
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    data: LaravelPagination<Resource>;
    filters: DataTableFilters;
}

const props = defineProps<Props>();

// Estado para el diálogo de confirmación
const showDeleteDialog = ref(false);
const resourceToDelete = ref<Resource | null>(null);

// Usar el composable para manejar la tabla
const { search, sortBy, sortOrder, isLoading, goToPage } = useDataTable(
    '/admin/resources',
    props.filters,
);

// Definición de columnas
const columns: TableColumn[] = [
    {
        key: 'name',
        label: 'Nombre',
        sortable: true,
    },
    {
        key: 'capacity',
        label: 'Capacidad',
        sortable: true,
    },
    {
        key: 'is_self_assignable',
        label: 'Tipo',
        sortable: false,
    },
    {
        key: 'is_active',
        label: 'Estado',
        sortable: false,
    },
    {
        key: 'acciones',
        label: 'Acciones',
        sortable: false,
        headerClass: 'text-right',
        cellClass: 'text-right',
    },
];

// Funciones de navegación
const handleCreate = (): void => {
    router.visit('/admin/resources/create');
};

const handleEdit = (id: number): void => {
    router.visit(`/admin/resources/${id}/edit`);
};

// Confirmar eliminación
const confirmDelete = (resource: Resource): void => {
    resourceToDelete.value = resource;
    showDeleteDialog.value = true;
};

// Ejecutar eliminación
const executeDelete = (): void => {
    if (!resourceToDelete.value) return;

    const resource = resourceToDelete.value;

    router.delete(`/admin/resources/${resource.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Recurso eliminado!', {
                description: `${resource.name} ha sido eliminado exitosamente.`,
            });
            showDeleteDialog.value = false;
            resourceToDelete.value = null;
        },
        onError: (errors) => {
            toast.error('Error al eliminar', {
                description:
                    'No se pudo eliminar el recurso. Por favor, intenta nuevamente.',
            });
            console.error(errors);
            showDeleteDialog.value = false;
        },
    });
};

// Cancelar eliminación
const cancelDelete = (): void => {
    showDeleteDialog.value = false;
    resourceToDelete.value = null;
};
</script>
