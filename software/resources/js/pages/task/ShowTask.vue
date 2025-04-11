<script setup lang="ts">
// imports for components, layout, UI, and helpers
import AssigneeSelector from '@/components/AssigneeSelector.vue';
import Comment from '@/components/Comment.vue';
import DatePicker from '@/components/DatePicker.vue';
import FormError from '@/components/FormError.vue';
import PrioritySelect from '@/components/PrioritySelect.vue';
import ProjectSelect from '@/components/ProjectSelect.vue';
import StatusSelect from '@/components/StatusSelect.vue';
import SupervisorSelect from '@/components/SupervisorSelect.vue';
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
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ViewerSelector from '@/components/ViewerSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Priority, Project, Status, Task, User } from '@/types';
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDownIcon, ChevronRightIcon, ExternalLink } from 'lucide-vue-next';
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
    task: Task;
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
    projects: Project[];
}

const props = defineProps<Props>();

// whether the form is in edit mode
const isEditMode = ref(false);

// main task form initialized with task data
const form = useForm({
    project_id: props.task.project_id,
    name: props.task.name,
    description: props.task.description,
    start_date: props.task.start_date,
    due_date: props.task.due_date,
    status_id: props.task.status_id,
    priority_id: props.task.priority_id,
    is_private: props.task.is_private,
    supervisor_id: props.task.supervisor_id,
    assignees: props.task.assignees.map((user) => user.id),
    viewers: props.task.viewers.map((user) => user.id),
});

// comment form for posting a new comment
const commentForm = useForm({
    content: '',
});

// handles task update on form submit
const submitEdit = () => {
    if (!form.is_private) {
        form.viewers = []; // remove viewers if task is not private
    }

    form.put(route('tasks.edit', props.task.id), {
        onSuccess: () => {
            isEditMode.value = false; // exit edit mode after successful update
        },
    });
};

// toggles task status when not in edit mode
const toggleStatus = () => {
    if (!isEditMode.value) {
        router.put(route('tasks.update-status', props.task.id), {
            status_id: form.status_id,
        });
    }
};

// posts a new comment and clears input on success
const submitComment = () => {
    commentForm.post(route('tasks.add-comment', props.task.id), {
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
        title: props.task.parent?.slug || 'Project',
        href: `/projects/${props.task.project_id}`,
    },
    {
        title: props.task.slug,
        href: `/tasks/${props.task.id}`,
    },
];
</script>

<template>
    <Head title="Task Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <!-- Task Title Section -->
                <h1 class="text-2xl font-bold">{{ task.slug }}</h1>

                <!-- Action Buttons: Edit, Save, Cancel, and Delete -->
                <div class="flex flex-wrap gap-2">
                    <!-- Edit Button (only visible if not in edit mode and the user can edit) -->
                    <Button v-if="!isEditMode && props.can.edit" @click="isEditMode = true"> Edit Task </Button>

                    <!-- Save Button (only visible when in edit mode and the form is not processing) -->
                    <Button v-if="isEditMode" type="submit" :disabled="form.processing" @click="submitEdit"> Save Changes </Button>

                    <!-- Cancel Button (only visible when in edit mode) -->
                    <Button v-if="isEditMode" variant="outline" @click="isEditMode = false"> Cancel </Button>

                    <!-- Delete Task Alert Dialog (visible when not in edit mode and user can delete) -->
                    <AlertDialog v-if="!isEditMode && can.edit">
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">Delete Task</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    This action cannot be undone. This will permanently delete the task and all associated data.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <!-- Cancel the deletion -->
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <!-- Confirm the deletion with a destructive action -->
                                <AlertDialogAction as-child>
                                    <Button as-child variant="destructive">
                                        <Link method="delete" :href="route('tasks.delete', props.task.id)"> Delete </Link>
                                    </Button>
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <!-- Task Edit Form -->
            <form id="edit-form" @submit.prevent="isEditMode ? submitEdit() : null">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content - Left Side -->
                    <div class="col-span-full lg:col-span-2">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-6 p-6">
                                <!-- Task Name Field -->
                                <div>
                                    <Label for="name">Task Name</Label>
                                    <Input id="name" type="text" v-model="form.name" :disabled="!isEditMode" class="mt-1" />
                                    <FormError :err="form.errors.name" />
                                </div>

                                <!-- Task Description Field -->
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
                                <!-- Task Status Field -->
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

                                <!-- Task Priority Field -->
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

                                <!-- Project Selection Field -->
                                <div class="flex flex-col">
                                    <Label for="project-selector" class="mb-1">Project</Label>
                                    <ProjectSelect
                                        id="project-selector"
                                        :projects="props.projects"
                                        v-model="form.project_id"
                                        :disabled="!isEditMode"
                                    />
                                    <FormError :err="form.errors.project_id" />
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

                                <!-- Private Task Checkbox -->
                                <div class="flex items-center space-x-2 pt-2">
                                    <Checkbox id="is-private" v-model="form.is_private" :disabled="!isEditMode" />
                                    <label
                                        for="is-private"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        :class="{ 'opacity-70': !isEditMode }"
                                    >
                                        Private Task
                                    </label>
                                </div>

                                <!-- Viewers Field (only visible if task is private) -->
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
                    <h2 class="mb-4 text-xl font-semibold">Task Comments</h2>

                    <!-- Comment Form (visible only if the user can comment) -->
                    <form v-if="can.comment" @submit.prevent="submitComment" class="mb-6">
                        <div class="space-y-3">
                            <Textarea id="update-content" v-model="commentForm.content" placeholder="Add a task comment..." rows="3" />
                            <FormError :err="commentForm.errors.content" />
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="commentForm.processing || !commentForm.content"> Post Comment </Button>
                            </div>
                        </div>
                    </form>

                    <!-- Loop over the comments and display each one using the Comment component -->
                    <div class="space-y-4">
                        <div v-if="!task.comments || task.comments.length === 0" class="py-4 text-center text-muted-foreground">
                            No comments yet. Be the first to add a comment!
                        </div>

                        <!-- Loop through all comments and display each one using the Comment component -->
                        <Comment v-for="comment in task.comments" :key="comment.id" :comment="comment" :canDeleteComment="props.can.deleteComment" />
                    </div>
                </div>
            </div>

            <!-- Sub-Tasks Section -->
            <div class="mt-6 w-full rounded-md border shadow-sm">
                <div class="p-6">
                    <!-- Header for Sub-Tasks -->
                    <h2 class="mb-4 text-xl font-semibold">Sub-Tasks</h2>

                    <!-- Display message if no subtasks are present -->
                    <div v-if="!task.subtasks?.length" class="text-sm text-muted-foreground">No sub-tasks yet.</div>

                    <!-- Loop through the subtasks and display each one inside a collapsible container -->
                    <div v-else class="space-y-2">
                        <Collapsible v-for="subtask in task.subtasks" :key="subtask.id" class="ml-2" v-slot="{ open }">
                            <!-- Trigger for expanding/collapsing each subtask -->
                            <CollapsibleTrigger class="flex w-full items-center gap-2 overflow-hidden">
                                <!-- Chevron icons to indicate open/closed state -->
                                <span>
                                    <ChevronRightIcon v-if="!open" class="h-4 w-4" />
                                    <ChevronDownIcon v-else class="h-4 w-4" />
                                </span>
                                <!-- Display subtask information like subtask slug and link to view the subtask -->
                                <span class="font-medium">{{ subtask.slug }} :</span>
                                <a
                                    :href="route('sub-tasks.show', subtask.id)"
                                    target="_blank"
                                    class="inline-block text-sm text-blue-600 transition hover:text-blue-400"
                                >
                                    <ExternalLink class="size-4" />
                                </a>
                                <span class="text-muted-foreground">{{ subtask.name }}</span>
                            </CollapsibleTrigger>

                            <!-- Transition for smoothly showing/hiding subtask details -->
                            <TransitionRoot
                                :show="open"
                                enter="transition ease-in-out duration-200"
                                enter-from="opacity-0"
                                enter-to="opacity-100"
                                leave="transition ease-in-out duration-200"
                                leave-from="opacity-100"
                                leave-to="opacity-0"
                            >
                                <CollapsibleContent class="ml-2 mt-2 space-y-2 border-l pl-4">
                                    <!-- Comment Section for each subtask using Comment component -->
                                    <Collapsible v-slot="{ open: commentsOpen }">
                                        <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium">
                                            <!-- Chevron icons to toggle comments visibility -->
                                            <ChevronRightIcon v-if="!commentsOpen" class="h-4 w-4" />
                                            <ChevronDownIcon v-else class="h-4 w-4" />
                                            <span>Comments</span>
                                        </CollapsibleTrigger>
                                        <TransitionRoot
                                            :show="commentsOpen"
                                            enter="transition ease-in-out duration-200"
                                            enter-from="opacity-0"
                                            enter-to="opacity-100"
                                            leave="transition ease-in-out duration-200"
                                            leave-from="opacity-100"
                                            leave-to="opacity-0"
                                        >
                                            <CollapsibleContent class="ml-2 mt-2 space-y-2 border-l pl-4">
                                                <!-- Loop through comments and use the Comment component for each one -->
                                                <div v-if="subtask.comments && subtask.comments.length" class="space-y-4">
                                                    <Comment
                                                        v-for="comment in subtask.comments"
                                                        :key="comment.id"
                                                        :comment="comment"
                                                        :canDeleteComment="props.can.deleteComment"
                                                    />
                                                </div>
                                                <div v-else class="text-sm text-muted-foreground">No comments yet.</div>
                                            </CollapsibleContent>
                                        </TransitionRoot>
                                    </Collapsible>
                                </CollapsibleContent>
                            </TransitionRoot>
                        </Collapsible>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
