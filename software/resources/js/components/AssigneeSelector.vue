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
import { computed, ref } from 'vue';
import { Avatar, AvatarFallback } from './ui/avatar';

interface Props {
    users: User[]; // List of available users
    disabled: boolean; // Whether select is disabled
    buttonClass?: string; // Optional CSS class for button styling
}

const props = defineProps<Props>();
// Selected user state (ID of selected user or null if none is selected)
const selectedAssignees = defineModel<number[]>({ required: true, default: [] });
// Search query for filtering users
const searchQuery = ref('');

// Computed property to filter users based on search term
// these users will be displayed in the dropdown
const filteredPeople = computed(() => {
    const filtered = props.users.filter(
        (user) =>
            user.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email?.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );

    // Sort to ensure the selected user appears first in the list
    return filtered.sort((a, b) => {
        const aSelected = selectedAssignees.value.includes(a.id);
        const bSelected = selectedAssignees.value.includes(b.id);
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

// Function to check if a user is currently selected
const isUserSelected = (userId: number) => selectedAssignees.value.includes(userId);

const toggleUser = (userId: number) => {
    const index = selectedAssignees.value.indexOf(userId);
    if (index === -1) {
        selectedAssignees.value.push(userId);
    } else {
        selectedAssignees.value.splice(index, 1);
    }
};

// Function to get initials from a user's name
const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <!-- button that display the dropdown to select assignees -->
            <Button
                variant="outline"
                :class="cn('justify-between', props.buttonClass)"
            >
                <span>Select Assignees</span>
                <div class="inline-flex items-center gap-2">
                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                    <!-- display how many assignees are selected -->
                    <template v-if="selectedAssignees.length > 0">
                        <Separator orientation="vertical" class="h-4" />
                        <Badge variant="secondary">
                            {{ selectedAssignees.length }} selected
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
                    <CommandEmpty> No users found. </CommandEmpty>
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
