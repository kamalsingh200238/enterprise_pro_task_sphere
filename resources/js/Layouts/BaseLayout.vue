<script setup lang="ts">
import { Toaster } from '@/Components/ui/sonner';
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, watch } from 'vue';
import { toast } from 'vue-sonner';

const page = usePage();
const flash = computed(() => page.props.flash);
const created = computed(() => page.props.created);
const deleted = computed(() => page.props.deleted);

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
    created,
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

watch(
    deleted,
    (newValue) => {
        if (!newValue) {
            return;
        }
        nextTick(() => {
            toast.error('Deleted project successfully', {
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
