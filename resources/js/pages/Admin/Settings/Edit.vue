<template>
    <Head title="Configuración" />

    <AdminLayout title="Configuración del Estudio">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="mx-auto w-full max-w-7xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Info del Estudio -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                >
                                    <Building2 class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <CardTitle
                                        >Información del Estudio</CardTitle
                                    >
                                    <CardDescription
                                        >Configurado por el
                                        administrador</CardDescription
                                    >
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="grid gap-6 md:grid-cols-2">
                            <FormField id="name" label="Nombre del Estudio">
                                <Input
                                    id="name"
                                    :model-value="tenant.name"
                                    disabled
                                    class="bg-muted/50"
                                />
                            </FormField>

                            <FormField id="subdomain" label="Subdominio">
                                <Input
                                    id="subdomain"
                                    :model-value="`${tenant.subdomain}.turnosapp.com`"
                                    disabled
                                    class="bg-muted/50"
                                />
                            </FormField>
                        </CardContent>
                    </Card>

                    <!-- Grid 2 columnas -->
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Identidad Visual -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/10"
                                    >
                                        <Palette
                                            class="h-5 w-5 text-purple-600"
                                        />
                                    </div>
                                    <div>
                                        <CardTitle>Identidad Visual</CardTitle>
                                        <CardDescription
                                            >Logo y colores de tu
                                            marca</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-8">
                                <!-- Logo -->
                                <div class="space-y-3">
                                    <Label class="text-base font-medium"
                                        >Logo del Estudio</Label
                                    >
                                    <LogoUploader
                                        :current-logo="tenant.logo_url"
                                        :error="form.errors.logo"
                                        @update="handleLogoUpdate"
                                    />
                                </div>

                                <Separator />

                                <!-- Color Principal -->
                                <FormField
                                    id="primary_color"
                                    label="Color Principal"
                                    hint="Este color se usará en toda la aplicación"
                                    :error="form.errors.primary_color"
                                >
                                    <div class="flex items-center gap-3">
                                        <Input
                                            id="primary_color"
                                            v-model="form.primary_color"
                                            type="color"
                                            class="h-12 w-20 cursor-pointer"
                                            :disabled="form.processing"
                                        />
                                        <Input
                                            v-model="form.primary_color"
                                            type="text"
                                            placeholder="#6366f1"
                                            class="font-mono"
                                            :disabled="form.processing"
                                        />
                                    </div>
                                </FormField>
                            </CardContent>
                        </Card>

                        <!-- Políticas -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10"
                                    >
                                        <Settings
                                            class="h-5 w-5 text-blue-600"
                                        />
                                    </div>
                                    <div>
                                        <CardTitle>Políticas</CardTitle>
                                        <CardDescription
                                            >Reglas de cancelación y cambios de
                                            horario</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Cancelación -->
                                <FormField
                                    id="cancellation_hours"
                                    label="Cancelación de Clases"
                                    hint="Con cuántas horas de anticipación pueden cancelar"
                                    :error="form.errors.cancellation_hours"
                                >
                                    <div class="flex items-center gap-3">
                                        <Input
                                            id="cancellation_hours"
                                            v-model.number="
                                                form.cancellation_hours
                                            "
                                            type="number"
                                            min="0"
                                            max="168"
                                            :disabled="form.processing"
                                            class="w-32 text-center text-lg font-semibold"
                                        />
                                        <span
                                            class="text-sm font-medium text-muted-foreground"
                                            >horas antes</span
                                        >
                                    </div>
                                </FormField>

                                <Separator />

                                <!-- Cambio de Horario -->
                                <div class="space-y-4">
                                    <Label class="text-base font-medium"
                                        >Cambios de Horario Mensual</Label
                                    >
                                    <RadioGroup
                                        v-model="form.schedule_change_policy"
                                        :disabled="form.processing"
                                    >
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <RadioGroupItem
                                                value="anytime"
                                                id="anytime"
                                            />
                                            <Label
                                                for="anytime"
                                                class="cursor-pointer font-normal"
                                            >
                                                Cualquier momento
                                            </Label>
                                        </div>
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <RadioGroupItem
                                                value="end_of_month"
                                                id="end_of_month"
                                            />
                                            <Label
                                                for="end_of_month"
                                                class="cursor-pointer font-normal"
                                            >
                                                Solo fin de mes
                                            </Label>
                                        </div>
                                    </RadioGroup>
                                    <p
                                        v-if="
                                            form.errors.schedule_change_policy
                                        "
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.schedule_change_policy }}
                                    </p>
                                </div>

                                <!-- Días de anticipación (solo si es end_of_month) -->
                                <FormField
                                    v-if="
                                        form.schedule_change_policy ===
                                        'end_of_month'
                                    "
                                    id="schedule_change_cutoff_days"
                                    label="Días de Anticipación"
                                    hint="Con cuántos días de anticipación deben avisar"
                                    :error="
                                        form.errors.schedule_change_cutoff_days
                                    "
                                >
                                    <div class="flex items-center gap-3">
                                        <Input
                                            id="schedule_change_cutoff_days"
                                            v-model.number="
                                                form.schedule_change_cutoff_days
                                            "
                                            type="number"
                                            min="1"
                                            max="30"
                                            :disabled="form.processing"
                                            class="w-32 text-center text-lg font-semibold"
                                        />
                                        <span
                                            class="text-sm font-medium text-muted-foreground"
                                            >días antes del fin de mes</span
                                        >
                                    </div>
                                </FormField>
                            </CardContent>
                        </Card>

                        <!-- Pricing Rules -->
                        <Card class="lg:col-span-2">
                            <CardHeader>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/10"
                                    >
                                        <DollarSign
                                            class="h-5 w-5 text-green-600"
                                        />
                                    </div>
                                    <div>
                                        <CardTitle>Recargos por Mora</CardTitle>
                                        <CardDescription
                                            >Penalización por pago tardío de
                                            cuotas mensuales</CardDescription
                                        >
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <PricingRules
                                    v-model="form.pricing_rules"
                                    :disabled="form.processing"
                                />
                                <p
                                    v-if="form.errors.pricing_rules"
                                    class="mt-2 text-sm text-destructive"
                                >
                                    {{ form.errors.pricing_rules }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Botones -->
                    <div
                        class="flex items-center justify-end gap-4 rounded-lg border bg-card p-6"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            size="lg"
                            @click="cancel"
                            :disabled="form.processing"
                        >
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            size="lg"
                            :disabled="form.processing"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            Guardar Configuración
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import LogoUploader from '@/components/admin/settings/LogoUploader.vue';
import PricingRules from '@/components/admin/settings/PricingRules.vue';
import FormField from '@/components/shared/FormField.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { dashboard } from '@/routes';
import { Tenant } from '@/types/model';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Building2,
    DollarSign,
    Palette,
    Save,
    Settings,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface Props {
    tenant: Tenant;
}

const props = defineProps<Props>();

const form = useForm({
    logo: null as File | null,
    primary_color: props.tenant.primary_color || '#6366f1',
    cancellation_hours: props.tenant.cancellation_hours || 24,
    schedule_change_policy:
        props.tenant.schedule_change_policy || 'end_of_month',
    schedule_change_cutoff_days: props.tenant.schedule_change_cutoff_days || 7,
    pricing_rules: props.tenant.pricing_rules || [
        { day_start: 1, day_end: 10, multiplier: 1.0 },
        { day_start: 11, day_end: 20, multiplier: 1.05 },
        { day_start: 21, day_end: 31, multiplier: 1.1 },
    ],
});

const handleLogoUpdate = (file: File | null) => {
    form.logo = file;
};

const submit = () => {
    form.post('/admin/settings', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('¡Configuración guardada!', {
                description: 'Los cambios se han aplicado exitosamente.',
            });
        },
        onError: () => {
            toast.error('Error al guardar', {
                description: 'Por favor, revisa los campos marcados en rojo.',
            });
        },
    });
};

const cancel = () => {
    router.visit(dashboard());
};
</script>
