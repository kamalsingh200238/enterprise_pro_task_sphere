<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps({
    err: String,
    class: String,
});
</script>

<template>
    <div v-if="props.err" :class="cn('mt-1 text-xs text-red-500', props.class)">
        {{ props.err }}
    </div>
</template>
