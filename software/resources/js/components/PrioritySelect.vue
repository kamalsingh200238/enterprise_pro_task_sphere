<script setup lang="ts">
import { priorityIcons } from '@/lib/priorityIcons';
import type { Priority } from '@/types';
import { computed } from 'vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from './ui/select';

interface Props {
    priorities: Priority[];
    disabled: boolean;
}

const props = defineProps<Props>();
const modelValue = defineModel<number | null>();

const selectValue = computed({
    get: () => modelValue.value?.toString() ?? null,
    set: (value: string) => {
        modelValue.value = value === null ? null : parseInt(value, 10);
    },
});
</script>

<template>
    <Select v-model="selectValue" :disabled="props.disabled">
        <SelectTrigger>
            <SelectValue placeholder="Select a Priority" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel>Priorities</SelectLabel>
                <SelectItem v-for="priority in props.priorities" :key="priority.id" :value="priority.id.toString()">
                    <span class="flex items-center gap-2">
                        <component :is="priorityIcons[priority.name]" class="h-4 w-4" />
                        {{ priority.name }}
                    </span>
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
