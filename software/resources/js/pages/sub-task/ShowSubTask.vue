<script setup lang="ts">
// imports for components, layout, UI, and helpers
import AssigneeSelector from '@/components/AssigneeSelector.vue';
import Comment from '@/components/Comment.vue';
import DatePicker from '@/components/DatePicker.vue';
import FormError from '@/components/FormError.vue';
import PrioritySelect from '@/components/PrioritySelect.vue';
import StatusSelect from '@/components/StatusSelect.vue';
import SupervisorSelect from '@/components/SupervisorSelect.vue';
import TaskSelect from '@/components/TaskSelect.vue';
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
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ViewerSelector from '@/components/ViewerSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Priority, Status, SubTask, Task, User } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// props received from server
interface Props {
    can: {
        edit: boolean;
        updateStatus: boolean;
        updateStatusToDone: boolean;
        deleteComment: boolean;
        comment: boolean;
    };
    subTask: SubTask;
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
    tasks: Task[];
}

const props = defineProps<Props>();

// whether the form is in edit mode
const isEditMode = ref(false);

// main subtask form initialized with subtask data
const form = useForm({
    task_id: props.subTask.task_id,
    name: props.subTask.name,
    description: props.subTask.description,
    start_date: props.subTask.start_date,
    due_date: props.subTask.due_date,
    status_id: props.subTask.status_id,
    priority_id: props.subTask.priority_id,
    is_private: props.subTask.is_private,
    supervisor_id: props.subTask.supervisor_id,
    assignees: props.subTask.assignees.map((user) => user.id),
    viewers: props.subTask.viewers.map((user) => user.id),
});

// comment form for posting a new comment
const commentForm = useForm({
    content: '',
});

// handles subtask update on form submit
const submitEdit = () => {
    if (!form.is_private) {
        form.viewers = []; // remove viewers if subtask is not private
    }

    form.put(route('sub-tasks.edit', props.subTask.id), {
        onSuccess: () => {
            isEditMode.value = false; // exit edit mode after successful update
        },
    });
};

// toggles subtask status when not in edit mode
const toggleStatus = () => {
    if (!isEditMode.value) {
        router.put(route('sub-tasks.update-status', props.subTask.id), {
            status_id: form.status_id,
        });
    }
};

// posts a new comment and clears input on success
const submitComment = () => {
    commentForm.post(route('sub-tasks.add-comment', props.subTask.id), {
        onSuccess: () => {
            commentForm.content = '';
        },
        preserveScroll: true,
    });
};

// breadcrumb trail for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: props.subTask.parent?.parent?.slug || 'Project',
        href: `/projects/${props.subTask.parent?.project_id}`,
    },
    {
        title: props.subTask.parent?.slug || 'Task',
        href: `/tasks/${props.subTask.task_id}`,
    },
    {
        title: props.subTask.slug,
        href: `/sub-tasks/${props.subTask.id}`,
    },
];
</script>

<template>
    <Head title="Sub-Task Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <!-- SubTask Title Section -->
                <h1 class="text-2xl font-bold">{{ subTask.slug }}</h1>

                <!-- Action Buttons: Edit, Save, Cancel, and Delete -->
                <div class="flex flex-wrap gap-2">
                    <!-- Edit Button (only visible if not in edit mode and the user can edit) -->
                    <Button v-if="!isEditMode && props.can.edit" @click="isEditMode = true"> Edit Sub-Task </Button>

                    <!-- Save Button (only visible when in edit mode and the form is not processing) -->
                    <Button v-if="isEditMode" type="submit" :disabled="form.processing" @click="submitEdit"> Save Changes </Button>

                    <!-- Cancel Button (only visible when in edit mode) -->
                    <Button v-if="isEditMode" variant="outline" @click="isEditMode = false"> Cancel </Button>

                    <!-- Delete SubTask Alert Dialog (visible when not in edit mode and user can delete) -->
                    <AlertDialog v-if="!isEditMode && can.edit">
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">Delete Sub-Task</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    This action cannot be undone. This will permanently delete the sub-task and all associated data.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <!-- Cancel the deletion -->
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <!-- Confirm the deletion with a destructive action -->
                                <AlertDialogAction as-child>
                                    <Button as-child variant="destructive">
                                        <Link method="delete" :href="route('sub-tasks.delete', props.subTask.id)"> Delete </Link>
                                    </Button>
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <!-- SubTask Edit Form -->
            <form id="edit-form" @submit.prevent="isEditMode ? submitEdit() : null">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content - Left Side -->
                    <div class="col-span-full lg:col-span-2">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-6 p-6">
                                <!-- SubTask Name Field -->
                                <div>
                                    <Label for="name">Sub-Task Name</Label>
                                    <Input id="name" type="text" v-model="form.name" :disabled="!isEditMode" class="mt-1" />
                                    <FormError :err="form.errors.name" />
                                </div>

                                <!-- SubTask Description Field -->
                                <div>
                                    <Label for="description">Description</Label>
                                    <Textarea id="description" v-model="form.description" rows="15" :disabled="!isEditMode" class="mt-1" />
                                    <FormError :err="form.errors.description" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar - Right Side (for status, priority, dates, etc.) -->
                    <div class="col-span-full lg:col-span-1">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-6 p-6">
                                <!-- SubTask Status Field -->
                                <div>
                                    <Label for="status">Status</Label>
                                    <StatusSelect
                                        id="status"
                                        v-model="form.status_id"
                                        :statuses="props.statuses"
                                        :disabled="!props.can.updateStatus"
                                        :update-status-to-done="props.can.updateStatusToDone"
                                        @update:modelValue="isEditMode ? null : toggleStatus()"
                                    />
                                    <FormError :err="form.errors.status_id" />
                                </div>

                                <!-- SubTask Priority Field -->
                                <div>
                                    <Label for="priority">Priority</Label>
                                    <PrioritySelect id="priority" v-model="form.priority_id" :priorities="props.priorities" :disabled="!isEditMode" />
                                    <FormError :err="form.errors.priority_id" />
                                </div>

                                <!-- Start Date Field -->
                                <div class="flex flex-col">
                                    <Label for="start-date" class="mb-1">Start Date</Label>
                                    <DatePicker id="start-date" v-model="form.start_date" :disabled="!isEditMode" />
                                    <FormError :err="form.errors.start_date" />
                                </div>

                                <!-- Due Date Field -->
                                <div class="flex flex-col">
                                    <Label for="due-date" class="mb-1">Due Date</Label>
                                    <DatePicker id="due-date" v-model="form.due_date" :disabled="!isEditMode" />
                                    <FormError :err="form.errors.due_date" />
                                </div>

                                <!-- Parent Task Selection Field -->
                                <div class="flex flex-col">
                                    <Label for="task-selector" class="mb-1">Parent Task</Label>
                                    <TaskSelect id="task-selector" :tasks="props.tasks" v-model="form.task_id" :disabled="!isEditMode" />
                                    <FormError :err="form.errors.task_id" />
                                </div>

                                <!-- Supervisor Field -->
                                <div class="flex flex-col">
                                    <Label for="supervisor-selector" class="mb-1">Supervisor</Label>
                                    <SupervisorSelect
                                        id="supervisor-selector"
                                        :disabled="!isEditMode"
                                        :superisorsAndAdmins="props.supervisorsAndAdmins"
                                        v-model="form.supervisor_id"
                                    />
                                    <FormError :err="form.errors.supervisor_id" />
                                </div>

                                <!-- Assignees Field -->
                                <div class="flex flex-col">
                                    <Label for="assignees-selector" class="mb-1">Assignees</Label>
                                    <AssigneeSelector id="assignees-selector" :users="props.users" v-model="form.assignees" :disabled="!isEditMode" />
                                    <FormError :err="form.errors.assignees" />
                                </div>

                                <!-- Private SubTask Checkbox -->
                                <div class="flex items-center space-x-2 pt-2">
                                    <Checkbox id="is-private" v-model="form.is_private" :disabled="!isEditMode" />
                                    <label
                                        for="is-private"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        :class="{ 'opacity-70': !isEditMode }"
                                    >
                                        Private Sub-Task
                                    </label>
                                </div>

                                <!-- Viewers Field (only visible if subtask is private) -->
                                <div v-if="form.is_private" class="flex flex-col pt-2">
                                    <Label for="viewers-selector" class="mb-1">Viewers</Label>
                                    <ViewerSelector
                                        id="viewers-selector"
                                        :users="users"
                                        v-model="form.viewers"
                                        :assignee-ids="form.assignees"
                                        :supervisor-id="form.supervisor_id"
                                        :disabled="!isEditMode"
                                    />
                                    <FormError :err="form.errors.viewers" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Submit Button (visible on small screens when in edit mode) -->
                <div v-if="isEditMode" class="mt-6 flex justify-center lg:hidden">
                    <Button type="submit" :disabled="form.processing" class="max-w-xs"> Save Changes </Button>
                </div>
            </form>

            <!-- Comments Section -->
            <div class="mt-6 w-full rounded-md border shadow-sm">
                <div class="p-6">
                    <h2 class="mb-4 text-xl font-semibold">Sub-Task Comments</h2>

                    <!-- Comment Form (visible only if the user can comment) -->
                    <form v-if="can.comment" @submit.prevent="submitComment" class="mb-6">
                        <div class="space-y-3">
                            <Textarea id="update-content" v-model="commentForm.content" placeholder="Add a sub-task comment..." rows="3" />
                            <FormError :err="commentForm.errors.content" />
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="commentForm.processing || !commentForm.content"> Post Comment </Button>
                            </div>
                        </div>
                    </form>

                    <!-- Loop over the comments and display each one using the Comment component -->
                    <div class="space-y-4">
                        <div v-if="!subTask.comments || subTask.comments?.length === 0" class="py-4 text-center text-muted-foreground">
                            No comments yet. Be the first to add a comment!
                        </div>

                        <!-- Loop through all comments and display each one using the Comment component -->
                        <Comment
                            v-for="comment in subTask.comments"
                            :key="comment.id"
                            :comment="comment"
                            :canDeleteComment="props.can.deleteComment"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
