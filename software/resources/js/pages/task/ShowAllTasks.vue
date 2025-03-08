<script setup lang="ts">
// import BaseLayout from '@/Layouts/BaseLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Task } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    tasks: Task[];
}

function projectLink(id: number) {
    return `/projects/${id}`;
}

defineProps<Props>();
</script>
<template>
    <!-- <BaseLayout> all projects page </BaseLayout> -->
    <AppLayout>
        <div v-for="task in tasks" :key="task.id">
            <Link :href="projectLink(task.id)">
                <p>Project name: {{ task.name }}</p>
                <p>Project slug: {{ task.slug }}</p>
            </Link>
        </div>
    </AppLayout>
</template>
