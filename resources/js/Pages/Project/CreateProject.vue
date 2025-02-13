<script setup lang="ts">
import DatePicker from '@/Components/DatePicker.vue';
import FormError from '@/Components/FormError.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { useForm } from '@inertiajs/vue3';

import StatusSelect from '@/Components/StatusSelect.vue';

import PrioritySelect from '@/Components/PrioritySelect.vue';
import { Priority, Status, User } from '@/types';
import UserSelect from '@/Components/UserSelect.vue';

interface Props {
    statuses: Status[];
    priorities: Priority[];
    users: User[];
}

const props = defineProps<Props>();

const form = useForm<{
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status_id: string;
    priority_id: string;
    is_private: boolean;
    supervisor_id?: string;
    assignees: number[];
    viewers: number[];
}>({
    name: '',
    description: '',
    start_date: '',
    due_date: '',
    status_id: '',
    priority_id: '',
    is_private: false,
    supervisor_id: '',
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
    <div class="mx-auto max-w-2xl py-8">
        <form @submit.prevent="submit" class="space-y-6">
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
                <div class="flex flex-col gap-1">
                    <Label for="start-date">Start Date</Label>
                    <DatePicker id="start-date" v-model="form.start_date" />
                    <FormError :err="form.errors.start_date" />
                </div>
                <div class="flex flex-col gap-1">
                    <Label for="due-date">Due Date</Label>
                    <DatePicker id="due-date" v-model="form.due_date" />
                    <FormError :err="form.errors.due_date" />
                </div>
            </div>
            <div>
                <Label for="status">Status</Label>
                <StatusSelect
                    id="status"
                    v-model="form.status_id"
                    :statuses="props.statuses"
                ></StatusSelect>
                <FormError :err="form.errors.status_id" />
            </div>
            <div>
                <Label for="priority">Priority</Label>
                <PrioritySelect
                    id="priority"
                    v-model="form.priority_id"
                    :priorities="props.priorities"
                ></PrioritySelect>
                <FormError :err="form.errors.priority_id" />
            </div>
            <div>
                <UserSelect :users="props.users" />
            </div>
            <Button type="submit" :disabled="form.processing">
                Create Project
            </Button>
        </form>
    </div>
</template>
