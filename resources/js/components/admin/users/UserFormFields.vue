<template>
    <div style="display: none">
        <input type="email" name="fake-email" />
        <input type="password" name="fake-password" />
    </div>
    <!-- Nombre -->
    <FormField
        id="name"
        label="Nombre Completo"
        :error="form.errors.name"
        required
    >
        <Input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Juan Pérez"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.name }"
        />
    </FormField>

    <!-- Email -->
    <FormField id="email" label="Email" :error="form.errors.email" required>
        <Input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="juan@example.com"
            autocomplete="off"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.email }"
        />
    </FormField>

    <!-- Password -->
    <FormField
        id="password"
        :label="isEditing ? 'Nueva Contraseña' : 'Contraseña'"
        :error="form.errors.password"
        :required="!isEditing"
        autocomplete="off"
        :hint="isEditing ? 'Dejar vacío para mantener la actual' : undefined"
    >
        <Input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            :disabled="form.processing"
            :class="{ 'border-destructive': form.errors.password }"
        />
    </FormField>

    <!-- Rol -->
    <FormField id="role" label="Rol" :error="form.errors.role" required>
        <RadioGroup v-model="form.role" :disabled="form.processing">
            <div class="flex items-center space-x-2">
                <RadioGroupItem id="instructor" value="instructor" />
                <Label for="instructor" class="cursor-pointer font-normal">
                    Instructor
                </Label>
            </div>
            <div class="flex items-center space-x-2">
                <RadioGroupItem id="admin" value="admin" />
                <Label for="admin" class="cursor-pointer font-normal">
                    Administrador (acceso total)
                </Label>
            </div>
        </RadioGroup>
    </FormField>

    <!-- Permisos (solo si es instructor) -->
    <div v-if="form.role === 'instructor'">
        <Separator class="my-6" />

        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-medium">Permisos</h3>
                <p class="text-sm text-muted-foreground">
                    Selecciona qué acciones puede realizar este instructor
                </p>
            </div>

            <div
                v-for="(permissions, group) in permissionsByGroup"
                :key="group"
                class="space-y-3"
            >
                <h4 class="text-sm font-medium">
                    {{ groupLabels[group] || group }}
                </h4>

                <div class="space-y-2 pl-4">
                    <div
                        v-for="permission in permissions"
                        :key="permission.id"
                        class="flex items-center space-x-2"
                    >
                        <Checkbox
                            :id="`permission-${permission.id}`"
                            :checked="
                                form.permission_ids.includes(permission.id)
                            "
                            @update:checked="
                                togglePermission(permission.id, $event)
                            "
                            :disabled="form.processing"
                        />
                        <Label
                            :for="`permission-${permission.id}`"
                            class="cursor-pointer text-sm font-normal"
                        >
                            {{ permission.description }}
                        </Label>
                    </div>
                </div>
            </div>

            <p
                v-if="form.errors.permission_ids"
                class="text-sm text-destructive"
            >
                {{ form.errors.permission_ids }}
            </p>
        </div>
    </div>
</template>
<script setup lang="ts">
import FormField from '@/components/shared/FormField.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Separator } from '@/components/ui/separator';
import { Permission } from '@/types/model';
import { inject } from 'vue';

interface Props {
    permissionsByGroup: Record<string, Permission[]>;
    isEditing?: boolean;
}

defineProps<Props>();

const form = inject<any>('userForm');

const togglePermission = (permissionId: number, checked: boolean | string) => {
    const isChecked = checked === true || checked === 'true';

    if (isChecked) {
        if (!form.permission_ids.includes(permissionId)) {
            form.permission_ids.push(permissionId);
        }
    } else {
        const index = form.permission_ids.indexOf(permissionId);
        if (index > -1) {
            form.permission_ids.splice(index, 1);
        }
    }
};

const groupLabels: Record<string, string> = {
    resources: 'Recursos',
    services: 'Servicios',
    plans: 'Planes de Suscripción',
    users: 'Usuarios',
    schedule: 'Horarios',
    bookings: 'Reservas',
    reports: 'Reportes',
    settings: 'Configuración',
};
</script>
