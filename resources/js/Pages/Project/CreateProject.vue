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

interface Props {
    statuses: Status[];
    priorities: Priority[];
    users: User[];
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
    form.post(route('projects.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create New Project" />
    <main>
        <form @submit.prevent="submit">
            <div>
                <Label for="name">Project Name </Label>
                <Input id="name" type="text" v-model="form.name" />
                <FormError :err="form.errors.name" />
            </div>
            <div>
                <Label for="description">Description</Label>
                <Textarea id="description" v-model="form.description" />
                <FormError :err="form.errors.description" />
            </div>
            <div class="flex gap-5">
                <div>
                    <div class="flex flex-col gap-1">
                        <Label for="start-date">Start Date</Label>
                        <DatePicker id="start-date" v-model="form.start_date" />
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
            </div>
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
                <SupervisorSelect :disabled="false" :users="users" v-model="form.supervisor_id" />
                <FormError :err="form.errors.supervisor_id" />
            </div>
            <div>
                <AssigneeSelector
                    :users="props.users"
                    v-model="form.assignees"
                    :disabled="false"
                />
                <FormError :err="form.errors.assignees" />
            </div>
            <div>
                <ViewerSelector
                    :users="users"
                    v-model="form.viewers"
                    :assignee-ids="form.assignees"
                    :disabled="true"
                />
                <FormError :err="form.errors.supervisor_id" />
            </div>
            <div class="flex items-center space-x-2">
                <Checkbox id="is-private" v-model="form.is_private" />
                <label
                    for="is-private"
                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                >
                    Private
                </label>
            </div>
            <Button type="submit" :disabled="form.processing">
                Create Project
            </Button>
        </form>
    </main>
</template>
