<script setup lang="ts">
import type { Priority } from '@/types';
import Select from './ui/select/Select.vue';
import SelectContent from './ui/select/SelectContent.vue';
import SelectGroup from './ui/select/SelectGroup.vue';
import SelectItem from './ui/select/SelectItem.vue';
import SelectTrigger from './ui/select/SelectTrigger.vue';
import SelectValue from './ui/select/SelectValue.vue';
import { priorityIcons } from '@/lib/PriorityIcons';
import { computed } from 'vue';

interface Props {
    priorities: Priority[];
}

const props = defineProps<Props>();
const modelValue = defineModel<number | null>();

const selectValue = computed({
    get: () => modelValue.value?.toString() ?? null,
    set: (value: string) => {
        modelValue.value = value === null ? null : parseInt(value, 10);
    }
});
</script>

<template>
    <Select v-model="selectValue">
        <SelectTrigger>
            <SelectValue placeholder="Select a Priority" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectItem
                    v-for="priority in props.priorities"
                    :key="priority.id"
                    :value="priority.id.toString()"
                >
                    <span class="flex items-center gap-2">
                        <component 
                            :is="priorityIcons[priority.name]" 
                            class="h-4 w-4" 
                        />
                        {{ priority.name }}
                    </span>
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>