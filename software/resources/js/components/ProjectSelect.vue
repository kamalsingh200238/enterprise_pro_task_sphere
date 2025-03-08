<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Project } from '@/types';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    projects: Project[]; // List of users (supervisors and admins)
    disabled: boolean; // Disable selection feature if true
    buttonClass?: string; // Optional class for styling the button
}

const props = defineProps<Props>();
// Selected project state (ID of selected project or null if none is selected)
const selectedProject = defineModel<number | null>({
    required: true,
    default: null,
});
// Search query for filtering users
const searchQuery = ref('');

// Computed property to filter and sort the project list
// these projects will be displayed in the dropdown
const filteredProjects = computed(() => {
    const filtered = props.projects.filter(
        (project) =>
            project.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            project.slug?.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );

    // Sort to ensure the selected project appears first in the list
    return filtered.sort((a, b) => {
        const aSelected = selectedProject.value === a.id;
        const bSelected = selectedProject.value === b.id;
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

// Function to check if a project is currently selected
const isProjectSelected = (userId: number) => selectedProject.value === userId;

// Function to toggle project selection
const selectProject = (projectId: number) => {
    selectedProject.value = selectedProject.value === projectId ? null : projectId;
};

// Computed property to get details of the selected user
const selectedProjectData = computed(() => props.projects.find((project) => project.id === selectedProject.value));
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <!-- button that shows the dropdown with user name -->
            <Button variant="outline" :class="cn('justify-between', props.buttonClass)">
                <span>
                    <!-- if user is selected then show user data -->
                    <template v-if="selectedProjectData">
                        {{ selectedProjectData.name }}
                    </template>
                    <template v-else> Select project </template>
                </span>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-96 p-0" align="start">
            <!-- dropdown with search and users -->
            <Command v-model:search-term="searchQuery" :disabled="props.disabled">
                <CommandInput v-model="searchQuery" placeholder="Search projects..." />
                <CommandList>
                    <CommandEmpty>No projects found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="project in filteredProjects"
                            :key="project.id"
                            :value="project"
                            @select="() => selectProject(project.id)"
                            :disabled="props.disabled"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- show a check if user is selected -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            isProjectSelected(project.id) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon :class="cn('h-4 w-4')" />
                                </div>

                                <div class="flex min-w-0 flex-1 flex-col">
                                    <span class="text-sm font-medium leading-none">
                                        {{ project.slug }}
                                    </span>
                                    <span class="text-sm text-muted-foreground">
                                        {{ project.name }}
                                    </span>
                                </div>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
