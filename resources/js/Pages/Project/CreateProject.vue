<script setup lang="ts">
import AssigneeSelector from '@/Components/AssigneeSelector.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FormError from '@/Components/FormError.vue';
import PrioritySelect from '@/Components/PrioritySelect.vue';
import StatusSelect from '@/Components/StatusSelect.vue';
import SupervisorSelect from '@/Components/SupervisorSelect.vue';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import ViewerSelector from '@/Components/ViewerSelector.vue';
import { Priority, Status, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface Props {
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
}

const props = defineProps<Props>();

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

const submit = () => {
    // if project is private remove all viewers
    if (!form.is_private) {
        form.viewers = [];
    }

    // submit form
    form.post(route('projects.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create New Project" />
    <main class="mx-auto max-w-7xl p-8">
        <form @submit.prevent="submit">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="col-span-2 space-y-6">
                    <div>
                        <Label for="name">Project Name </Label>
                        <Input id="name" type="text" v-model="form.name" />
                        <FormError :err="form.errors.name" />
                    </div>
                    <div>
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            rows="20"
                        />
                        <FormError :err="form.errors.description" />
                    </div>
                </div>
                <div class="space-y-6">
                    <div>
                        <Label for="status">Status</Label>
                        <StatusSelect
                            id="status"
                            v-model="form.status_id"
                            :statuses="props.statuses"
                        />
                        <FormError :err="form.errors.status_id" />
                    </div>
                    <div>
                        <Label for="priority">Priority</Label>
                        <PrioritySelect
                            id="priority"
                            v-model="form.priority_id"
                            :priorities="props.priorities"
                        />
                        <FormError :err="form.errors.priority_id" />
                    </div>
                    <div>
                        <div class="flex flex-col gap-1">
                            <Label for="start-date">Start Date</Label>
                            <DatePicker
                                id="start-date"
                                v-model="form.start_date"
                            />
                        </div>
                        <FormError :err="form.errors.start_date" />
                    </div>
                    <div>
                        <div class="flex flex-col gap-1">
                            <Label for="due-date">Due Date</Label>
                            <DatePicker id="due-date" v-model="form.due_date" />
                        </div>
                        <FormError :err="form.errors.due_date" />
                    </div>
                    <div>
                        <div class="flex flex-col gap-1">
                            <Label for="supervisor-selector">
                                Select Supervisor
                            </Label>
                            <SupervisorSelect
                                id="supervisor-selector"
                                :disabled="false"
                                :superisorsAndAdmins="
                                    props.supervisorsAndAdmins
                                "
                                v-model="form.supervisor_id"
                            />
                        </div>
                        <FormError :err="form.errors.supervisor_id" />
                    </div>
                    <div>
                        <div class="flex flex-col gap-1">
                            <Label for="assignees-selector">
                                Select Assignees
                            </Label>
                            <AssigneeSelector
                                id="assignees-selector"
                                :users="props.users"
                                v-model="form.assignees"
                                :disabled="false"
                            />
                        </div>
                        <FormError :err="form.errors.assignees" />
                    </div>
                    <div class="flex items-center space-x-2">
                        <Checkbox
                            id="is-private"
                            v-model:checked="form.is_private"
                        />
                        <label
                            for="is-private"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Private
                        </label>
                    </div>
                    <!-- display viewers selector when project is private -->
                    <div v-if="form.is_private === true">
                        <div class="flex flex-col gap-1">
                            <Label for="viewers-selector">
                                Select Viewers
                            </Label>
                            <ViewerSelector
                                id="viewers-selector"
                                :users="users"
                                v-model="form.viewers"
                                :assignee-ids="form.assignees"
                                :supervisor-id="form.supervisor_id"
                                :disabled="false"
                            />
                        </div>
                        <FormError :err="form.errors.viewers" />
                    </div>
                </div>
            </div>
            <Button type="submit" :disabled="form.processing">
                Create Project
            </Button>
        </form>
    </main>
</template>
