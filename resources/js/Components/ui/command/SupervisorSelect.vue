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
import { cn } from '@/lib/utils';
import { User } from '@/types';
import { CheckIcon, XIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    users: User[];
}

const props = defineProps<Props>();
const searchTerm = ref('');
const open = ref(false);
const selectedUserId = ref<number | null>(null);

const selectedUser = computed(() =>
    props.users.find((user) => user.id === selectedUserId.value),
);

const filteredPeople = computed(() => {
    return props.users.filter(
        (user) =>
            user.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            user.email?.toLowerCase().includes(searchTerm.value.toLowerCase()),
    );
});

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const clearSelection = (e: Event) => {
    e.stopPropagation();
    selectedUserId.value = null;
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="
                    cn(
                        'h-10 w-full justify-between',
                        !selectedUser && 'text-muted-foreground',
                    )
                "
            >
                <div class="flex items-center gap-3">
                    <div
                        v-if="selectedUser"
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-muted text-muted-foreground"
                    >
                        <span class="text-xs font-medium">
                            {{
                                selectedUser.name
                                    ? getInitials(selectedUser.name)
                                    : ''
                            }}
                        </span>
                    </div>
                    <span v-if="selectedUser">{{ selectedUser.name }}</span>
                    <span v-else>Select user...</span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        v-if="selectedUser"
                        @click="clearSelection"
                        class="rounded-full p-1 hover:bg-muted"
                    >
                        <XIcon class="h-4 w-4" />
                    </button>
                </div>
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-[400px] p-0" align="start">
            <Command v-model:search-term="searchTerm">
                <CommandInput
                    v-model="searchTerm"
                    placeholder="Search users..."
                    class="h-11"
                />
                <CommandList>
                    <CommandEmpty class="py-6 text-center text-sm">
                        No users found.
                    </CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="user in filteredPeople"
                            :key="user.id"
                            :value="user"
                            @select="
                                () => {
                                    selectedUserId = user.id;
                                    open = false;
                                }
                            "
                            class="px-4 py-3 focus:bg-accent"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <!-- Selection indicator -->
                                <div
                                    :class="
                                        cn(
                                            'flex h-5 w-5 items-center justify-center rounded-full border border-primary transition-colors',
                                            user.id === selectedUserId
                                                ? 'bg-primary text-primary-foreground'
                                                : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon :class="cn('h-4 w-4')" />
                                </div>

                                <!-- Avatar -->
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-muted text-muted-foreground"
                                    :class="
                                        user.id === selectedUserId
                                            ? 'bg-primary/10'
                                            : ''
                                    "
                                >
                                    <span class="text-sm font-medium">
                                        {{ getInitials(user.name) }}
                                    </span>
                                </div>

                                <!-- User Info -->
                                <div class="flex min-w-0 flex-col">
                                    <span
                                        class="text-sm font-medium leading-none"
                                    >
                                        {{ user.name }}
                                    </span>
                                    <span
                                        class="truncate text-sm text-muted-foreground"
                                    >
                                        {{ user.email }}
                                    </span>
                                </div>

                                <!-- Selected Badge -->
                                <Badge
                                    v-if="user.id === selectedUserId"
                                    variant="secondary"
                                    class="ml-auto text-xs"
                                >
                                    Selected
                                </Badge>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
