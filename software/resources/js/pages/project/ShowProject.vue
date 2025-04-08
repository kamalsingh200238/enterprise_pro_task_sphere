<script setup lang="ts">
// imports for components, layout, UI, and helpers
import AssigneeSelector from '@/components/AssigneeSelector.vue';
import Comment from '@/components/Comment.vue';
import DatePicker from '@/components/DatePicker.vue';
import FormError from '@/components/FormError.vue';
import PrioritySelect from '@/components/PrioritySelect.vue';
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
import { BreadcrumbItem, Priority, Project, Status, User } from '@/types';
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
    project: Project;
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
}

const props = defineProps<Props>();

// whether the form is in edit mode
const isEditMode = ref(false);

// main project form initialized with project data
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

// comment form for posting a new comment
const commentForm = useForm({
    content: '',
});

// handles project update on form submit
const submitEdit = () => {
    if (!form.is_private) {
        form.viewers = []; // remove viewers if project is not private
    }

    form.put(route('projects.edit', props.project.id), {
        onSuccess: () => {
            isEditMode.value = false; // exit edit mode after successful update
        },
    });
};

// toggles project status when not in edit mode
const toggleStatus = () => {
    if (!isEditMode.value) {
        router.put(route('projects.update-status', props.project.id), {
            status_id: form.status_id,
        });
    }
};

// posts a new comment and clears input on success
const submitComment = () => {
    commentForm.post(route('projects.add-comment', props.project.id), {
        onSuccess: () => {
            commentForm.content = '';
        },
        preserveScroll: true,
    });
};

// breadcrumb trail for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashobard',
        href: '/dashboard',
    },
    {
        title: props.project.slug,
        href: `/projects/${props.project.id}`,
    },
];
</script>

<template>
    <Head :title="project.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <!-- Project Title Section -->
                <h1 class="text-2xl font-bold">{{ project.slug }}</h1>

                <!-- Action Buttons: Edit, Save, Cancel, and Delete -->
                <div class="flex flex-wrap gap-2">
                    <!-- Edit Button (only visible if not in edit mode and the user can edit) -->
                    <Button v-if="!isEditMode && props.can.edit" @click="isEditMode = true"> Edit Project </Button>

                    <!-- Save Button (only visible when in edit mode and the form is not processing) -->
                    <Button v-if="isEditMode" type="submit" :disabled="form.processing" @click="submitEdit"> Save Changes </Button>

                    <!-- Cancel Button (only visible when in edit mode) -->
                    <Button v-if="isEditMode" variant="outline" @click="isEditMode = false"> Cancel </Button>

                    <!-- Delete Project Alert Dialog (visible when not in edit mode and user can delete) -->
                    <AlertDialog v-if="!isEditMode && can.edit">
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">Delete Project</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    This action cannot be undone. This will permanently delete the project and all associated data.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <!-- Cancel the deletion -->
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <!-- Confirm the deletion with a destructive action -->
                                <AlertDialogAction as-child>
                                    <Button as-child variant="destructive">
                                        <Link method="delete" :href="route('projects.delete', props.project.id)"> Delete </Link>
                                    </Button>
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <!-- Project Edit Form -->
            <form id="edit-form" @submit.prevent="isEditMode ? submitEdit() : null">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content - Left Side -->
                    <div class="col-span-full lg:col-span-2">
                        <div class="w-full rounded-md border shadow-sm">
                            <div class="space-y-6 p-6">
                                <!-- Project Name Field -->
                                <div>
                                    <Label for="name">Project Name</Label>
                                    <Input id="name" type="text" v-model="form.name" :disabled="!isEditMode" class="mt-1" />
                                    <FormError :err="form.errors.name" />
                                </div>

                                <!-- Project Description Field -->
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
                                <!-- Project Status Field -->
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

                                <!-- Project Priority Field -->
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

                                <!-- Private Project Checkbox -->
                                <div class="flex items-center space-x-2 pt-2">
                                    <Checkbox id="is-private" v-model="form.is_private" :disabled="!isEditMode" />
                                    <label
                                        for="is-private"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        :class="{ 'opacity-70': !isEditMode }"
                                    >
                                        Private Project
                                    </label>
                                </div>

                                <!-- Viewers Field (only visible if project is private) -->
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
                    <h2 class="mb-4 text-xl font-semibold">Project Comments</h2>

                    <!-- Comment Form (visible only if the user can comment) -->
                    <form v-if="can.comment" @submit.prevent="submitComment" class="mb-6">
                        <div class="space-y-3">
                            <Textarea id="update-content" v-model="commentForm.content" placeholder="Add a project comment..." rows="3" />
                            <FormError :err="commentForm.errors.content" />
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="commentForm.processing || !commentForm.content"> Post Update </Button>
                            </div>
                        </div>
                    </form>

                    <!-- Loop over the comments and display each one using the Comment component -->
                    <div class="space-y-4">
                        <div v-if="!project.comments || project.comments.length === 0" class="py-4 text-center text-muted-foreground">
                            No comments yet. Be the first to add a comment!
                        </div>

                        <!-- Loop through all comments and display each one using the Comment component -->
                        <Comment
                            v-for="comment in project.comments"
                            :key="comment.id"
                            :comment="comment"
                            :canDeleteComment="props.can.deleteComment"
                        />
                    </div>
                </div>
            </div>

            <!-- Project Tasks Section -->
            <div class="mt-6 w-full rounded-md border shadow-sm">
                <div class="p-6">
                    <!-- Header for Project Tasks -->
                    <h2 class="mb-4 text-xl font-semibold">Project Tasks</h2>

                    <!-- Display message if no tasks are present -->
                    <div v-if="!project.tasks?.length" class="text-sm text-muted-foreground">No tasks yet.</div>

                    <!-- Loop through the project tasks and display each task inside a collapsible container -->
                    <div v-else class="space-y-2">
                        <Collapsible v-for="task in project.tasks" :key="task.id" class="ml-2" v-slot="{ open }">
                            <!-- Trigger for expanding/collapsing each task -->
                            <CollapsibleTrigger class="flex w-full items-center gap-2 overflow-hidden">
                                <!-- Chevron icons to indicate open/closed state -->
                                <span>
                                    <ChevronRightIcon v-if="!open" class="h-4 w-4" />
                                    <ChevronDownIcon v-else class="h-4 w-4" />
                                </span>
                                <!-- Display task information like task slug and link to view the task -->
                                <span class="font-medium">{{ task.slug }} :</span>
                                <a
                                    :href="route('tasks.show', task.id)"
                                    target="_blank"
                                    class="inline-block text-sm text-blue-600 transition hover:text-blue-400"
                                >
                                    <ExternalLink class="size-4" />
                                </a>
                                <span class="text-muted-foreground">{{ task.name }}</span>
                            </CollapsibleTrigger>

                            <!-- Transition for smoothly showing/hiding task details -->
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
                                    <!-- Comment Section for each task using Comment component -->
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
                                                <div v-if="task.comments && task.comments.length" class="space-y-4">
                                                    <Comment
                                                        v-for="comment in task.comments"
                                                        :key="comment.id"
                                                        :comment="comment"
                                                        :canDeleteComment="props.can.deleteComment"
                                                    />
                                                </div>
                                                <div v-else class="text-sm text-muted-foreground">No comments yet.</div>
                                            </CollapsibleContent>
                                        </TransitionRoot>
                                    </Collapsible>

                                    <!-- Display Sub-Tasks Section if available -->
                                    <Collapsible v-if="task.subtasks && task.subtasks.length" v-slot="{ open: subtasksOpen }">
                                        <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium">
                                            <!-- Chevron icons to toggle sub-tasks visibility -->
                                            <ChevronRightIcon v-if="!subtasksOpen" class="h-4 w-4" />
                                            <ChevronDownIcon v-else class="h-4 w-4" />
                                            <span>Sub Tasks</span>
                                        </CollapsibleTrigger>
                                        <TransitionRoot
                                            :show="subtasksOpen"
                                            enter="transition ease-in-out duration-200"
                                            enter-from="opacity-0"
                                            enter-to="opacity-100"
                                            leave="transition ease-in-out duration-200"
                                            leave-from="opacity-100"
                                            leave-to="opacity-0"
                                        >
                                            <CollapsibleContent class="ml-2 mt-2 space-y-2 border-l pl-4">
                                                <!-- Loop through subtasks and display each one -->
                                                <div v-for="sub in task.subtasks" :key="sub.id">
                                                    <Collapsible v-slot="{ open: subOpen }">
                                                        <CollapsibleTrigger
                                                            class="flex w-full items-center gap-2 overflow-hidden text-sm font-medium"
                                                        >
                                                            <!-- Chevron icons for subtask collapse/expand -->
                                                            <ChevronRightIcon v-if="!subOpen" class="h-4 w-4" />
                                                            <ChevronDownIcon v-else class="h-4 w-4" />
                                                            <span class="font-semibold">{{ sub.slug }} :</span>
                                                            <a
                                                                :href="route('sub-tasks.show', sub.id)"
                                                                target="_blank"
                                                                class="text-sm text-blue-600 hover:text-blue-400"
                                                            >
                                                                <ExternalLink class="size-4" />
                                                            </a>
                                                            <span class="text-muted-foreground">{{ sub.name }}</span>
                                                        </CollapsibleTrigger>
                                                        <TransitionRoot
                                                            :show="subOpen"
                                                            enter="transition ease-in-out duration-200"
                                                            enter-from="opacity-0"
                                                            enter-to="opacity-100"
                                                            leave="transition ease-in-out duration-200"
                                                            leave-from="opacity-100"
                                                            leave-to="opacity-0"
                                                        >
                                                            <CollapsibleContent class="ml-2 mt-2 space-y-2 border-l pl-4">
                                                                <!-- Subtask comments section using Comment component -->
                                                                <Collapsible v-slot="{ open: subCommentsOpen }">
                                                                    <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium">
                                                                        <!-- Chevron icons for subtask comments collapse/expand -->
                                                                        <ChevronRightIcon v-if="!subCommentsOpen" class="h-4 w-4" />
                                                                        <ChevronDownIcon v-else class="h-4 w-4" />
                                                                        <span>Comments</span>
                                                                    </CollapsibleTrigger>
                                                                    <TransitionRoot
                                                                        :show="subCommentsOpen"
                                                                        enter="transition ease-in-out duration-200"
                                                                        enter-from="opacity-0"
                                                                        enter-to="opacity-100"
                                                                        leave="transition ease-in-out duration-200"
                                                                        leave-from="opacity-100"
                                                                        leave-to="opacity-0"
                                                                    >
                                                                        <CollapsibleContent class="ml-2 mt-2 space-y-2 border-l pl-4">
                                                                            <!-- Loop through subtask comments using Comment component -->
                                                                            <div v-if="sub.comments && sub.comments.length" class="space-y-4">
                                                                                <Comment
                                                                                    v-for="comment in sub.comments"
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
