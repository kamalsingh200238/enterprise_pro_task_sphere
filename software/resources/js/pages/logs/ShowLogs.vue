<script setup lang="ts">
import DateTimeRangePicker from '@/components/RangeDateTimePicker.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
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
import { getInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import { PaginatedData } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import { Bell } from 'lucide-vue-next';
import { ref } from 'vue';

interface ReadableLogSubject {
    heading: string;
    description: string;
}

interface ReadableLogCauser {
    name: string;
    email: string;
}

interface ReadableLog {
    id: number;
    timestamp: string;
    subject: ReadableLogSubject;
    event: string;
    causer: ReadableLogCauser;
    oldValues: string[];
    newValues: string[];
}

const props = defineProps<{
    logs: PaginatedData<ReadableLog>;
    startDate: string;
    endDate: string;
}>();

// Pagination and filtering
const perPageOptions = [10, 25, 50, 100];
const filter = ref({
    page: props.logs.current_page ?? 1,
    perPage: props.logs.per_page ?? 10,
    startDate: props.startDate,
    endDate: props.endDate,
});

// Fetch logs with pagination
const fetchLogs = () => {
    router.get(
        route('logs.show-all'),
        {
            page: filter.value.page,
            perPage: filter.value.perPage,
            startDate: filter.value.startDate,
            endDate: filter.value.endDate,
        },
        { preserveState: true, replace: true },
    );
};

// Update page number
const updatePage = (newPage: number) => {
    filter.value.page = newPage;
    fetchLogs();
};

// Change items per page
const changePerPage = (newPerPage: number) => {
    filter.value.perPage = newPerPage;
    filter.value.page = 1;
    fetchLogs();
};

const getReadableDate = (dateString: string) => {
    const d = parseISO(dateString);
    return format(d, 'dd MMM, yyyy HH:mm:ss');
};

const exportLogsPage = () => {
    window.open(
        route('logs.export', {
            startDate: filter.value.startDate,
            endDate: filter.value.endDate,
        }),
        '_blank',
    );
};
</script>

<template>
    <Head title="Activity Logs" />
    <AppLayout>
        <div class="container mx-auto space-y-6 px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex gap-5">
                    <DateTimeRangePicker v-model:start-date="filter.startDate" v-model:end-date="filter.endDate" />
                    <Button @click="fetchLogs"> Search </Button>
                </div>
                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button> Export Logs </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Export Logs Confirmation</AlertDialogTitle>
                            <AlertDialogDescription>
                                <div class="space-y-4 py-4">
                                    <div>
                                        <p>You are about to export logs from</p>
                                        <p>
                                            <span class="font-semibold">{{ getReadableDate(filter.startDate) }}</span> to
                                            <span class="font-semibold">{{ getReadableDate(filter.endDate) }}</span>
                                        </p>
                                    </div>
                                    <p>
                                        Once confirmed, a new page will open where your logs will be prepared for download. The download will start
                                        automatically once the logs are ready.
                                    </p>
                                    <p>This process may take some time depending on the amount of data being exported. Please be patient.</p>
                                </div>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click="exportLogsPage">Download Logs</AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
            <!-- Scrollable Table Container -->
            <div class="w-full rounded-md border shadow-sm">
                <div class="relative h-[40rem] overflow-x-auto rounded-md">
                    <table class="w-full border-separate border-spacing-0 whitespace-nowrap text-sm">
                        <thead>
                            <tr>
                                <th class="sticky left-0 top-0 z-30 border-b border-r bg-muted px-4 py-3 text-left font-bold text-muted-foreground">
                                    Timestamp
                                </th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">Caused by</th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">Event</th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">Subject</th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">
                                    Previous Values
                                </th>
                                <th class="sticky top-0 z-20 border-b bg-muted px-4 py-3 text-left font-bold text-muted-foreground">New Values</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="activityLog in logs.data" :key="activityLog.id" class="group transition-colors hover:bg-muted/50">
                                <!-- Timestamp Column -->
                                <td class="sticky left-0 z-10 border-b border-r bg-background px-6 py-4 transition-colors group-hover:bg-muted">
                                    {{ activityLog.timestamp }}
                                </td>

                                <!-- Causer Column -->
                                <td class="border-b px-4 py-3">
                                    <div class="flex items-center">
                                        <Avatar>
                                            <AvatarFallback>{{ getInitials(activityLog.causer.name) }}</AvatarFallback>
                                        </Avatar>
                                        <div class="ml-3">
                                            <div class="font-medium">
                                                {{ activityLog.causer.name }}
                                            </div>
                                            <div class="text-muted-foreground">
                                                {{ activityLog.causer.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Event Column -->
                                <td class="border-b px-6 py-4 capitalize">
                                    {{ activityLog.event }}
                                </td>

                                <!-- Subject Column -->
                                <td class="min-w-60 whitespace-normal border-b px-6 py-4">
                                    <div>
                                        {{ activityLog.subject.heading }}
                                    </div>
                                    <div class="text-muted-foreground">
                                        {{ activityLog.subject.description }}
                                    </div>
                                </td>

                                <!-- Old Values Column -->
                                <td class="min-w-96 space-y-4 whitespace-normal border-b px-6 py-4 font-bold">
                                    <div v-for="(oldValue, index) in activityLog.oldValues" :key="index" class="text-red-800 dark:text-red-500">
                                        {{ oldValue }}
                                    </div>
                                </td>

                                <!-- New Values Column -->
                                <td class="min-w-96 space-y-4 whitespace-normal border-b px-6 py-4 font-bold">
                                    <div v-for="(newValue, index) in activityLog.newValues" :key="index" class="text-green-800 dark:text-green-500">
                                        {{ newValue }}
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <Bell class="mb-4 h-12 w-12 text-gray-300" />
                                        <p class="text-lg">No activity logs found</p>
                                        <p class="text-sm text-gray-400">There are no activities for the selected period</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between border-t px-4 py-3">
                <div class="text-sm text-muted-foreground">
                    Showing <span class="font-medium">{{ logs.from }}</span> to <span class="font-medium">{{ logs.to }}</span> of
                    <span class="font-medium">{{ logs.total }}</span> results
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
                        :items-per-page="logs.per_page"
                        :total="logs.total"
                        :sibling-count="0"
                        show-edges
                        :default-page="logs.current_page"
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
    </AppLayout>
</template>
