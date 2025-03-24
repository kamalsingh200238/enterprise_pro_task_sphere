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
import {
    Pagination,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
} from '@/components/ui/pagination';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { getBadgeColors } from '@/lib/getBadgeColors';
import { PaginatedData, Priority, Project, SortDirection, Status, SubTask, Task, User, type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import { debounce } from 'lodash';
import { ChevronDown, Filter, MoveDown, MoveUp, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Props {
    tasks: PaginatedData<Project | Task | SubTask>;
    users: User[];
    supervisorsAndAdmins: User[];
    statuses: Status[];
    priorities: Priority[];
    defaultFilters: Filter;
}

interface Filter {
    search: string;
    taskTypes: string[];
    supervisorIds: number[];
    assigneeIds: number[];
    viewerIds: number[];
    creatorIds: number[];
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
    search: props.defaultFilters.search ?? '',
    taskTypes: props.defaultFilters.taskTypes ?? [],
    supervisorIds: props.defaultFilters.supervisorIds ?? [],
    assigneeIds: props.defaultFilters.assigneeIds ?? [],
    viewerIds: props.defaultFilters.viewerIds ?? [],
    creatorIds: props.defaultFilters.creatorIds ?? [],
    statusIds: props.defaultFilters.statusIds ?? [],
    priorityIds: props.defaultFilters.priorityIds ?? [],
    page: props.defaultFilters.page ?? 1,
    perPage: props.defaultFilters.perPage ?? 10,
    showOverdue: props.defaultFilters.showOverdue ?? false,
    sortBy: props.defaultFilters.sortBy ?? 'updated_at',
    sortDirection: props.defaultFilters.sortDirection ?? 'asc',
});

// state to show filters collapsible
const showFilters = ref(false);

// function to fetch data with filters
const fetchTasks = () => {
    router.get(
        route('dashboard'),
        {
            search: filter.value.search,
            taskTypes: filter.value.taskTypes,
            statusIds: filter.value.statusIds,
            priorityIds: filter.value.priorityIds,
            supervisorIds: filter.value.supervisorIds,
            assigneeIds: filter.value.assigneeIds,
            viewerIds: filter.value.viewerIds,
            creatorIds: filter.value.creatorIds,
            showOverdue: filter.value.showOverdue,
            sortBy: filter.value.sortBy,
            sortDirection: filter.value.sortDirection,
            page: filter.value.page,
            perPage: filter.value.perPage,
        },
        { preserveState: true, replace: true, preserveScroll: true },
    );
};

const toggleShowFilters = () => {
    showFilters.value = !showFilters.value;
};

// available sort options
const sortOptions = [
    { label: 'Priority', value: 'priority_id' },
    { label: 'Status', value: 'status_id' },
    { label: 'Updated At', value: 'updated_at' },
    { label: 'Due Date', value: 'due_date' },
];

// function to select sort by and sort direction
const selectSortOption = (optionValue: string, dir: SortDirection) => {
    filter.value.sortBy = optionValue;
    filter.value.sortDirection = dir;
    filter.value.page = 1;
    fetchTasks();
};

// function to check if a sort by and sort direction is selected
const isSortOptionSelected = (optionValue: string, dir: SortDirection) => {
    return filter.value.sortBy === optionValue && filter.value.sortDirection === dir;
};

// function to check if there are active filters
const hasActiveFilters = computed(() => {
    return (
        filter.value.taskTypes.length > 0 ||
        filter.value.statusIds.length > 0 ||
        filter.value.priorityIds.length > 0 ||
        filter.value.supervisorIds.length > 0 ||
        filter.value.assigneeIds.length > 0 ||
        filter.value.viewerIds.length > 0 ||
        filter.value.creatorIds.length > 0 ||
        filter.value.showOverdue
    );
});

// function to fetch tasks if the search query changes, debounce the function to stop hammering backend
const changeSearch = debounce(() => {
    filter.value.page = 1;
    fetchTasks();
}, 500);

const applyFilters = () => {
    filter.value.page = 1;
    fetchTasks();
};

// function to reset all filters
const clearFilters = () => {
    filter.value.supervisorIds = [];
    filter.value.assigneeIds = [];
    filter.value.viewerIds = [];
    filter.value.creatorIds = [];
    filter.value.taskTypes = [];
    filter.value.statusIds = [];
    filter.value.priorityIds = [];
    filter.value.showOverdue = false;
};

// function to return number of applied filters
const filtersLength = () => {
    const countForOverdue = filter.value.showOverdue ? 1 : 0;
    return (
        filter.value.supervisorIds.length +
        filter.value.assigneeIds.length +
        filter.value.viewerIds.length +
        filter.value.creatorIds.length +
        filter.value.taskTypes.length +
        filter.value.statusIds.length +
        filter.value.priorityIds.length +
        countForOverdue
    );
};

// state to store current sort by
const currentSortOption = computed(() => {
    const option = sortOptions.find((opt) => opt.value === filter.value.sortBy);
    return option ? option.label : 'Sort By';
});

// update page number and then fetch data
const updatePage = (newPage: number) => {
    filter.value.page = newPage;
    fetchTasks();
};

// change per page data
const changePerPage = (newPerPage: number) => {
    filter.value.perPage = newPerPage;
    // reset to first page when changing items per page
    filter.value.page = 1;
    fetchTasks();
};

const getTaskLink = (slug: string, id: number) => {
    if (slug.startsWith('PRO')) {
        return route('projects.show', id);
    } else if (slug.startsWith('TASK')) {
        return route('tasks.show', id);
    } else {
        return route('sub-tasks.show', id);
    }
};

const perPageOptions = [10, 25, 50, 100];

const readableDate = (isoDate: string) => {
    const date = parseISO(isoDate);
    return format(date, 'do MMM, yyyy');
};
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto space-y-6 px-4 py-6">
            <!-- search, filters and sorting -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <!-- search input -->
                <div class="relative max-w-sm flex-1">
                    <Input v-model="filter.search" placeholder="Search tasks..." class="pl-9" @update:model-value="changeSearch" />
                    <span class="absolute inset-y-0 start-0 flex items-center justify-center px-3">
                        <Search class="size-4 text-muted-foreground" />
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- filters button -->
                    <Button variant="outline" @click="toggleShowFilters">
                        <Filter class="h-4 w-4" />
                        Filters
                        <template v-if="filtersLength() > 0">
                            <Separator orientation="vertical" class="mx-2 h-4" />
                            <Badge>
                                {{ filtersLength() }}
                            </Badge>
                        </template>
                    </Button>

                    <!-- sorting dropdown with selected label and direction icon -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline">
                                <template v-if="filter.sortDirection === 'asc'">
                                    <MoveUp class="h-4 w-4" />
                                </template>
                                <template v-else>
                                    <MoveDown class="h-4 w-4" />
                                </template>
                                <span>{{ currentSortOption }}</span>
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuLabel>Sort Options</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuSub v-for="sortOption in sortOptions" :key="sortOption.value">
                                <DropdownMenuSubTrigger :class="{ 'bg-accent': filter.sortBy === sortOption.value }">
                                    <span>{{ sortOption.label }}</span>
                                </DropdownMenuSubTrigger>
                                <DropdownMenuSubContent>
                                    <DropdownMenuItem
                                        @click="selectSortOption(sortOption.value, 'asc')"
                                        :class="{ 'bg-accent': isSortOptionSelected(sortOption.value, 'asc') }"
                                    >
                                        <MoveUp class="mr-2 h-4 w-4" />
                                        <span>Ascending</span>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="selectSortOption(sortOption.value, 'desc')"
                                        :class="{ 'bg-accent': isSortOptionSelected(sortOption.value, 'desc') }"
                                    >
                                        <MoveDown class="mr-2 h-4 w-4" />
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
                    <div class="rounded-lg border bg-muted/50 p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium">Filters</h3>
                            <div class="flex items-center gap-2">
                                <Button v-if="hasActiveFilters" variant="ghost" class="text-muted-foreground" @click="clearFilters">
                                    <X class="h-4 w-4" />
                                    Clear all
                                </Button>
                                <CollapsibleTrigger as-child>
                                    <Button variant="ghost" class="text-muted-foreground">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </CollapsibleTrigger>
                            </div>
                        </div>

                        <div class="mb-8 flex flex-wrap gap-4">
                            <MultiTaskTypeSelector v-model="filter.taskTypes" />
                            <MultiStatusSelector v-model="filter.statusIds" :statuses="statuses" />
                            <MultiPrioritySelector v-model="filter.priorityIds" :priorities="priorities" />
                            <MultiUserSelector v-model="filter.supervisorIds" :users="supervisorsAndAdmins" placeholder="Select Supervisors" />
                            <MultiUserSelector v-model="filter.assigneeIds" :users="users" placeholder="Select Assignees" />
                            <MultiUserSelector v-model="filter.creatorIds" :users="supervisorsAndAdmins" placeholder="Select Creators" />
                            <MultiUserSelector v-model="filter.viewerIds" :users="users" placeholder="Select Viewers" />
                        </div>
                        <div class="mb-8 flex items-center space-x-2">
                            <Switch id="overdue-switch" v-model="filter.showOverdue" />
                            <Label for="overdue-switch">Filter to overdue tasks only</Label>
                        </div>
                        <div>
                            <Button @click="applyFilters">Apply Filters</Button>
                        </div>
                    </div>
                </CollapsibleContent>
            </Collapsible>

            <!-- Tasks table -->
            <div class="rounded-md border shadow-sm">
                <div class="relative max-h-[40rem] overflow-auto rounded-md">
                    <table class="w-full border-separate border-spacing-0 whitespace-nowrap text-sm">
                        <thead>
                            <tr>
                                <th class="sticky left-0 top-0 z-30 border-b border-r bg-muted px-4 py-3 text-left font-bold text-muted-foreground">
                                    Task
                                </th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">Slug</th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">Priority</th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">Status</th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">Due Date</th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">
                                    Last Updated At
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- task rows -->
                            <tr v-for="task in tasks.data" :key="task.slug" class="group transition-colors hover:bg-muted/50">
                                <td
                                    class="sticky left-0 z-10 max-w-md whitespace-normal border-b border-r bg-background p-0 transition-colors group-hover:bg-muted group-hover:text-blue-500"
                                >
                                    <Link
                                        :href="getTaskLink(task.slug, task.id)"
                                        class="block h-full w-full px-4 py-3 font-medium focus-visible:border focus-visible:border-primary focus-visible:text-blue-500 focus-visible:outline-none"
                                    >
                                        {{ task.name }}
                                    </Link>
                                </td>

                                <Link as="td" :href="getTaskLink(task.slug, task.id)" class="cursor-pointer border-b px-4 py-3 text-muted-foreground">
                                    {{ task.slug }}
                                </Link>

                                <Link as="td" :href="getTaskLink(task.slug, task.id)" class="cursor-pointer border-b px-4 py-3">
                                    <Badge :class="getBadgeColors(task.priority?.color)">
                                        {{ task.priority?.name }}
                                    </Badge>
                                </Link>

                                <Link as="td" :href="getTaskLink(task.slug, task.id)" class="cursor-pointer border-b px-4 py-3">
                                    <Badge :class="getBadgeColors(task.status?.color)">
                                        {{ task.status?.name }}
                                    </Badge>
                                </Link>
                                <Link as="td" :href="getTaskLink(task.slug, task.id)" class="cursor-pointer border-b px-4 py-3 text-muted-foreground">
                                    {{ readableDate(task.due_date) }}
                                </Link>
                                <Link as="td" :href="getTaskLink(task.slug, task.id)" class="cursor-pointer border-b px-4 py-3 text-muted-foreground">
                                    {{ readableDate(task.updated_at) }}
                                </Link>
                            </tr>

                            <!-- empty state -->
                            <tr v-if="tasks.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No tasks found. Try adjusting your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t px-4 py-3">
                    <div class="text-sm text-muted-foreground">
                        Showing <span class="font-medium">{{ tasks.from }}</span> to <span class="font-medium">{{ tasks.to }}</span> of
                        <span class="font-medium">{{ tasks.total }}</span> results
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground">Items per page:</span>
                            <Select v-model="filter.perPage" @update:model-value="(e) => changePerPage(e as number)">
                                <SelectTrigger class="w-20">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="option in perPageOptions" :key="option" :value="option">
                                            {{ option }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                        <Pagination
                            v-slot="{ page }"
                            v-model:page="filter.page"
                            :items-per-page="tasks.per_page"
                            :total="tasks.total"
                            :sibling-count="0"
                            show-edges
                            :default-page="tasks.current_page"
                            @update:page="(e) => updatePage(e)"
                        >
                            <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                                <PaginationFirst />
                                <PaginationPrev />

                                <template v-for="(item, index) in items">
                                    <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                                        <Button class="h-9 w-9 p-0" :variant="item.value === page ? 'default' : 'outline'">
                                            {{ item.value }}
                                        </Button>
                                    </PaginationListItem>
                                </template>

                                <PaginationNext />
                                <PaginationLast />
                            </PaginationList>
                        </Pagination>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
