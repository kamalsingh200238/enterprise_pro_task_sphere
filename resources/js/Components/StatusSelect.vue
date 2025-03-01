<script setup lang="ts">
import { statusIcons } from '@/lib/StatusIcons';
import type { Status } from '@/types';
import { computed } from 'vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from './ui/select';

interface Props {
    disabled: boolean;
    statuses: Status[];
    updateStatusToDone: boolean;
}

const props = defineProps<Props>();
const modelValue = defineModel<number | null>();

const selectValue = computed({
    get: () => modelValue.value?.toString() ?? null,
    set: (value: string) => {
        modelValue.value = value === '' ? null : parseInt(value, 10);
    },
});
</script>

<template>
    <Select v-model="selectValue" :disabled="props.disabled">
        <SelectTrigger>
            <SelectValue placeholder="Select a Status" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel>Statuses</SelectLabel>
                <SelectItem
                    v-for="status in props.statuses"
                    :key="status.id"
                    :value="status.id.toString()"
                    :disabled="status.name === 'Done' && !props.updateStatusToDone"
                >
                    <span class="flex items-center gap-2">
                        <component
                            :is="statusIcons[status.name]"
                            class="h-4 w-4"
                        />
                        {{ status.name }}
                    </span>
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
