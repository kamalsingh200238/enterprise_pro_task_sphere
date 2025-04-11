<script setup lang="ts">
import FormError from '@/components/FormError.vue';
import RoleSelect from '@/components/RoleSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

type FormType = {
    name: string;
    email: string;
    password: string;
    role: string;
};

const form = useForm<FormType>({
    name: '',
    email: '',
    password: '',
    role: '',
});

const submit = () => {
    // submit form
    form.post(route('users.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

// breadcrumb for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create User',
        href: '/users/new',
    },
];
</script>

<template>
    <!-- set page title -->
    <Head title="Create New User" />

    <!-- wrap page content inside main layout with breadcrumbs -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <!-- header section with page title and top-right submit button -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">Create New User</h1>
                <!-- button to submit form (top-right on desktop) -->
                <Button type="submit" :disabled="form.processing" @click="submit"> Create User </Button>
            </div>

            <form @submit.prevent="submit">
                <!-- simple form with single column -->
                <div>
                    <div class="w-full rounded-md border shadow-sm">
                        <div class="space-y-6 p-6">
                            <!-- input for user name -->
                            <div>
                                <Label for="name">User Name</Label>
                                <Input id="name" type="text" v-model="form.name" class="mt-1" />
                                <FormError :err="form.errors.name" />
                            </div>

                            <!-- input for user email -->
                            <div>
                                <Label for="email">User Email</Label>
                                <Input id="email" type="email" v-model="form.email" class="mt-1" />
                                <FormError :err="form.errors.email" />
                            </div>

                            <!-- input for password -->
                            <div>
                                <Label for="password">Password</Label>
                                <Input id="password" type="password" v-model="form.password" class="mt-1" />
                                <FormError :err="form.errors.password" />
                            </div>

                            <!-- select role -->
                            <div>
                                <Label for="role">Select Role</Label>
                                <RoleSelect id="role" v-model="form.role" :disabled="false" class="mt-1" />
                                <FormError :err="form.errors.role" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- submit button for mobile users (shown only on small screens) -->
                <div class="mt-6 flex justify-center sm:hidden">
                    <Button type="submit" :disabled="form.processing" class="max-w-xs"> Create User </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
