<script setup lang="ts">
import { Toaster } from '@/Components/ui/sonner';
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, watch } from 'vue';
import { toast } from 'vue-sonner';

const page = usePage();
const flash = computed(() => page.props.flash);
const createdProject = computed(() => page.props.createdProject);

watch(
    flash,
    (newValue) => {
        if (!newValue) {
            return;
        }

        switch (newValue.variant) {
            case 'success':
                nextTick(() => {
                    toast.success(newValue.heading, {
                        description: newValue.description,
                        duration: newValue.duraton,
                        closeButton: true,
                    });
                });
                break;
            case 'danger':
                nextTick(() => {
                    toast.error(newValue.heading, {
                        description: newValue.description,
                        duration: newValue.duraton,
                        closeButton: true,
                    });
                });
                break;
        }
    },
    { immediate: true },
);

watch(
    createdProject,
    (newValue) => {
        if (!newValue) {
            return;
        }
        nextTick(() => {
            toast.success('Created project successfully', {
                duration: 10000,
                closeButton: true,
            });
        });
    },
    { immediate: true },
);
</script>

<template>
    <div>
        <Toaster rich-colors />
        <slot />
    </div>
</template>
