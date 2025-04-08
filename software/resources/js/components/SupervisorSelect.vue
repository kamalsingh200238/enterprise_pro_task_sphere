<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { User } from '@/types';
import { computed, ref } from 'vue';

import { useInitials } from '@/composables/useInitials';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { Avatar, AvatarFallback } from './ui/avatar';

interface Props {
    superisorsAndAdmins: User[]; // List of users (supervisors and admins)
    disabled: boolean; // Disable selection feature if true
    buttonClass?: string; // Optional class for styling the button
}

const props = defineProps<Props>();
// Selected user state (ID of selected user or null if none is selected)
const selectedUser = defineModel<number | null>({
    required: true,
    default: null,
});
// Search query for filtering users
const searchQuery = ref('');
const open = ref(false);

// Computed property to filter and sort the user list
// these users will be displayed in the dropdown
const filteredPeople = computed(() => {
    const filtered = props.superisorsAndAdmins.filter(
        (user) =>
            user.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) || user.email?.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );

    // Sort to ensure the selected user appears first in the list
    return filtered.sort((a, b) => {
        const aSelected = selectedUser.value === a.id;
        const bSelected = selectedUser.value === b.id;
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

// Function to check if a user is currently selected
const isUserSelected = (userId: number) => selectedUser.value === userId;

// Function to toggle user selection
const selectUser = (userId: number) => {
    selectedUser.value = selectedUser.value === userId ? null : userId;
    open.value = false;
    searchQuery.value = '';
};

const { getInitials } = useInitials();

// Computed property to get details of the selected user
const selectedUserData = computed(() => props.superisorsAndAdmins.find((user) => user.id === selectedUser.value));
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <!-- button that shows the dropdown with user name -->
            <Button variant="outline" :class="cn('justify-between', props.buttonClass)">
                <span>
                    <!-- if user is selected then show user data -->
                    <template v-if="selectedUserData">
                        {{ selectedUserData.name }}
                    </template>
                    <template v-else> Select user </template>
                </span>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-96 p-0" align="start">
            <!-- dropdown with search and users -->
            <Command :disabled="props.disabled">
                <CommandInput v-model="searchQuery" placeholder="Search supervisors..." />
                <CommandList>
                    <CommandEmpty>No supervisors found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="user in filteredPeople"
                            :key="user.id"
                            :value="user"
                            @select.prevent="() => selectUser(user.id)"
                            :disabled="props.disabled"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- show a check if user is selected -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            isUserSelected(user.id) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon :class="cn('h-4 w-4')" />
                                </div>

                                <Avatar>
                                    <AvatarFallback>
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>

                                <div class="flex min-w-0 flex-1 flex-col">
                                    <span class="text-sm font-medium leading-none">
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
