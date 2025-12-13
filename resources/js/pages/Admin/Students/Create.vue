<script setup lang="ts">
import StudentForm from '@/components/admin/students/StudentForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Crear Alumno" />

    <AdminLayout title="Nuevo Alumno">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Nuevo Alumno
                    </h2>
                    <p class="text-muted-foreground">
                        Completa el formulario para agregar un nuevo alumno
                    </p>
                </div>

                <StudentForm />
            </div>
        </div>
    </AdminLayout>
</template>
