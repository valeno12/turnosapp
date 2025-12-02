<template>
    <FormCard
        card-title="Información del Servicio"
        card-description="Tipo de clase o actividad que se ofrece"
        entity-name="Servicio"
        :is-submitting="form.processing"
        :is-dirty="form.isDirty"
        @submit="submit"
        @cancel="handleCancel"
    >
        <ServiceFormFields :form="form" />
    </FormCard>
</template>

<script setup lang="ts">
import FormCard from '@/components/shared/FormCard.vue';
import { Service } from '@/types/model';
import { router, useForm } from '@inertiajs/vue3';
import { provide } from 'vue';
import { toast } from 'vue-sonner';
import ServiceFormFields from './ServiceFormFields.vue';

interface Props {
    service?: Service;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.service?.name || '',
    duration_minutes: props.service?.duration_minutes || 60,
    color: props.service?.color || '#3b82f6',
    description: props.service?.description || '',
});

provide('serviceForm', form);

const submit = () => {
    const isEdit = !!props.service;
    const url = isEdit
        ? `/admin/services/${props.service!.id}`
        : '/admin/services';

    const options = {
        onSuccess: () => {
            toast.success(
                isEdit ? '¡Servicio actualizado!' : '¡Servicio creado!',
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
    router.visit('/admin/services');
};
</script>
