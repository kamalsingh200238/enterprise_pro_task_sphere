<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType, FlashMessage, SharedData } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<SharedData>();
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
                    router.get(route('projects.show', flash.context.project?.id));
                },
            },
        });
    });
};

const createdTaskToast = (flash: FlashMessage) => {
    nextTick(() => {
        toast.success(flash.heading, {
            duration: flash.duration,
            closeButton: true,
            action: {
                label: 'Open',
                onClick: () => {
                    router.get(route('tasks.show', flash.context.task?.id));
                },
            },
        });
    });
};

const createSubTaskToast = (flash: FlashMessage) => {
    nextTick(() => {
        toast.success(flash.heading, {
            duration: flash.duration,
            closeButton: true,
            action: {
                label: 'Open',
                onClick: () => {
                    router.get(route('sub-tasks.show', flash.context.subTask?.id));
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
            case 'createdTask':
                createdTaskToast(newValue);
                break;
            case 'createdSubTask':
                createSubTaskToast(newValue);
                break;
        }
    },
    { immediate: true },
);
</script>

<template>
    <Toaster rich-colors position="top-right" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
