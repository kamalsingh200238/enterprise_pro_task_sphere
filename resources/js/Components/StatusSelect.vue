<script setup lang="ts">
import { statusIcons } from '@/lib/StatusIcons';
import type { Status } from '@/types';
import Select from './ui/select/Select.vue';
import SelectContent from './ui/select/SelectContent.vue';
import SelectGroup from './ui/select/SelectGroup.vue';
import SelectItem from './ui/select/SelectItem.vue';
import SelectTrigger from './ui/select/SelectTrigger.vue';
import SelectValue from './ui/select/SelectValue.vue';
import { computed } from 'vue';

interface Props {
    statuses: Status[];
}

const props = defineProps<Props>();
const modelValue = defineModel<number | null>();

const selectValue = computed({
    get: () => modelValue.value?.toString() ?? '',
    set: (value: string) => {
        modelValue.value = value === '' ? null : parseInt(value, 10);
    }
});
</script>

<template>
    <Select v-model="selectValue">
        <SelectTrigger>
            <SelectValue placeholder="Select a Status" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectItem
                    v-for="status in props.statuses"
                    :key="status.id"
                    :value="status.id.toString()"
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