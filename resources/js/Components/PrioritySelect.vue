// PrioritySelect.vue
<script setup lang="ts">
import type { Priority } from '@/types';
import {
    Flag,
    AlertCircle,
    ArrowUp,
    ArrowDown,
    Minus,
} from 'lucide-vue-next';
import Select from './ui/select/Select.vue';
import SelectContent from './ui/select/SelectContent.vue';
import SelectGroup from './ui/select/SelectGroup.vue';
import SelectItem from './ui/select/SelectItem.vue';
import SelectTrigger from './ui/select/SelectTrigger.vue';
import SelectValue from './ui/select/SelectValue.vue';

interface Props {
    priorities: Priority[];
}

const props = defineProps<Props>();
const model = defineModel<string>();

const icons = {
    'Urgent': AlertCircle,
    'High': ArrowUp,
    'Medium': Flag,
    'Low': ArrowDown,
};
</script>

<template>
    <Select v-model="model">
        <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Select a Priority" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectItem
                    :key="priority.id"
                    v-for="priority in props.priorities"
                    :value="priority.id.toString()"
                >
                    <span class="flex items-center gap-2">
                        <component :is="icons[priority.name]" class="h-4 w-4" />
                        {{ priority.name }}
                    </span>
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>