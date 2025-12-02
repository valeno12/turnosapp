<template>
    <Head title="Crear Servicio" />

    <AdminLayout title="Nuevo Servicio">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-4xl">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight">
                        Nuevo Servicio
                    </h2>
                    <p class="text-muted-foreground">
                        Completa el formulario para agregar un nuevo tipo de
                        clase
                    </p>
                </div>

                <ServiceForm />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ServiceForm from '@/components/admin/services/ServiceForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>
