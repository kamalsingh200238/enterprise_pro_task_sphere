<script setup lang="ts">
import BaseLayout from '@/Layouts/BaseLayout.vue';
import { Project } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    projects: Project[];
}

function projectLink(id: number) {
    return `/projects/${id}`;
}

const props = defineProps<Props>();
</script>
<template>
    <BaseLayout> all projects page </BaseLayout>
    <div v-for="project in projects" :key="project.id">
        <Link :href="projectLink(project.id)">
            <p>Project name: {{ project.name }}</p>
            <p>Project slug: {{ project.slug }}</p>
        </Link>
    </div>
</template>
