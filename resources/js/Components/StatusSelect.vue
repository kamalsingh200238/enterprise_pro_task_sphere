<script setup lang="ts">
import type { Status } from '@/types';
import {
    CheckSquare,
    GitPullRequest,
    ListTodo,
    Pause,
    Timer,
} from 'lucide-vue-next';
import Select from './ui/select/Select.vue';
import SelectContent from './ui/select/SelectContent.vue';
import SelectGroup from './ui/select/SelectGroup.vue';
import SelectItem from './ui/select/SelectItem.vue';
import SelectTrigger from './ui/select/SelectTrigger.vue';
import SelectValue from './ui/select/SelectValue.vue';

interface Props {
    statuses: Status[];
}

const props = defineProps<Props>();
const model = defineModel<string>();

const icons = {
    Backlog: ListTodo,
    'In Progress': Timer,
    'On Hold': Pause,
    'In Review': GitPullRequest,
    Done: CheckSquare,
};
</script>

<template>
    <Select v-model="model">
        <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Select a Status" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectItem
                    :key="status.id"
                    v-for="status in props.statuses"
                    :value="status.id.toString()"
                >
                    <span class="flex items-center gap-2">
                        <component :is="icons[status.name]" class="h-4 w-4" />
                        {{ status.name }}
                    </span>
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
