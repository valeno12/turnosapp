<template>
    <FormCard
        card-title="Información del Recurso"
        card-description="Datos del espacio físico o equipamiento"
        entity-name="Recurso"
        :is-submitting="form.processing"
        :is-dirty="form.isDirty"
        @submit="submit"
        @cancel="handleCancel"
    >
        <ResourceFormFields :form="form" />
    </FormCard>
</template>

<script setup lang="ts">
import FormCard from '@/components/shared/FormCard.vue';
import { Resource } from '@/types/model';
import { router, useForm } from '@inertiajs/vue3';
import { provide } from 'vue';
import { toast } from 'vue-sonner';
import ResourceFormFields from './ResourceFormFields.vue';

interface Props {
    resource?: Resource;
}

const props = defineProps<Props>();

// Inertia useForm - maneja todo automáticamente
const form = useForm({
    name: props.resource?.name || '',
    capacity: props.resource?.capacity || null,
    is_self_assignable: props.resource?.is_self_assignable ?? true,
    notes: props.resource?.notes || '',
});

provide('resourceForm', form);

const submit = () => {
    const isEdit = !!props.resource;
    const url = isEdit
        ? `/admin/resources/${props.resource!.id}`
        : '/admin/resources';

    const options = {
        onSuccess: () => {
            toast.success(
                isEdit ? '¡Recurso actualizado!' : '¡Recurso creado!',
                {
                    description: `${form.name} ha sido ${isEdit ? 'actualizado' : 'creado'} exitosamente.`,
                },
            );
        },
        onError: () => {
            toast.error('Error en el formulario', {
                description: 'Por favor, revisa los campos marcados en rojo.',
            });
        },
    };

    if (isEdit) {
        form.put(url, options);
    } else {
        form.post(url, options);
    }
};

const handleCancel = () => {
    router.visit('/admin/resources');
};
</script>
