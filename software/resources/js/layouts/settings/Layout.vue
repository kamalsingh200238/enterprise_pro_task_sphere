<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Appearance',
        href: '/settings/appearance',
        isActive: true,
    },
    {
        title: 'Profile',
        href: '/settings/profile',
        isActive: true,
    },
];

if (!user.value.uses_oauth) {
    sidebarNavItems.push({
        title: 'Password',
        href: '/settings/password',
        isActive: true,
    });
}

if (user.value.role === 'admin') {
    sidebarNavItems.push({
        title: 'OAuth Settings',
        href: '/settings/oauth',
        isActive: true,
    });
}
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-x-0 space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                        :disabled="!item.isActive"
                    >
                        <Link as="button" :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
