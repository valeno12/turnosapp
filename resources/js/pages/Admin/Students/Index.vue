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
import { Student } from '@/types';
import { LaravelPagination } from '@/types/pagination';
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    data: LaravelPagination<Student>;
    filters: DataTableFilters;
}

const props = defineProps<Props>();

const showDeleteDialog = ref(false);
const studentToDelete = ref<Student | null>(null);

const { search, sortBy, sortOrder, isLoading, goToPage } = useDataTable(
    '/admin/students',
    props.filters,
);

const columns: TableColumn[] = [
    { key: 'name', label: 'Nombre', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'phone', label: 'Teléfono', sortable: false },
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
    router.visit('/admin/students/create');
};

const handleEdit = (id: number): void => {
    router.visit(`/admin/students/${id}/edit`);
};

const confirmDelete = (student: Student): void => {
    studentToDelete.value = student;
    showDeleteDialog.value = true;
};

const executeDelete = (): void => {
    if (!studentToDelete.value) return;

    const student = studentToDelete.value;

    router.delete(`/admin/students/${student.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Alumno eliminado!', {
                description: `${student.name} ha sido eliminado exitosamente.`,
            });
            showDeleteDialog.value = false;
            studentToDelete.value = null;
        },
        onError: (errors) => {
            toast.error('Error al eliminar', {
                description:
                    'No se pudo eliminar el alumno. Por favor, intenta nuevamente.',
            });
            console.error(errors);
            showDeleteDialog.value = false;
        },
    });
};

const cancelDelete = (): void => {
    showDeleteDialog.value = false;
    studentToDelete.value = null;
};
</script>

<template>
    <Head title="Alumnos" />

    <AdminLayout title="Alumnos">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <DataTable
                title="Alumnos"
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
                        Nuevo Alumno
                    </Button>
                </template>

                <!-- Estado -->
                <template #cell-is_active="{ item }">
                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </template>

                <!-- Acciones -->
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

        <!-- Diálogo -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Estás seguro?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Se eliminará el alumno
                        <strong>{{ studentToDelete?.name }}</strong
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
