<script setup lang="ts">
import MultiPrioritySelector from '@/components/MultiPrioritySelector.vue';
import MultiStatusSelector from '@/components/MultiStatusSelector.vue';
import MultiTaskTypeSelector from '@/components/MultiTaskTypeSelector.vue';
import MultiUserSelector from '@/components/MultiUserSelector.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { PaginatedData, Priority, Project, Status, SubTask, Task, User, type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Define breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Props {
    tasks: PaginatedData<Project | Task | SubTask>;
    search: string | null;
    users: User[];
    supervisorsAndAdmins: User[];
    statuses: Status[];
    priorities: Priority[];
}

interface Filter {
    search: string;
    supervisorIds: number[];
    assigneeIds: number[];
    viewerIds: number[];
    creatorIds: number[];
    taskType: string[];
    statusIds: number[];
    priorityIds: number[];
    page: number;
}

const props = defineProps<Props>();

const filter = ref<Filter>({
    search: props.search ?? '',
    supervisorIds: [],
    assigneeIds: [],
    viewerIds: [],
    creatorIds: [],
    taskType: [],
    statusIds: [],
    priorityIds: [],
    page: 1,
});

const fetchTasks = () => {
    router.get(route('dashboard'), { search: filter.value.search, page: filter.value.page }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- filter and search bar -->
        <Input v-model="filter.search" placeholder="Search tasks..." />
        <MultiUserSelector v-model="filter.supervisorIds" :users="supervisorsAndAdmins" placeholder="Select Supervisors" />
        <MultiUserSelector v-model="filter.assigneeIds" :users="users" placeholder="Select Assignees" />
        <MultiUserSelector v-model="filter.viewerIds" :users="users" placeholder="Select Viewers" :disabled="true" />
        <MultiUserSelector v-model="filter.creatorIds" :users="supervisorsAndAdmins" placeholder="Select Creators" :disabled="true" />
        <MultiTaskTypeSelector v-model="filter.taskType" />
        <MultiStatusSelector v-model="filter.statusIds" :statuses="statuses" />
        <MultiPrioritySelector v-model="filter.priorityIds" :priorities="priorities" />
        <Button @click="fetchTasks">Search</Button>
        <div v-for="task in tasks.data" :key="task.slug" class="flex gap-5">
            <span> {{ task.name }} </span>
            <span> {{ task.slug }} </span>
            <span> {{ task.priority_id }} </span>
            <span> {{ task.status?.name }} </span>
            <span> {{ task.priority?.name }} </span>
        </div>
    </AppLayout>
</template>
