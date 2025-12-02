<template>
    <FormCard
        card-title="Información del Plan"
        card-description="Configuración de plan de suscripción mensual"
        entity-name="Plan"
        :is-submitting="form.processing"
        :is-dirty="form.isDirty"
        @submit="submit"
        @cancel="handleCancel"
    >
        <SubscriptionPlanFormFields
            :form="form"
            :available-services="availableServices"
        />
    </FormCard>
</template>

<script setup lang="ts">
import FormCard from '@/components/shared/FormCard.vue';
import { Service, SubscriptionPlan } from '@/types/model';
import { router, useForm } from '@inertiajs/vue3';
import { provide } from 'vue';
import { toast } from 'vue-sonner';
import SubscriptionPlanFormFields from './SubscriptionPlanFormFields.vue';

interface Props {
    plan?: SubscriptionPlan;
    availableServices: Service[];
}

const props = defineProps<Props>();

// Extraer IDs de servicios si estamos editando
const initialServiceIds = props.plan?.services?.map((s) => s.id) || [];

const form = useForm({
    name: props.plan?.name || '',
    classes_per_week: props.plan?.classes_per_week || 2,
    base_price: props.plan?.base_price ? parseFloat(props.plan.base_price) : 0,
    allows_makeups: props.plan?.allows_makeups ?? true,
    description: props.plan?.description || '',
    service_ids: initialServiceIds,
});

provide('subscriptionPlanForm', form);

const submit = () => {
    console.log('📤 Form data antes de enviar:', {
        name: form.name,
        classes_per_week: form.classes_per_week,
        base_price: form.base_price,
        allows_makeups: form.allows_makeups,
        description: form.description,
        service_ids: form.service_ids,
    });
    const isEdit = !!props.plan;
    const url = isEdit
        ? `/admin/subscription-plans/${props.plan!.id}`
        : '/admin/subscription-plans';

    const options = {
        onSuccess: () => {
            toast.success(isEdit ? '¡Plan actualizado!' : '¡Plan creado!', {
                description: `${form.name} ha sido ${isEdit ? 'actualizado' : 'creado'} exitosamente.`,
            });
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
    router.visit('/admin/subscription-plans');
};
</script>
