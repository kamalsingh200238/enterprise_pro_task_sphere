<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { statusIcons } from '@/lib/statusIcons';
import { cn } from '@/lib/utils';
import { Status } from '@/types';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from './ui/badge';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from './ui/command';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Separator } from './ui/separator';

// component props definition
interface Props {
    statuses: Status[];
    disabled?: boolean;
    buttonClass?: string;
}

// set default values for optional props
const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

// two-way binding for selected status ids
const selectedStatusIds = defineModel<number[]>({ required: true });

// search input state
const searchQuery = ref('');

// filter statuses based on search query
const filteredStatuses = computed(() => {
    return props.statuses.filter((status) => status.name?.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

// check if a status is currently selected
const isStatusSelected = (statusId: number) => selectedStatusIds.value.includes(statusId);

// toggle status selection state
const toggleStatus = (statusId: number) => {
    const index = selectedStatusIds.value.indexOf(statusId);
    if (index === -1) {
        selectedStatusIds.value.push(statusId);
    } else {
        selectedStatusIds.value.splice(index, 1);
    }
    searchQuery.value = '';
};

// get icon component for status if available
const getStatusIcon = (statusName: Status['name']) => {
    return statusName && statusIcons[statusName] ? statusIcons[statusName] : null;
};
</script>

<template>
    <Popover>
        <!-- trigger button for the dropdown -->
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('min-w-80 justify-between text-left', buttonClass)" :disabled="disabled">
                <span class="truncate">Select Status</span>
                <div class="ml-2 flex h-full items-center gap-2">
                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                    <!-- show badge with count when items selected -->
                    <template v-if="selectedStatusIds.length > 0">
                        <Separator orientation="vertical" class="mx-2" />
                        <Badge variant="secondary" class="rounded-sm px-1 font-normal"> {{ selectedStatusIds.length }} selected </Badge>
                    </template>
                </div>
            </Button>
        </PopoverTrigger>

        <!-- dropdown content -->
        <PopoverContent class="min-w-80 p-0" align="start">
            <Command :disabled="disabled">
                <CommandInput v-model="searchQuery" placeholder="Search Status..." />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <!-- status items with checkboxes -->
                        <CommandItem
                            v-for="status in filteredStatuses"
                            :key="status.id"
                            :value="status.id.toString()"
                            :disabled="disabled"
                            @select.prevent="toggleStatus(status.id)"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- custom checkbox design -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            isStatusSelected(status.id) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon class="h-3 w-3" />
                                </div>
                                <!-- status icon if available -->
                                <component v-if="getStatusIcon(status.name)" :is="getStatusIcon(status.name)" class="h-4 w-4 text-muted-foreground" />
                                <span>{{ status.name }}</span>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
