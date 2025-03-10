<script setup lang="ts">
import FormError from '@/components/FormError.vue';
import RoleSelect from '@/components/RoleSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { User } from '@/types';
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
</script>

<template>
    <Head title="Edit User" />
    <AppLayout>
        <main class="mx-auto min-w-[65rem] max-w-7xl p-4">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-bold">User Details</h1>
                <div class="space-x-2">
                    <Button v-if="!isEditMode" @click="isEditMode = true" variant="outline">Edit</Button>
                </div>
            </div>
            <form @submit.prevent="submit">
                <div class="col-span-2 space-y-6">
                    <div>
                        <Label for="name">User Name</Label>
                        <Input id="name" type="text" v-model="form.name" :disabled="!isEditMode" />
                        <FormError :err="form.errors.name" />
                    </div>
                    <div>
                        <Label for="email">User Email</Label>
                        <Input id="email" type="email" v-model="form.email" :disabled="!isEditMode" />
                        <FormError :err="form.errors.email" />
                    </div>
                    <div>
                        <Label for="password">Password</Label>
                        <Input id="password" type="password" v-model="form.password" :disabled="!isEditMode" />
                        <FormError :err="form.errors.password" />
                    </div>
                    <div>
                        <Label for="role">Select Role</Label>
                        <RoleSelect v-model="form.role" :disabled="!isEditMode" />
                        <FormError :err="form.errors.role" />
                    </div>
                </div>
                <div class="mt-4 space-x-2">
                    <Button type="submit" :disabled="form.processing || !isEditMode">Save Changes</Button>
                    <Button v-if="isEditMode" variant="outline" as-child>
                        <Link :href="route('users.show', props.user.id)">Cancel</Link>
                    </Button>
                    <Button variant="destructive" as-child>
                        <Link method="delete" :href="route('users.delete', props.user.id)">Delete</Link>
                    </Button>
                </div>
            </form>
        </main>
    </AppLayout>
</template>
