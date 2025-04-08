<script setup lang="ts">
// import ui components and layout
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
} from '@/components/ui/pagination';
import AppLayout from '@/layouts/AppLayout.vue';

// import types and helper libraries
import { BreadcrumbItem, PaginatedData, SubTask } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { ref, watch } from 'vue';

// props from the server: list of subTasks and optional search string
interface Props {
    subTasks: PaginatedData<SubTask>;
    search?: string;
}

const props = defineProps<Props>();

// bind search input to local ref, fallback to empty if not passed in
const searchQuery = ref(props.search ?? '');

// track current page for pagination
const currentPage = ref(props.subTasks.current_page);

// fetch new results when search changes, with a small delay
const updateSearch = debounce(() => {
    router.get(
        route('sub-tasks.show-all'),
        {
            search: searchQuery.value,
            page: 1, // always reset to page 1 on new search
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}, 300);

// fetch results when a new page is selected
const updatePage = (page: number) => {
    router.get(
        route('sub-tasks.show-all'),
        {
            search: searchQuery.value,
            page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// react to changes in the search input
watch(searchQuery, updateSearch);

// breadcrumb for navigation at top of the page
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'See All Sub-Tasks',
        href: '/sub-tasks',
    },
];
</script>

<template>
    <!-- main layout with breadcrumb navigation -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto space-y-4 px-4 py-6">
            <!-- search input field for filtering subTasks -->
            <Input v-model="searchQuery" placeholder="Search sub-tasks..." />

            <div class="space-y-2">
                <!-- loop through each subTask and display it as a link -->
                <Link
                    v-for="subTask in subTasks.data"
                    :key="subTask.id"
                    :href="route('sub-tasks.show', subTask.id)"
                    class="group block rounded-md border bg-background p-4 shadow-sm ring-offset-background transition hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                    <!-- display subTask slug as the title -->
                    <h2 class="text-lg font-semibold text-foreground group-hover:text-primary">
                        {{ subTask.slug }}
                    </h2>
                    <!-- display subTask name below the title -->
                    <p class="text-muted-foreground">
                        {{ subTask.name }}
                    </p>
                </Link>
            </div>

            <!-- pagination control and display of results range -->
            <div class="flex items-center justify-between py-3">
                <div class="text-sm text-muted-foreground">
                    Showing
                    <span class="font-medium">{{ subTasks.from }}</span>
                    to
                    <span class="font-medium">{{ subTasks.to }}</span>
                    of
                    <span class="font-medium">{{ subTasks.total }}</span>
                    results
                </div>

                <div class="flex items-center gap-4">
                    <!-- pagination component to switch between pages -->
                    <Pagination
                        v-slot="{ page }"
                        v-model:page="currentPage"
                        :items-per-page="subTasks.per_page"
                        :total="subTasks.total"
                        :sibling-count="0"
                        show-edges
                        :default-page="subTasks.current_page"
                        @update:page="(e) => updatePage(e)"
                    >
                        <!-- list of pagination items -->
                        <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                            <PaginationFirst />
                            <PaginationPrev />

                            <!-- loop through pagination items and display page numbers -->
                            <template v-for="(item, index) in items" :key="index">
                                <PaginationListItem v-if="item.type === 'page'" :value="item.value" as-child>
                                    <!-- button for each page number -->
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
    </AppLayout>
</template>
