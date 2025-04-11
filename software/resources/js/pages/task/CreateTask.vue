<script setup lang="ts">
import AssigneeSelector from '@/components/AssigneeSelector.vue';
import DatePicker from '@/components/DatePicker.vue';
import FormError from '@/components/FormError.vue';
import PrioritySelect from '@/components/PrioritySelect.vue';
import ProjectSelect from '@/components/ProjectSelect.vue';
import StatusSelect from '@/components/StatusSelect.vue';
import SupervisorSelect from '@/components/SupervisorSelect.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ViewerSelector from '@/components/ViewerSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Priority, Project, Status, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

// props for the page
interface Props {
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
    projects: Project[];
}

// props passed from the server-side (Inertia)
const props = defineProps<Props>();

// form data type definition
type FormType = {
    project_id: number | null;
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

// initialize form state using useForm from Inertia
const form = useForm<FormType>({
    project_id: null,
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

// submit the form data
const submit = () => {
    // if task is not private, remove all viewers
    if (!form.is_private) {
        form.viewers = [];
    }

    // submit the form using inertia's post method
    form.post(route('tasks.store'), {
        onSuccess: () => {
            form.reset(); // reset the form on successful submission
        },
    });
};

// breadcrumb for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create Task',
        href: '/tasks/new',
    },
];
</script>

<template>
    <!-- page title for browser tab -->
    <Head title="Create New Task" />

    <!-- app layout wrapper -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <!-- header section with page title and submit button -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <!-- page title -->
                <h1 class="text-2xl font-bold">Create New Task</h1>
                <!-- submit button for task creation -->
                <Button type="submit" :disabled="form.processing" @click="submit"> Create Task </Button>
            </div>

            <!-- form for creating a new task -->
            <form @submit.prevent="submit">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- left section: task details (name, description) -->
                    <div class="col-span-full lg:col-span-2">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-6 p-6">
                                <!-- task name input field -->
                                <div>
                                    <Label for="name">Task Name</Label>
                                    <Input id="name" type="text" v-model="form.name" class="mt-1" />
                                    <FormError :err="form.errors.name" />
                                </div>

                                <!-- task description textarea -->
                                <div>
                                    <Label for="description">Description</Label>
                                    <Textarea id="description" v-model="form.description" rows="15" class="mt-1" />
                                    <FormError :err="form.errors.description" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- right section: task settings and assignments -->
                    <div class="col-span-full lg:col-span-1">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-4 p-6">
                                <!-- status selection dropdown -->
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

                                <!-- priority selection dropdown -->
                                <div>
                                    <Label for="priority">Priority</Label>
                                    <PrioritySelect id="priority" v-model="form.priority_id" :priorities="props.priorities" :disabled="false" />
                                    <FormError :err="form.errors.priority_id" />
                                </div>

                                <!-- start and due date selection -->
                                <div class="space-y-4">
                                    <!-- start date -->
                                    <div class="flex flex-col">
                                        <Label for="start-date" class="mb-1">Start Date</Label>
                                        <DatePicker id="start-date" v-model="form.start_date" :disabled="false" />
                                        <FormError :err="form.errors.start_date" />
                                    </div>

                                    <!-- due date -->
                                    <div class="flex flex-col">
                                        <Label for="due-date" class="mb-1">Due Date</Label>
                                        <DatePicker id="due-date" v-model="form.due_date" :disabled="false" />
                                        <FormError :err="form.errors.due_date" />
                                    </div>
                                </div>

                                <!-- project selection dropdown -->
                                <div class="flex flex-col">
                                    <Label for="project-selector" class="mb-1">Select Project</Label>
                                    <ProjectSelect id="project-selector" :disabled="false" :projects="props.projects" v-model="form.project_id" />
                                    <FormError :err="form.errors.project_id" />
                                </div>

                                <!-- supervisor selection dropdown -->
                                <div class="flex flex-col">
                                    <Label for="supervisor-selector" class="mb-1">Select Supervisor</Label>
                                    <SupervisorSelect
                                        id="supervisor-selector"
                                        :disabled="false"
                                        :superisorsAndAdmins="props.supervisorsAndAdmins"
                                        v-model="form.supervisor_id"
                                    />
                                    <FormError :err="form.errors.supervisor_id" />
                                </div>

                                <!-- assignees selection dropdown -->
                                <div class="flex flex-col">
                                    <Label for="assignees-selector" class="mb-1">Select Assignees</Label>
                                    <AssigneeSelector id="assignees-selector" :users="props.users" v-model="form.assignees" :disabled="false" />
                                    <FormError :err="form.errors.assignees" />
                                </div>

                                <!-- private task checkbox -->
                                <div class="flex items-center space-x-2 pt-2">
                                    <Checkbox id="is-private" v-model="form.is_private" />
                                    <label
                                        for="is-private"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Private Task
                                    </label>
                                </div>

                                <!-- viewers selection (only shown if task is private) -->
                                <div v-if="form.is_private" class="flex flex-col pt-2">
                                    <Label for="viewers-selector" class="mb-1">Viewers</Label>
                                    <ViewerSelector
                                        id="viewers-selector"
                                        :users="props.users"
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

                <!-- mobile submit button (visible on small screens) -->
                <div class="mt-6 flex justify-center lg:hidden">
                    <Button type="submit" :disabled="form.processing" class="max-w-xs"> Create Task </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
