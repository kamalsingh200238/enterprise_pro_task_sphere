<script setup lang="ts">
// import components and ui elements
import AssigneeSelector from '@/components/AssigneeSelector.vue';
import DatePicker from '@/components/DatePicker.vue';
import FormError from '@/components/FormError.vue';
import PrioritySelect from '@/components/PrioritySelect.vue';
import StatusSelect from '@/components/StatusSelect.vue';
import SupervisorSelect from '@/components/SupervisorSelect.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ViewerSelector from '@/components/ViewerSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Priority, Status, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

// define props
interface Props {
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
}

const props = defineProps<Props>();

// define form shape
type FormType = {
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status_id: number | null;
    priority_id: number | null;
    is_private: boolean;
    supervisor_id: number | null;
    assignees: number[];
    viewers: number[];
};

// initialize form
const form = useForm<FormType>({
    name: '',
    description: '',
    start_date: '',
    due_date: '',
    status_id: null,
    priority_id: null,
    is_private: false,
    supervisor_id: null,
    assignees: [],
    viewers: [],
});

// handle submit
const submit = () => {
    // if not private then remove viewers
    if (!form.is_private) {
        form.viewers = [];
    }

    // send form data to server
    form.post(route('projects.store'), {
        onSuccess: () => {
            // reset form after success
            form.reset();
        },
    });
};

// breadcrumb for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create Project',
        href: '/projects/new',
    },
];
</script>

<template>
    <!-- set page title -->
    <Head title="Create New Project" />

    <!-- wrap page content inside main layout with breadcrumbs -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <!-- header section with page title and top-right submit button -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">Create New Project</h1>
                <!-- button to submit form (top-right on desktop) -->
                <Button type="submit" :disabled="form.processing" @click="submit"> Create Project </Button>
            </div>

            <form @submit.prevent="submit">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- left side: main project details -->
                    <div class="col-span-full lg:col-span-2">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-6 p-6">
                                <!-- input for project name -->
                                <div>
                                    <Label for="name">Project Name</Label>
                                    <Input id="name" type="text" v-model="form.name" class="mt-1" />
                                    <FormError :err="form.errors.name" />
                                </div>

                                <!-- input for project description -->
                                <div>
                                    <Label for="description">Description</Label>
                                    <Textarea id="description" v-model="form.description" rows="15" class="mt-1" />
                                    <FormError :err="form.errors.description" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- right side: settings and assignments -->
                    <div class="col-span-full lg:col-span-1">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-4 p-6">
                                <!-- select project status -->
                                <div>
                                    <Label for="status">Status</Label>
                                    <StatusSelect
                                        id="status"
                                        v-model="form.status_id"
                                        :statuses="props.statuses"
                                        :disabled="false"
                                        :update-status-to-done="true"
                                    />
                                    <FormError :err="form.errors.status_id" />
                                </div>

                                <!-- select project priority -->
                                <div>
                                    <Label for="priority">Priority</Label>
                                    <PrioritySelect id="priority" v-model="form.priority_id" :priorities="props.priorities" :disabled="false" />
                                    <FormError :err="form.errors.priority_id" />
                                </div>

                                <!-- pick start and due dates -->
                                <div class="space-y-4">
                                    <div class="flex flex-col">
                                        <Label for="start-date" class="mb-1">Start Date</Label>
                                        <DatePicker id="start-date" v-model="form.start_date" :disabled="false" />
                                        <FormError :err="form.errors.start_date" />
                                    </div>

                                    <div class="flex flex-col">
                                        <Label for="due-date" class="mb-1">Due Date</Label>
                                        <DatePicker id="due-date" v-model="form.due_date" :disabled="false" />
                                        <FormError :err="form.errors.due_date" />
                                    </div>
                                </div>

                                <!-- select supervisor -->
                                <div class="flex flex-col">
                                    <Label for="supervisor-selector" class="mb-1">Supervisor</Label>
                                    <SupervisorSelect
                                        id="supervisor-selector"
                                        :disabled="false"
                                        :superisorsAndAdmins="props.supervisorsAndAdmins"
                                        v-model="form.supervisor_id"
                                    />
                                    <FormError :err="form.errors.supervisor_id" />
                                </div>

                                <!-- select assignees -->
                                <div class="flex flex-col">
                                    <Label for="assignees-selector" class="mb-1">Assignees</Label>
                                    <AssigneeSelector id="assignees-selector" :users="props.users" v-model="form.assignees" :disabled="false" />
                                    <FormError :err="form.errors.assignees" />
                                </div>

                                <!-- checkbox to mark project as private -->
                                <div class="flex items-center space-x-2 pt-2">
                                    <Checkbox id="is-private" v-model="form.is_private" />
                                    <label
                                        for="is-private"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Private Project
                                    </label>
                                </div>

                                <!-- show viewer selector only if project is private -->
                                <div v-if="form.is_private" class="flex flex-col pt-2">
                                    <Label for="viewers-selector" class="mb-1">Viewers</Label>
                                    <ViewerSelector
                                        id="viewers-selector"
                                        :users="users"
                                        v-model="form.viewers"
                                        :assignee-ids="form.assignees"
                                        :supervisor-id="form.supervisor_id"
                                        :disabled="false"
                                    />
                                    <FormError :err="form.errors.viewers" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- submit button for mobile users (shown only on small screens) -->
                <div class="mt-6 flex justify-center lg:hidden">
                    <Button type="submit" :disabled="form.processing" class="max-w-xs"> Create Project </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
