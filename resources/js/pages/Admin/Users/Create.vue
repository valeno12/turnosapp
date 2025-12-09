<template>
    <Head title="Crear Usuario" />

    <AdminLayout title="Nuevo Usuario">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Nuevo Usuario
                    </h2>
                    <p class="text-muted-foreground">
                        Completa el formulario para agregar un nuevo empleado
                    </p>
                </div>

                <UserForm :permissions-by-group="permissionsByGroup" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import UserForm from '@/components/admin/users/UserForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Permission } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    permissionsByGroup: Record<string, Permission[]>;
}

defineProps<Props>();
</script>
