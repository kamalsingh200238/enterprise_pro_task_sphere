<script setup lang="ts">
import { Badge } from '@/Components/ui/badge';
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
import { Separator } from '@/Components/ui/separator';
import { cn } from '@/lib/utils';
import { User } from '@/types';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Avatar, AvatarFallback } from './ui/avatar';

interface Props {
    users: User[];
    assigneeIds: number[];
    buttonClass?: string;
}

const props = defineProps<Props>();
const selectedViewers = defineModel<number[]>({ required: true, default: [] });
const searchTerm = ref('');

watch(
    () => props.assigneeIds,
    (newAssigneeIds) => {
        selectedViewers.value = selectedViewers.value.filter(
            (viewerId) => !newAssigneeIds.includes(viewerId),
        );
    },
    { deep: true },
);

const availableUsers = computed(() => {
    return props.users.filter((user) => !props.assigneeIds.includes(user.id));
});

const filteredPeople = computed(() => {
    const filtered = availableUsers.value.filter(
        (user) =>
            user.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            user.email?.toLowerCase().includes(searchTerm.value.toLowerCase()),
    );

    return filtered.sort((a, b) => {
        const aSelected = selectedViewers.value.includes(a.id);
        const bSelected = selectedViewers.value.includes(b.id);
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

const isUserSelected = (userId: number) =>
    selectedViewers.value.includes(userId);

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
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" :class="buttonClass">
                Add viewers
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                <template v-if="selectedViewers.length > 0">
                    <Separator orientation="vertical" class="mx-2" />
                    <Badge variant="secondary">
                        {{ selectedViewers.length }} selected
                    </Badge>
                </template>
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-96 p-0" align="start">
            <Command v-model:search-term="searchTerm">
                <CommandInput
                    v-model="searchTerm"
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
