<script setup lang="ts">
import MultiPrioritySelector from '@/components/MultiPrioritySelector.vue';
import MultiStatusSelector from '@/components/MultiStatusSelector.vue';
import MultiTaskTypeSelector from '@/components/MultiTaskTypeSelector.vue';
import MultiUserSelector from '@/components/MultiUserSelector.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { PaginatedData, Priority, Project, SortDirection, Status, SubTask, Task, User, type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { ArrowDown, ArrowUp, ArrowUpDown, ChevronDown, Filter, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
    perPage: number;
    showOverdue: boolean;
    sortBy: string;
    sortDirection: SortDirection;
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
    perPage: 10,
    showOverdue: false,
    sortBy: 'updated_at',
    sortDirection: 'desc',
});
const showFilters = ref(false);

const fetchTasks = () => {
    router.get(
        route('dashboard'),
        {
            search: filter.value.search,
            page: filter.value.page,
        },
        { preserveState: true, replace: true },
    );
};

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const sortOptions = [
    { label: 'Priority', value: 'priority' },
    { label: 'Status', value: 'status' },
    { label: 'Updated At', value: 'updated_at' },
    { label: 'Due Date', value: 'due_date' },
];

const selectOption = (optionValue: string, dir: SortDirection) => {
    filter.value.sortBy = optionValue;
    filter.value.sortDirection = dir;
};

// const currentSortLabel = computed(() => {
//     return sortOptions.find((option) => option.value === filter.value.sortBy)?.label || 'Sort By';
// });

const isSelected = (optionValue: string, dir: SortDirection) => {
    return filter.value.sortBy === optionValue && filter.value.sortDirection === dir;
};

const hasActiveFilters = computed(() => {
    return (
        filter.value.supervisorIds.length > 0 ||
        filter.value.assigneeIds.length > 0 ||
        filter.value.taskType.length > 0 ||
        filter.value.statusIds.length > 0 ||
        filter.value.priorityIds.length > 0
    );
});

const changeSearch = debounce(() => {
    fetchTasks();
}, 500);

const clearFilters = () => {
    filter.value.supervisorIds = [];
    filter.value.assigneeIds = [];
    filter.value.viewerIds = [];
    filter.value.creatorIds = [];
    filter.value.taskType = [];
    filter.value.statusIds = [];
    filter.value.priorityIds = [];
    fetchTasks();
};

const filtersLength = () => {
    return (
        filter.value.supervisorIds.length +
        filter.value.assigneeIds.length +
        filter.value.taskType.length +
        filter.value.statusIds.length +
        filter.value.priorityIds.length
    );
};
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- TODO: shift the layout code to app layout -->
        <div class="container mx-auto space-y-6 px-4 py-6">
            <!-- search, filters and sorting -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <!-- search input -->
                <div class="relative max-w-md flex-1">
                    <Input v-model="filter.search" placeholder="Search tasks..." class="pl-9" @update:model-value="changeSearch" />
                    <span class="absolute inset-y-0 start-0 flex items-center justify-center px-3">
                        <Search class="size-4 text-muted-foreground" />
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- filters button -->
                    <Button variant="outline" size="sm" class="flex items-center gap-2" @click="toggleFilters">
                        <Filter class="h-4 w-4" />
                        Filters
                        <template v-if="filtersLength() > 0">
                            <Badge>
                                {{ filtersLength() }}
                            </Badge>
                        </template>
                    </Button>

                    <!-- sorting dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="flex items-center gap-2">
                                <ArrowUpDown class="h-4 w-4" />
                                <span>{{ filter.sortBy }}</span>
                                <ChevronDown class="ml-1 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent class="w-48">
                            <DropdownMenuLabel>Sort Options</DropdownMenuLabel>
                            <DropdownMenuSeparator />

                            <DropdownMenuSub v-for="option in sortOptions" :key="option.value">
                                <DropdownMenuSubTrigger :class="{ 'bg-accent': filter.sortBy === option.value }">
                                    <span>{{ option.label }}</span>
                                </DropdownMenuSubTrigger>
                                <DropdownMenuSubContent>
                                    <DropdownMenuItem
                                        @click="selectOption(option.value, 'asc')"
                                        :class="{ 'bg-accent': isSelected(option.value, 'asc') }"
                                    >
                                        <ArrowUp class="mr-2 h-4 w-4" />
                                        <span>Ascending</span>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="selectOption(option.value, 'desc')"
                                        :class="{ 'bg-accent': isSelected(option.value, 'desc') }"
                                    >
                                        <ArrowDown class="mr-2 h-4 w-4" />
                                        <span>Descending</span>
                                    </DropdownMenuItem>
                                </DropdownMenuSubContent>
                            </DropdownMenuSub>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <!-- filters panel -->
            <Collapsible v-model:open="showFilters">
                <CollapsibleContent>
                    <div class="rounded-lg border bg-muted/40 p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium">Filters</h3>
                            <div class="flex items-center gap-2">
                                <Button
                                    v-if="hasActiveFilters"
                                    variant="ghost"
                                    size="sm"
                                    class="flex items-center gap-1 text-muted-foreground"
                                    @click="clearFilters"
                                >
                                    <X class="h-3 w-3" />
                                    Clear all
                                </Button>
                                <CollapsibleTrigger as-child>
                                    <Button variant="ghost" size="sm" class="text-muted-foreground">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </CollapsibleTrigger>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap gap-4">
                            <MultiTaskTypeSelector v-model="filter.taskType" />
                            <MultiStatusSelector v-model="filter.statusIds" :statuses="statuses" />
                            <MultiPrioritySelector v-model="filter.priorityIds" :priorities="priorities" />
                            <MultiUserSelector v-model="filter.supervisorIds" :users="supervisorsAndAdmins" placeholder="Select Supervisors" />
                            <MultiUserSelector v-model="filter.assigneeIds" :users="users" placeholder="Select Assignees" />
                        </div>
                        <div class="mb-4 flex items-center space-x-2">
                            <Switch id="overdue-switch" v-model="filter.showOverdue" />
                            <Label for="overdue-switch">Filter to overdue tasks only</Label>
                        </div>
                        <div>
                            <Button @click="fetchTasks">Apply Filters</Button>
                        </div>
                    </div>
                </CollapsibleContent>
            </Collapsible>

            <!-- Tasks table -->
            <div class="rounded-md border">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Task</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Slug</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Priority</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in tasks.data" :key="task.slug" class="border-b hover:bg-muted/20">
                                <td class="px-4 py-3 font-medium">{{ task.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ task.slug }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']">
                                        {{ task.priority?.name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground"
                                    >
                                        {{ task.status?.name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ task.due_date || 'Not set' }}
                                </td>
                            </tr>
                            <tr v-if="tasks.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No tasks found. Try adjusting your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
