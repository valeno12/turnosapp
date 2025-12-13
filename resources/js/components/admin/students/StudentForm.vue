<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import admin from '@/routes/admin';
import { Student } from '@/types/model';
import { router, useForm } from '@inertiajs/vue3';
import { provide } from 'vue';
import StudentFormFields from './StudentFormFields.vue';

interface Props {
    student?: Student;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.student?.name || '',
    email: props.student?.email || '',
    phone: props.student?.phone || '',
    birth_date: props.student?.birth_date || '',
    health_notes: props.student?.health_notes || '',
    emergency_contact: props.student?.emergency_contact || '',
    emergency_phone: props.student?.emergency_phone || '',
    is_active: props.student?.is_active ?? true,
});

provide('studentForm', form);

const submit = () => {
    if (props.student) {
        // Editar
        form.put(`/admin/students/${props.student.id}`);
    } else {
        // Crear
        form.post('/admin/students');
    }
};

const cancel = () => {
    router.visit(admin.students.index());
};
</script>

<template>
    <form @submit.prevent="submit">
        <Card>
            <CardContent class="space-y-6 pt-6">
                <StudentFormFields :is-editing="!!student" />

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
                        {{ student ? 'Actualizar' : 'Crear' }} Alumno
                    </Button>
                </div>
            </CardContent>
        </Card>
    </form>
</template>
