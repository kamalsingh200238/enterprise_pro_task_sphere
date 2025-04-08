<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import { useToast } from '@/composables/useToast';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
useToast();
</script>

<template>
    <Toaster rich-colors position="top-right" />
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
