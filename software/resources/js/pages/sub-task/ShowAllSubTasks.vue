<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { SubTask } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    subTasks: SubTask[];
}

function subTaskLink(id: number) {
    return `/sub-tasks/${id}`;
}

defineProps<Props>();
</script>
<template>
    <AppLayout>
        <div v-for="subTask in subTasks" :key="subTask.id">
            <Link :href="subTaskLink(subTask.id)">
                <p>Project name: {{ subTask.name }}</p>
                <p>Project slug: {{ subTask.slug }}</p>
            </Link>
        </div>
    </AppLayout>
</template>
