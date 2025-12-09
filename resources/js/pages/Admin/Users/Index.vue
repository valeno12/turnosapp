<template>
    <Head title="Usuarios" />

    <AdminLayout title="Usuarios">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <DataTable
                title="Usuarios"
                :columns="columns"
                :data="data"
                v-model:search="search"
                v-model:sort-by="sortBy"
                v-model:sort-order="sortOrder"
                :is-loading="isLoading"
                @change-page="goToPage"
            >
                <template #actions>
                    <Can permission="manage_users">
                        <Button @click="handleCreate">
                            <Plus class="mr-2 h-4 w-4" />
                            Nuevo Usuario
                        </Button>
                    </Can>
                </template>

                <!-- Rol -->
                <template #cell-role="{ item }">
                    <Badge
                        :variant="
                            item.role === 'admin' ? 'destructive' : 'default'
                        "
                    >
                        {{ item.role === 'admin' ? 'Admin' : 'Instructor' }}
                    </Badge>
                </template>

                <!-- Permisos -->
                <template #cell-permissions="{ item }">
                    <div v-if="item.role === 'admin'">
                        <Badge variant="default">Todos los permisos</Badge>
                    </div>
                    <div
                        v-else-if="
                            item.permissions && item.permissions.length > 0
                        "
                        class="flex flex-wrap gap-1"
                    >
                        <Badge
                            v-for="permission in item.permissions.slice(0, 2)"
                            :key="permission.id"
                            variant="outline"
                            class="text-xs"
                        >
                            {{ permission.description }}
                        </Badge>
                        <Badge
                            v-if="item.permissions.length > 2"
                            variant="secondary"
                            class="text-xs"
                        >
                            +{{ item.permissions.length - 2 }}
                        </Badge>
                    </div>
                    <span v-else class="text-sm text-muted-foreground">
                        Sin permisos
                    </span>
                </template>

                <!-- Acciones -->
                <template #cell-acciones="{ item }">
                    <Can permission="manage_users">
                        <div class="flex items-center justify-end gap-2">
                            <Button
                                v-if="item.id !== currentUserId"
                                variant="outline"
                                size="sm"
                                @click="handleEdit(item.id)"
                                title="Editar"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>

                            <Button
                                v-if="
                                    item.role !== 'admin' &&
                                    item.id !== currentUserId
                                "
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

        <!-- Diálogo -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Estás seguro?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Se eliminará el usuario
                        <strong>{{ userToDelete?.name }}</strong
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
import { User } from '@/types/model';
import { LaravelPagination } from '@/types/pagination';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    data: LaravelPagination<User>;
    filters: DataTableFilters;
}

const props = defineProps<Props>();

const page = usePage();
const currentUserId = computed(() => page.props.auth.user?.id);

const showDeleteDialog = ref(false);
const userToDelete = ref<User | null>(null);

const { search, sortBy, sortOrder, isLoading, goToPage } = useDataTable(
    '/admin/users',
    props.filters,
);

const columns: TableColumn[] = [
    { key: 'name', label: 'Nombre', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Rol', sortable: true },
    { key: 'permissions', label: 'Permisos', sortable: false },
    {
        key: 'acciones',
        label: 'Acciones',
        sortable: false,
        headerClass: 'text-right',
        cellClass: 'text-right',
    },
];

const handleCreate = (): void => {
    router.visit('/admin/users/create');
};

const handleEdit = (id: number): void => {
    router.visit(`/admin/users/${id}/edit`);
};

const confirmDelete = (user: User): void => {
    userToDelete.value = user;
    showDeleteDialog.value = true;
};

const executeDelete = (): void => {
    if (!userToDelete.value) return;

    const user = userToDelete.value;

    router.delete(`/admin/users/${user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Usuario eliminado!', {
                description: `${user.name} ha sido eliminado exitosamente.`,
            });
            showDeleteDialog.value = false;
            userToDelete.value = null;
        },
        onError: (errors) => {
            toast.error('Error al eliminar', {
                description:
                    'No se pudo eliminar el usuario. Por favor, intenta nuevamente.',
            });
            console.error(errors);
            showDeleteDialog.value = false;
        },
    });
};

const cancelDelete = (): void => {
    showDeleteDialog.value = false;
    userToDelete.value = null;
};
</script>
