<script setup lang="ts">
import { Toaster } from '@/Components/ui/sonner';
import { FlashMessage } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, watch } from 'vue';
import { toast } from 'vue-sonner';

const page = usePage();
const flash = computed(() => page.props.flash);

const normalToast = (flash: FlashMessage) => {
    switch (flash.variant) {
        case 'success':
            nextTick(() => {
                toast.success(flash.heading, {
                    description: flash.description,
                    duration: flash.duration,
                    closeButton: true,
                });
            });
            break;
        case 'danger':
            nextTick(() => {
                toast.error(flash.heading, {
                    description: flash.description,
                    duration: flash.duration,
                    closeButton: true,
                });
            });
            break;
    }
};

const createdProjectToast = (flash: FlashMessage) => {
    nextTick(() => {
        toast.success(flash.heading, {
            duration: flash.duration,
            closeButton: true,
            action: {
                label: 'Open',
                onClick: () => {
                    router.get(
                        route('projects.show', flash.context.project.id),
                    );
                },
            },
        });
    });
};

watch(
    flash,
    (newValue) => {
        if (!newValue) {
            return;
        }

        switch (newValue.messageType) {
            case 'normal':
                normalToast(newValue);
                break;
            case 'createdProject':
                createdProjectToast(newValue);
                break;
        }
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
