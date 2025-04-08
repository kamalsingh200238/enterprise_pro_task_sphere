<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { CirclePlus, Folder, FolderPlus, LayoutGrid, LayoutList, ListChecks, Logs, SquarePlus, User, UserPlus } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const user = computed(() => usePage<SharedData>().props.auth.user);

const staffNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const supervisorNavItems: NavItem[] = [
    {
        title: 'Create Project',
        href: '/projects/new',
        icon: FolderPlus,
    },
    {
        title: 'Create Task',
        href: '/tasks/new',
        icon: SquarePlus,
    },
    {
        title: 'Create Sub-Task',
        href: '/sub-tasks/new',
        icon: CirclePlus,
    },
    {
        title: 'See all projects',
        href: '/projects',
        icon: Folder,
    },
    {
        title: 'See all tasks',
        href: '/tasks',
        icon: ListChecks,
    },
    {
        title: 'See all Sub-Tasks',
        href: '/sub-tasks',
        icon: LayoutList,
    },
    {
        title: 'Logs',
        href: '/logs',
        icon: Logs,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Manage Users',
        href: '/users',
        icon: User,
    },
    {
        title: 'Create Users',
        href: '/users/new',
        icon: UserPlus,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="offcanvas" variant="sidebar">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain label="Staff" :items="staffNavItems" />
            <NavMain v-if="user.role === 'supervisor' || user.role === 'admin'" label="Supervisor" :items="supervisorNavItems" />
            <NavMain v-if="user.role === 'admin'" label="Admin" :items="adminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
