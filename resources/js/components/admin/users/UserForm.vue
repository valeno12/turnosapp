<template>
    <form @submit.prevent="submit" autocomplete="off">
        <Card>
            <CardContent class="space-y-6 pt-6">
                <UserFormFields
                    :permissions-by-group="permissionsByGroup"
                    :is-editing="!!user"
                />

                <div class="flex justify-end gap-4 pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancel"
                        :disabled="form.processing"
                    >
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ user ? 'Actualizar' : 'Crear' }} Usuario
                    </Button>
                </div>
            </CardContent>
        </Card>
    </form>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import admin from '@/routes/admin';
import { Permission, User } from '@/types/model';
import { router, useForm } from '@inertiajs/vue3';
import { provide } from 'vue';
import UserFormFields from './UserFormFields.vue';

interface Props {
    permissionsByGroup: Record<string, Permission[]>;
    user?: User;
}

const props = defineProps<Props>();
console.log('🔍 User recibido:', props.user);
console.log('🔍 Permissions del user:', props.user?.permissions);
const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    role: props.user?.role || 'instructor',
    permission_ids: props.user?.permissions?.map((p) => p.id) || [],
});
console.log('📦 Form permission_ids inicial:', form.permission_ids);

provide('userForm', form);

const submit = () => {
    if (props.user) {
        // Editar
        form.put(`/admin/users/${props.user.id}`);
    } else {
        // Crear
        form.post('/admin/users');
    }
};

const cancel = () => {
    router.visit(admin.users.index());
};
</script>
