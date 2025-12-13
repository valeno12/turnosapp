<script setup lang="ts">
import StudentForm from '@/components/admin/students/StudentForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Student } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    student: Student;
}

defineProps<Props>();
</script>

<template>
    <Head :title="`Editar ${student.name}`" />

    <AdminLayout title="Editar Alumno">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Editar Alumno
                    </h2>
                    <p class="text-muted-foreground">
                        Modifica los datos del alumno
                    </p>
                </div>

                <StudentForm :student="student" />
            </div>
        </div>
    </AdminLayout>
</template>
