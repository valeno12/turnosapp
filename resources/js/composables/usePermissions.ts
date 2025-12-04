// resources/js/composables/usePermissions.ts
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const user = computed(() => page.props.auth.user);
    const permissions = computed(() => user.value?.permissions || []);
    const isAdmin = computed(() => user.value?.role === 'admin');

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    function can(permission: string): boolean {
        if (isAdmin.value) return true;
        return (
            permissions.value.includes(permission) ||
            permissions.value.includes('*')
        );
    }

    /**
     * Verifica si el usuario tiene al menos uno de los permisos
     */
    function canAny(...permissionList: string[]): boolean {
        if (isAdmin.value) return true;
        return permissionList.some((permission) =>
            permissions.value.includes(permission),
        );
    }

    /**
     * Verifica si el usuario tiene todos los permisos
     */
    function canAll(...permissionList: string[]): boolean {
        if (isAdmin.value) return true;
        return permissionList.every((permission) =>
            permissions.value.includes(permission),
        );
    }

    return {
        user,
        permissions,
        isAdmin,
        can,
        canAny,
        canAll,
    };
}
