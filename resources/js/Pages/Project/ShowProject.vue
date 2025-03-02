<script setup lang="ts">
import AssigneeSelector from '@/Components/AssigneeSelector.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FormError from '@/Components/FormError.vue';
import PrioritySelect from '@/Components/PrioritySelect.vue';
import StatusSelect from '@/Components/StatusSelect.vue';
import SupervisorSelect from '@/Components/SupervisorSelect.vue';
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
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import ViewerSelector from '@/Components/ViewerSelector.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import { Priority, Project, Status, User } from '@/types';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    can: {
        edit: boolean;
        updateStatus: boolean;
        updateStatusToDone: boolean;
    };
    project: Project;
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
}

const props = defineProps<Props>();
const isEditMode = ref(false);
const isConfirmModalVisible = ref(false);

const form = useForm({
    name: props.project.name,
    description: props.project.description,
    start_date: props.project.start_date,
    due_date: props.project.due_date,
    status_id: props.project.status_id,
    priority_id: props.project.priority_id,
    is_private: props.project.is_private,
    supervisor_id: props.project.supervisor_id,
    assignees: props.project.assignees.map((user) => user.id),
    viewers: props.project.viewers.map((user) => user.id),
});

const submitEdit = () => {
    if (!form.is_private) {
        form.viewers = [];
    }

    form.put(route('projects.edit', props.project.id), {
        onSuccess: () => {
            isEditMode.value = false;
        },
    });
};

const toggleStatus = () => {
    if (!isEditMode.value) {
        router.put(route('projects.update-status', props.project.id), {
            status_id: form.status_id,
        });
    }
};

const deleteProject = () => {
    isConfirmModalVisible.value = true;
};
</script>

<template>
    <BaseLayout>
        <main class="mx-auto max-w-7xl p-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ project.slug }}</h1>
                <Button
                    v-if="!isEditMode && props.can.edit"
                    @click="isEditMode = true"
                >
                    Edit Project
                </Button>
                <Button v-if="isEditMode" as-child>
                    <Link :href="route('projects.show', project.id)">
                        Cancel
                    </Link>
                </Button>
            </div>

            <form @submit.prevent="isEditMode ? submitEdit() : null">
                <div class="grid gap-8 lg:grid-cols-3">
                    <div class="col-span-2 space-y-6">
                        <div>
                            <Label for="name">Project Name</Label>
                            <Input
                                id="name"
                                type="text"
                                v-model="form.name"
                                :disabled="!isEditMode"
                            />
                            <FormError :err="form.errors.name" />
                        </div>
                        <div>
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="20"
                                :disabled="!isEditMode"
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
                                :disabled="!props.can.updateStatus"
                                :update-status-to-done="
                                    props.can.updateStatusToDone
                                "
                                @update:modelValue="
                                    isEditMode ? null : toggleStatus()
                                "
                            />
                            <FormError :err="form.errors.status_id" />
                        </div>
                        <div>
                            <Label for="priority">Priority</Label>
                            <PrioritySelect
                                id="priority"
                                v-model="form.priority_id"
                                :priorities="props.priorities"
                                :disabled="!isEditMode"
                            />
                            <FormError :err="form.errors.priority_id" />
                        </div>
                        <div>
                            <div class="flex flex-col gap-1">
                                <Label for="start-date">Start Date</Label>
                                <DatePicker
                                    id="start-date"
                                    v-model="form.start_date"
                                    :disabled="!isEditMode"
                                />
                            </div>
                            <FormError :err="form.errors.start_date" />
                        </div>
                        <div>
                            <div class="flex flex-col gap-1">
                                <Label for="due-date">Due Date</Label>
                                <DatePicker
                                    id="due-date"
                                    v-model="form.due_date"
                                    :disabled="!isEditMode"
                                />
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
                                    :disabled="!isEditMode"
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
                                    :disabled="!isEditMode"
                                />
                            </div>
                            <FormError :err="form.errors.assignees" />
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is-private"
                                v-model="form.is_private"
                                :disabled="!isEditMode"
                            />
                            <label
                                for="is-private"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                :class="{ 'opacity-70': !isEditMode }"
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
                                    :disabled="!isEditMode"
                                />
                            </div>
                            <FormError :err="form.errors.viewers" />
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <Button
                        v-if="isEditMode"
                        type="submit"
                        :disabled="form.processing"
                    >
                        Save Changes
                    </Button>
                </div>
            </form>
            <AlertDialog v-model:open="isConfirmModalVisible">
                <AlertDialogTrigger as-child>
                    <Button variant="destructive" @click="deleteProject">
                        Delete
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Are you absolutely sure?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently
                            delete your account and remove your data from our
                            servers.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction as-child>
                            <Button as-child>
                                <Link
                                    method="delete"
                                    :href="
                                        route(
                                            'projects.delete',
                                            props.project.id,
                                        )
                                    "
                                >
                                    Confirm
                                </Link>
                            </Button>
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </main>
    </BaseLayout>
</template>
