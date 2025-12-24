<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Calendar, Folder, LayoutGrid, Settings, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Horarios',
        href: admin.schedules.index(),
        icon: Calendar,
        permission: 'access_schedules',
    },
    {
        title: 'Alumnos',
        href: admin.students.index(),
        icon: Users,
        permission: 'access_students',
    },
    {
        title: 'Planes de Suscripción',
        href: admin.plans.index(),
        icon: Folder,
        permission: 'access_plans',
    },
    {
        title: 'Servicios',
        href: admin.services.index(),
        icon: Folder,
        permission: 'access_services',
    },
    {
        title: 'Recursos',
        href: admin.resources.index(),
        icon: Folder,
        permission: 'access_resources',
    },
    {
        title: 'Usuarios',
        href: admin.users.index(),
        icon: Users,
        permission: 'access_users',
    },
    {
        title: 'Configuración de la página',
        href: admin.settings.edit(),
        icon: Settings,
        permission: 'access_settings',
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>
    </Sidebar>
    <slot />
</template>
