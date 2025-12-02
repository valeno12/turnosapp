<template>
    <Head title="Crear Recurso" />

    <AdminLayout title="Nuevo Recurso">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Nuevo Recurso
                    </h2>
                    <p class="text-muted-foreground">
                        Completa el formulario para agregar un nuevo espacio o
                        equipamiento
                    </p>
                </div>

                <ResourceForm />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ResourceForm from '@/components/admin/resources/ResourceForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>
