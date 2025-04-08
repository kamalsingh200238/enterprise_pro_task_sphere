<script setup lang="ts">
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
import { Comment } from '@/types';
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

// Props for the single comment
defineProps<{
    comment: Comment;
    canDeleteComment: boolean;
}>();

// Get the initials of the user who posted the comment
const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((part) => part.charAt(0))
        .join('');
};

// Convert the comment's timestamp to a human-readable format
const getReadableDate = (dateString: string) => {
    const d = new Date(dateString);
    return d.toLocaleString();
};
</script>

<template>
    <!-- Single Comment Display -->
    <div class="rounded-md border p-4">
        <div class="flex items-start gap-3">
            <!-- Avatar for the user who made the comment -->
            <Avatar>
                <AvatarFallback>{{ getInitials(comment.user.name) }}</AvatarFallback>
            </Avatar>

            <!-- Comment content area -->
            <div class="flex-1">
                <!-- User name and comment timestamp -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="font-medium">
                        {{ comment.user.name }}
                    </div>
                    <div class="text-sm">
                        {{ getReadableDate(comment.created_at) }}
                    </div>
                </div>

                <!-- Comment text content -->
                <div class="mt-2 text-sm">
                    {{ comment.content }}
                </div>
            </div>
        </div>

        <!-- Delete Comment Section (only visible if the user can delete comments) -->
        <div class="mt-3 flex justify-end" v-if="canDeleteComment">
            <!-- Alert Dialog for confirming deletion of comment -->
            <AlertDialog>
                <AlertDialogTrigger as-child>
                    <!-- Delete Button -->
                    <Button variant="destructive" size="sm">Delete</Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                        <AlertDialogDescription> This action cannot be undone. This will permanently delete the comment. </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <!-- Cancel Deletion -->
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <!-- Confirm Deletion -->
                        <AlertDialogAction as-child>
                            <Button as-child variant="destructive">
                                <Link :href="route('comments.delete', comment.id)" preserve-scroll method="delete"> Delete comment </Link>
                            </Button>
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>
</template>
