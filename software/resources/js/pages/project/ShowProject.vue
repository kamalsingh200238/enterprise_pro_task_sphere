<script setup lang="ts">
import AssigneeSelector from '@/components/AssigneeSelector.vue';
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
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ViewerSelector from '@/components/ViewerSelector.vue';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import { Comment, Priority, Project, Status, User } from '@/types';
import { Link, router, useForm } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import { ref } from 'vue';

interface Props {
    can: {
        edit: boolean;
        updateStatus: boolean;
        updateStatusToDone: boolean;
        deleteComment: boolean;
    };
    project: Project;
    statuses: Status[];
    priorities: Priority[];
    users: User[];
    supervisorsAndAdmins: User[];
    comments: Comment[];
}

const props = defineProps<Props>();
const isEditMode = ref(false);

console.log(props);

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

const commentForm = useForm({
    content: '',
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

// Submit comment
const submitComment = () => {
    commentForm.post(route('projects.add-comment', props.project.id), {
        onSuccess: () => {
            // Reset form after successful submission
            commentForm.content = '';
        },
        preserveScroll: true,
    });
};

const { getInitials } = useInitials();
</script>

<template>
    <AppLayout>
        <main class="mx-auto max-w-7xl p-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ project.slug }}</h1>
                <div>
                    <Button v-if="!isEditMode && props.can.edit" @click="isEditMode = true"> Edit Project </Button>
                    <Button form="edit-form" v-if="isEditMode" type="submit" :disabled="form.processing"> Save Changes </Button>
                    <Button v-if="isEditMode" as-child>
                        <Link :href="route('projects.show', project.id)"> Cancel </Link>
                    </Button>
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive"> Delete </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle> Are you absolutely sure? </AlertDialogTitle>
                                <AlertDialogDescription>
                                    This action cannot be undone. This will permanently delete your account and remove your data from our servers.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction as-child>
                                    <Button as-child>
                                        <Link method="delete" :href="route('projects.delete', props.project.id)"> Confirm </Link>
                                    </Button>
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>
            <form id="edit-form" @submit.prevent="isEditMode ? submitEdit() : null">
                <div class="grid gap-8 lg:grid-cols-3">
                    <div class="col-span-2 space-y-6">
                        <div>
                            <Label for="name">Project Name</Label>
                            <Input id="name" type="text" v-model="form.name" :disabled="!isEditMode" />
                            <FormError :err="form.errors.name" />
                        </div>
                        <div>
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" rows="20" :disabled="!isEditMode" />
                            <FormError :err="form.errors.description" />
                        </div>
                        <form @submit.prevent="submitComment" class="mb-6">
                            <div class="space-y-3">
                                <Textarea id="update-content" v-model="commentForm.content" placeholder="Add a project update..." rows="3" />
                                <FormError :err="commentForm.errors.content" />
                                <div class="flex justify-end">
                                    <Button type="submit" :disabled="commentForm.processing || !commentForm.content"> Post Update </Button>
                                </div>
                            </div>
                        </form>
                        <div class="space-y-4">
                            <div v-if="!props.comments || props.comments.length === 0" class="py-4 text-center text-muted-foreground">
                                No updates yet. Be the first to add an update!
                            </div>

                            <div v-for="comment in props.comments" :key="comment.id" class="rounded-md border p-4">
                                <div class="flex items-start gap-3">
                                    <Avatar>
                                        <AvatarFallback>{{ getInitials(comment.user.name) }}</AvatarFallback>
                                    </Avatar>
                                    <div class="flex-1">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                            <div class="font-medium">
                                                {{ comment.user.name }}
                                            </div>
                                            <div class="text-sm text-muted-foreground">
                                                {{ format(parseISO(comment.created_at), 'EEEE, MMMM do yyyy, h:mm a') }}
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm">
                                            {{ comment.content }}
                                        </div>
                                    </div>
                                </div>
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button variant="destructive"> Delete </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle> Are you absolutely sure? </AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. This will permanently delete the comment.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction as-child>
                                                <Button as-child variant="destructive">
                                                    <Link :href="route('comments.delete', comment.id)" preserve-scroll method="delete"
                                                        >Delete comment</Link
                                                    >
                                                </Button>
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
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
                                :update-status-to-done="props.can.updateStatusToDone"
                                @update:modelValue="isEditMode ? null : toggleStatus()"
                            />
                            <FormError :err="form.errors.status_id" />
                        </div>
                        <div>
                            <Label for="priority">Priority</Label>
                            <PrioritySelect id="priority" v-model="form.priority_id" :priorities="props.priorities" :disabled="!isEditMode" />
                            <FormError :err="form.errors.priority_id" />
                        </div>
                        <div>
                            <div class="flex flex-col gap-1">
                                <Label for="start-date">Start Date</Label>
                                <DatePicker id="start-date" v-model="form.start_date" :disabled="!isEditMode" />
                            </div>
                            <FormError :err="form.errors.start_date" />
                        </div>
                        <div>
                            <div class="flex flex-col gap-1">
                                <Label for="due-date">Due Date</Label>
                                <DatePicker id="due-date" v-model="form.due_date" :disabled="!isEditMode" />
                            </div>
                            <FormError :err="form.errors.due_date" />
                        </div>
                        <div>
                            <div class="flex flex-col gap-1">
                                <Label for="supervisor-selector"> Select Supervisor </Label>
                                <SupervisorSelect
                                    id="supervisor-selector"
                                    :disabled="!isEditMode"
                                    :superisorsAndAdmins="props.supervisorsAndAdmins"
                                    v-model="form.supervisor_id"
                                />
                            </div>
                            <FormError :err="form.errors.supervisor_id" />
                        </div>
                        <div>
                            <div class="flex flex-col gap-1">
                                <Label for="assignees-selector"> Select Assignees </Label>
                                <AssigneeSelector id="assignees-selector" :users="props.users" v-model="form.assignees" :disabled="!isEditMode" />
                            </div>
                            <FormError :err="form.errors.assignees" />
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is-private" v-model="form.is_private" :disabled="!isEditMode" />
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
                                <Label for="viewers-selector"> Select Viewers </Label>
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
            </form>
            <div></div>
        </main>
    </AppLayout>
</template>
