<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { User } from '@/types';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Avatar, AvatarFallback } from './ui/avatar';

interface Props {
    users: User[]; // List of all available users
    disabled: boolean;
    assigneeIds: number[]; // List of assigned users (these should not be selectable)
    supervisorId: number | null; // A supervisor who should also not be selectable
    buttonClass?: string; // Optional CSS class for styling the button
}

const props = defineProps<Props>();
// Create a two-way bound model for selected viewers
const selectedViewers = defineModel<number[]>({ required: true, default: [] });
// Search query for filtering users
const searchQuery = ref('');

// Compute a list of users that can be selected (i.e., those who are not assigned or supervisors)
const availableUsers = computed(() => {
    return props.users.filter(
        (user) =>
            !props.assigneeIds.includes(user.id) &&
            props.supervisorId !== user.id,
    );
});

// Computed property to filter users based on search term
// these users will be displayed in the dropdown
const filteredPeople = computed(() => {
    const filtered = availableUsers.value.filter(
        (user) =>
            user.name
                ?.toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            user.email?.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );

    // Sort to ensure the selected user appears first in the list
    return filtered.sort((a, b) => {
        const aSelected = selectedViewers.value.includes(a.id);
        const bSelected = selectedViewers.value.includes(b.id);
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

// Function to check if a user is selected
const isUserSelected = (userId: number) =>
    selectedViewers.value.includes(userId);

// Function to toggle selection state of a user
const toggleUser = (userId: number) => {
    const index = selectedViewers.value.indexOf(userId);
    if (index === -1) {
        selectedViewers.value.push(userId);
    } else {
        selectedViewers.value.splice(index, 1);
    }
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Watch for changes in assigneeIds and remove them from selectedViewers
watch(props.assigneeIds, (newAssigneeIds) => {
    selectedViewers.value = selectedViewers.value.filter(
        (viewerId) => !newAssigneeIds.includes(viewerId),
    );
});

// Watch for changes in supervisorId and remove it from selectedViewers if it was previously selected
watch(
    () => props.supervisorId,
    (newSupervisorId) => {
        selectedViewers.value = selectedViewers.value.filter(
            (viewerId) => newSupervisorId !== viewerId,
        );
    },
    { deep: true },
);
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <!-- button that display the dropdown to select viewers -->
            <Button
                variant="outline"
                :class="cn('justify-between', props.buttonClass)"
            >
                <span>Select Viewers</span>
                <div class="inline-flex items-center gap-2">
                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />

                    <template v-if="selectedViewers.length > 0">
                        <Separator orientation="vertical" class="h-4" />
                        <!-- display how many viewers are selected -->
                        <Badge variant="secondary">
                            {{ selectedViewers.length }} selected
                        </Badge>
                    </template>
                </div>
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-96 p-0" align="start">
            <!-- dropdown with search that display viewers -->
            <Command
                v-model:search-term="searchQuery"
                :disabled="props.disabled"
            >
                <CommandInput
                    v-model="searchQuery"
                    placeholder="Search users..."
                />
                <CommandList>
                    <CommandEmpty>No available users found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="user in filteredPeople"
                            :key="user.id"
                            :value="user"
                            @select="() => toggleUser(user.id)"
                            :disabled="props.disabled"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- display check if the user is selected -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            isUserSelected(user.id)
                                                ? 'bg-primary text-primary-foreground'
                                                : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon :class="cn('h-4 w-4')" />
                                </div>

                                <Avatar
                                    :class="
                                        isUserSelected(user.id)
                                            ? 'bg-primary/10'
                                            : 'bg-primary/5'
                                    "
                                >
                                    <AvatarFallback>{{
                                        getInitials(user.name)
                                    }}</AvatarFallback>
                                </Avatar>

                                <div class="flex min-w-0 flex-1 flex-col">
                                    <span
                                        class="text-sm font-medium leading-none"
                                    >
                                        {{ user.name }}
                                    </span>
                                    <span class="text-sm text-muted-foreground">
                                        {{ user.email }}
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
