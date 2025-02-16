<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/Components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/Components/ui/popover';
import { cn } from '@/lib/utils';
import { User } from '@/types';
import { computed, ref } from 'vue';

import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { Avatar, AvatarFallback } from './ui/avatar';

interface Props {
    users: User[];
    disabled: boolean;
    buttonClass?: string;
}

const props = defineProps<Props>();
const selectedUser = defineModel<number | null>({
    required: true,
    default: null,
});
const searchTerm = ref('');

const filteredPeople = computed(() => {
    const filtered = props.users.filter(
        (user) =>
            user.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            user.email?.toLowerCase().includes(searchTerm.value.toLowerCase()),
    );

    return filtered.sort((a, b) => {
        const aSelected = selectedUser.value === a.id;
        const bSelected = selectedUser.value === b.id;
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

const isUserSelected = (userId: number) => selectedUser.value === userId;

const selectUser = (userId: number) => {
    selectedUser.value = selectedUser.value === userId ? null : userId;
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const selectedUserData = computed(() =>
    props.users.find((user) => user.id === selectedUser.value),
);
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="cn('justify-between', props.buttonClass)"
            >
                <span>
                    <template v-if="selectedUserData">
                        {{ selectedUserData.name }}
                    </template>
                    <template v-else> Select user </template>
                </span>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-96 p-0" align="start">
            <Command
                v-model:search-term="searchTerm"
                :disabled="props.disabled"
            >
                <CommandInput
                    v-model="searchTerm"
                    placeholder="Search supervisors..."
                />
                <CommandList>
                    <CommandEmpty>No supervisors found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="user in filteredPeople"
                            :key="user.id"
                            :value="user"
                            @select="() => selectUser(user.id)"
                            :disabled="props.disabled"
                        >
                            <div class="flex flex-1 items-center gap-3">
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
                                    <AvatarFallback>
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
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
