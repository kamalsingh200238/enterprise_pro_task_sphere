<script setup lang="ts">
import { taskTypes } from '@/lib/taskTypes';
import { cn } from '@/lib/utils';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from './ui/command';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Separator } from './ui/separator';

// component props definition
interface Props {
    disabled?: boolean;
    buttonClass?: string;
}

// set default values for optional props
withDefaults(defineProps<Props>(), {
    disabled: false,
});

// two-way binding for selected task types
const selectedTaskTypes = defineModel<string[]>({ required: true, default: [] });

// search input state
const searchQuery = ref('');

// filter task types based on search query
const filteredTaskTypes = computed(() => {
    return taskTypes.filter((taskType) => taskType.label.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

// toggle task type selection state
const toggleType = (value: string) => {
    const index = selectedTaskTypes.value.indexOf(value);
    if (index === -1) {
        selectedTaskTypes.value.push(value);
    } else {
        selectedTaskTypes.value.splice(index, 1);
    }
    searchQuery.value = '';
};
</script>

<template>
    <Popover>
        <!-- trigger button for the dropdown -->
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('min-w-80 justify-between text-left', buttonClass)" :disabled="disabled">
                <span class="truncate">Select Task Type</span>
                <div class="ml-2 flex h-full items-center gap-2">
                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                    <!-- show badge with count when items selected -->
                    <template v-if="selectedTaskTypes.length > 0">
                        <Separator orientation="vertical" class="mx-2" />
                        <Badge variant="secondary" class="rounded-sm px-1 font-normal"> {{ selectedTaskTypes.length }} selected </Badge>
                    </template>
                </div>
            </Button>
        </PopoverTrigger>

        <!-- dropdown content -->
        <PopoverContent class="min-w-80 p-0" align="start">
            <Command :disabled="disabled">
                <CommandInput v-model="searchQuery" placeholder="Search Task Type..." />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <!-- task type items with checkboxes -->
                        <CommandItem
                            v-for="taskType in filteredTaskTypes"
                            :key="taskType.value"
                            :value="taskType.value"
                            :disabled="disabled"
                            @select.prevent="toggleType(taskType.value)"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- custom checkbox design -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            selectedTaskTypes.includes(taskType.value)
                                                ? 'bg-primary text-primary-foreground'
                                                : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon class="h-4 w-4" />
                                </div>
                                <div>{{ taskType.label }}</div>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
