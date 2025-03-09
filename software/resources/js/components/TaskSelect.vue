<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Task } from '@/types';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    tasks: Task[]; // List of tasks
    disabled: boolean; // Disable selection feature if true
    buttonClass?: string; // Optional class for styling the button
}

const props = defineProps<Props>();
// Selected task state (ID of selected task or null if none is selected)
const selectedTask = defineModel<number | null>({
    required: true,
    default: null,
});
// Search query for filtering users
const searchQuery = ref('');
const open = ref(false);

// Computed property to filter and sort the task list
// these tasks will be displayed in the dropdown
const filteredTasks = computed(() => {
    const filtered = props.tasks.filter(
        (task) =>
            task.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            task.slug?.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );

    // Sort to ensure the selected task appears first in the list
    return filtered.sort((a, b) => {
        const aSelected = selectedTask.value === a.id;
        const bSelected = selectedTask.value === b.id;
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

// Function to check if a task is currently selected
const isTaskSelected = (taskId: number) => selectedTask.value === taskId;

// Function to toggle task selection
const selectTask = (taskId: number) => {
    selectedTask.value = selectedTask.value === taskId ? null : taskId;
    open.value = false;
    searchQuery.value = '';
};

// Computed property to get details of the selected user
const selectedTaskData = computed(() => props.tasks.find((task) => task.id === selectedTask.value));
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <!-- button that shows the dropdown with user name -->
            <Button variant="outline" :class="cn('justify-between', props.buttonClass)">
                <span>
                    <!-- if user is selected then show user data -->
                    <template v-if="selectedTaskData">
                        {{ selectedTaskData.slug }}
                    </template>
                    <template v-else> Select task </template>
                </span>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-96 p-0" align="start">
            <!-- dropdown with search and users -->
            <Command :disabled="props.disabled">
                <CommandInput v-model="searchQuery" placeholder="Search tasks..." />
                <CommandList>
                    <CommandEmpty>No tasks found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="task in filteredTasks"
                            :key="task.id"
                            :value="task"
                            @select.prevent="() => selectTask(task.id)"
                            :disabled="props.disabled"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- show a check if user is selected -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            isTaskSelected(task.id) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon :class="cn('h-4 w-4')" />
                                </div>

                                <div class="flex min-w-0 flex-1 flex-col">
                                    <span class="text-sm font-medium leading-none">
                                        {{ task.slug }}
                                    </span>
                                    <span class="text-sm text-muted-foreground">
                                        {{ task.name }}
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
