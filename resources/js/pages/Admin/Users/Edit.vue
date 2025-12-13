<template>
    <Head :title="`Editar ${user.name}`" />

    <AdminLayout :title="`Editar Usuario`">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Editar Usuario
                    </h2>
                    <p class="text-muted-foreground">
                        Modifica los datos del empleado
                    </p>
                </div>

                <UserForm
                    :user="user"
                    :permissions-by-group="permissionsByGroup"
                />
            </div>
        </div>
    </AdminLayout>
</template>
<script setup lang="ts">
import UserForm from '@/components/admin/users/UserForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Permission, User } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    user: User;
    permissionsByGroup: Record<string, Permission[]>;
}

defineProps<Props>();
</script>
