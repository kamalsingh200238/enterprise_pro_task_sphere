<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';

const page = usePage();
const flash = computed(() => page.props.flash);
watch(flash, (newValue) => {
    if (!newValue) {
        return;
    }

    switch (newValue.variant) {
        case 'success':
            toast.success(newValue.heading, {
                description: newValue.description,
                duration: newValue.duraton,
                closeButton: true,
            });
            break;
        case 'danger':
            toast.error(newValue.heading, {
                description: newValue.description,
                duration: newValue.duraton,
                closeButton: true,
            });
            break;
    }
});
</script>

<template>
    <div>
        <slot />
    </div>
</template>
