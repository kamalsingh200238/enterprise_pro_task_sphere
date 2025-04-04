<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// composable to show all toasts
useToast();
</script>

<template>
    <Toaster rich-colors position="top-right" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
