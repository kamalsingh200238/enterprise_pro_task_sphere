<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { priorityIcons } from '@/lib/priorityIcons';
import { cn } from '@/lib/utils';
import { Priority } from '@/types';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from './ui/badge';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from './ui/command';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Separator } from './ui/separator';

// component props definition
interface Props {
    priorities: Priority[];
    disabled?: boolean;
    buttonClass?: string;
}

// set default values for optional props
const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

// two-way binding for selected priority ids
const selectedPriorityIds = defineModel<number[]>({ required: true });

// search input state
const searchQuery = ref('');

// filter priorities based on search query
const filteredPriorities = computed(() => {
    return props.priorities.filter((priority) => priority.name?.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

// check if a priority is currently selected
const isPrioritySelected = (priorityId: number) => selectedPriorityIds.value.includes(priorityId);

// toggle priority selection state
const togglePriority = (priorityId: number) => {
    const index = selectedPriorityIds.value.indexOf(priorityId);
    if (index === -1) {
        selectedPriorityIds.value.push(priorityId);
    } else {
        selectedPriorityIds.value.splice(index, 1);
    }
    searchQuery.value = '';
};

// get icon component for priority if available
const getPriorityIcon = (priorityName: Priority['name']) => {
    return priorityName && priorityIcons[priorityName] ? priorityIcons[priorityName] : null;
};
</script>

<template>
    <Popover>
        <!-- trigger button for the dropdown -->
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('min-w-80 justify-between text-left', buttonClass)" :disabled="disabled">
                <span class="truncate">Select Priority</span>
                <div class="ml-2 flex h-full items-center gap-2">
                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                    <!-- show badge with count when items selected -->
                    <template v-if="selectedPriorityIds.length > 0">
                        <Separator orientation="vertical" class="mx-2" />
                        <Badge variant="secondary" class="rounded-sm px-1 font-normal"> {{ selectedPriorityIds.length }} selected </Badge>
                    </template>
                </div>
            </Button>
        </PopoverTrigger>

        <!-- dropdown content -->
        <PopoverContent class="min-w-80 p-0" align="start">
            <Command :disabled="disabled">
                <CommandInput v-model="searchQuery" placeholder="Search Priority..." />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <!-- priority items with checkboxes -->
                        <CommandItem
                            v-for="priority in filteredPriorities"
                            :key="priority.id"
                            :value="priority.id.toString()"
                            :disabled="disabled"
                            @select.prevent="togglePriority(priority.id)"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- custom checkbox design -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            isPrioritySelected(priority.id) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon class="h-4 w-4" />
                                </div>
                                <!-- priority icon if available -->
                                <component
                                    v-if="getPriorityIcon(priority.name)"
                                    :is="getPriorityIcon(priority.name)"
                                    class="h-4 w-4 text-muted-foreground"
                                />
                                <span>{{ priority.name }}</span>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
