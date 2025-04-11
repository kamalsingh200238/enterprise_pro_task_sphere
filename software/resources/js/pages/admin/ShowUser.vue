<script setup lang="ts">
import FormError from '@/components/FormError.vue';
import RoleSelect from '@/components/RoleSelect.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, User } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ user: User }>();
const isEditMode = ref(false);

type FormType = {
    name: string;
    email: string;
    password: string;
    role: string;
};

const form = useForm<FormType>({
    name: props.user.name,
    email: props.user.email,
    password: '',
    role: props.user.role,
});

const submit = () => {
    form.put(route('users.edit', props.user.id), {
        onSuccess: () => {
            isEditMode.value = false; // Exit edit mode after successful submission
        },
    });
};

// breadcrumb for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'All Users',
        href: '/users',
    },
    {
        title: 'User Details',
        href: `/users/${props.user.id}`,
    },
];
</script>

<template>
    <!-- set page title -->
    <Head title="User Details" />

    <!-- wrap page content inside main layout -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <!-- header section with page title and action buttons -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">User Details</h1>

                <!-- Action Buttons: Edit, Save, Cancel, and Delete -->
                <div class="flex flex-wrap gap-2">
                    <!-- Edit Button (only visible if not in edit mode) -->
                    <Button v-if="!isEditMode" @click="isEditMode = true"> Edit User </Button>

                    <!-- Save Button (only visible when in edit mode and the form is not processing) -->
                    <Button v-if="isEditMode" type="submit" :disabled="form.processing" @click="submit"> Save Changes </Button>

                    <!-- Cancel Button (only visible when in edit mode) -->
                    <Button v-if="isEditMode" variant="outline" @click="isEditMode = false"> Cancel </Button>

                    <!-- Delete User Alert Dialog (visible when not in edit mode) -->
                    <AlertDialog v-if="!isEditMode">
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">Delete User</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    This action cannot be undone. This will permanently delete the user account and all associated data.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <!-- Cancel the deletion -->
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <!-- Confirm the deletion with a destructive action -->
                                <AlertDialogAction as-child>
                                    <Button as-child variant="destructive">
                                        <Link method="delete" :href="route('users.delete', props.user.id)"> Delete </Link>
                                    </Button>
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <form @submit.prevent="submit">
                <!-- simple form with single column -->
                <div>
                    <div class="w-full rounded-md border shadow-sm">
                        <div class="space-y-6 p-6">
                            <!-- input for user name -->
                            <div>
                                <Label for="name">User Name</Label>
                                <Input id="name" type="text" v-model="form.name" :disabled="!isEditMode" class="mt-1" />
                                <FormError :err="form.errors.name" />
                            </div>

                            <!-- input for user email -->
                            <div>
                                <Label for="email">User Email</Label>
                                <Input id="email" type="email" v-model="form.email" :disabled="!isEditMode" class="mt-1" />
                                <FormError :err="form.errors.email" />
                            </div>

                            <!-- input for password (only visible in edit mode) -->
                            <div v-if="isEditMode">
                                <Label for="password">Password</Label>
                                <Input id="password" type="password" v-model="form.password" class="mt-1" />
                                <FormError :err="form.errors.password" />
                            </div>

                            <!-- select role -->
                            <div>
                                <Label for="role">Role</Label>
                                <RoleSelect id="role" v-model="form.role" :disabled="!isEditMode" class="mt-1" />
                                <FormError :err="form.errors.role" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Submit Button (visible on small screens when in edit mode) -->
                <div v-if="isEditMode" class="mt-6 flex justify-center sm:hidden">
                    <Button type="submit" :disabled="form.processing" class="max-w-xs"> Save Changes </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
