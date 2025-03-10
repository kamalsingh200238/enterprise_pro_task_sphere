<script setup lang="ts">
import FormError from '@/components/FormError.vue';
import RoleSelect from '@/components/RoleSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
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
</script>

<template>
    <Head title="Create New Project" />
    <AppLayout>
        <main class="mx-auto min-w-[65rem] max-w-7xl p-4">
            <form @submit.prevent="submit">
                <div class="col-span-2 space-y-6">
                    <div>
                        <Label for="name">User Name </Label>
                        <Input id="name" type="text" v-model="form.name" />
                        <FormError :err="form.errors.name" />
                    </div>
                    <div>
                        <Label for="email">User Eamil </Label>
                        <Input id="email" type="email" v-model="form.email" />
                        <FormError :err="form.errors.email" />
                    </div>
                    <div>
                        <Label for="password">Password</Label>
                        <Input id="password" type="password" v-model="form.password" />
                        <FormError :err="form.errors.password" />
                    </div>
                    <div>
                        <Label for="name">Select Role</Label>
                        <RoleSelect v-model="form.role" :disabled="false"/>
                        <FormError :err="form.errors.role" />
                    </div>
                </div>
                <Button type="submit" :disabled="form.processing"> Create Task </Button>
            </form>
        </main>
    </AppLayout>
</template>
