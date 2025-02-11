<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    statuses: Array,
    priorities: Array,
    users: Array,
});

const form = useForm({
    name: '',
    description: '',
    start_date: '',
    due_date: '',
    status_id: '',
    priority_id: '',
    is_private: false,
    supervisor_id: '',
    assignees: [],
    viewers: [],
});

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <div class="mx-auto max-w-2xl py-8">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Project Name</label
                >
                <input
                    type="text"
                    v-model="form.name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    required
                />
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                    {{ form.errors.name }}
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Description</label
                >
                <textarea
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                ></textarea>
                <div
                    v-if="form.errors.description"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.description }}
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Start Date</label
                    >
                    <input
                        type="date"
                        v-model="form.start_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    />
                    <div
                        v-if="form.errors.start_date"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.start_date }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Due Date</label
                    >
                    <input
                        type="date"
                        v-model="form.due_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    />
                    <div
                        v-if="form.errors.due_date"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.due_date }}
                    </div>
                </div>
            </div>

            <!-- Status and Priority -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Status</label
                    >
                    <select
                        v-model="form.status_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    >
                        <option value="">Select Status</option>
                        <option
                            v-for="status in statuses"
                            :key="status.id"
                            :value="status.id"
                        >
                            {{ status.name }}
                        </option>
                    </select>
                    <div
                        v-if="form.errors.status_id"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.status_id }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Priority</label
                    >
                    <select
                        v-model="form.priority_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    >
                        <option value="">Select Priority</option>
                        <option
                            v-for="priority in priorities"
                            :key="priority.id"
                            :value="priority.id"
                        >
                            {{ priority.name }}
                        </option>
                    </select>
                    <div
                        v-if="form.errors.priority_id"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.priority_id }}
                    </div>
                </div>
            </div>

            <!-- Supervisor -->
            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Supervisor</label
                >
                <select
                    v-model="form.supervisor_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    required
                >
                    <option value="">Select Supervisor</option>
                    <option
                        v-for="user in users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </option>
                </select>
                <div
                    v-if="form.errors.supervisor_id"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.supervisor_id }}
                </div>
            </div>

            <!-- Assignees -->
            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Assignees</label
                >
                <select
                    v-model="form.assignees"
                    multiple
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                >
                    <option
                        v-for="user in users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </option>
                </select>
                <div
                    v-if="form.errors.assignees"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.assignees }}
                </div>
            </div>

            <!-- Viewers -->
            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Viewers</label
                >
                <select
                    v-model="form.viewers"
                    multiple
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                >
                    <option
                        v-for="user in users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </option>
                </select>
                <div
                    v-if="form.errors.viewers"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.viewers }}
                </div>
            </div>

            <!-- Is Private -->
            <div class="flex items-center">
                <input
                    type="checkbox"
                    v-model="form.is_private"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm"
                />
                <label class="ml-2 block text-sm text-gray-700"
                    >Make Project Private</label
                >
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Create Project
                </button>
            </div>
        </form>
    </div>
</template>
