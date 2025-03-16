<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import { cn } from '@/lib/utils';
import { User } from '@/types';
import { CheckIcon, ChevronsUpDown, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Avatar, AvatarFallback } from './ui/avatar';
import { Badge } from './ui/badge';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList, ComboboxTrigger } from './ui/combobox';
import { Separator } from './ui/separator';

interface Props {
    users: User[]; // list of users to be displayed in the dropdown
    disabled?: boolean; // whether the dropdown is disabled
    buttonClass?: string; // optional custom class for the button
    placeholder?: string; // placeholder text for the button
    emptyMessage?: string; // message displayed when no users are found
    searchPlaceholder?: string; // placeholder text for the search input
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    placeholder: 'Select users',
    emptyMessage: 'No users found.',
    searchPlaceholder: 'Search users...',
});

const selectedUserIds = defineModel<number[]>({ required: true, default: [] }); // stores selected user ids
const searchQuery = ref(''); // stores the search input value

const filteredUsers = computed(() => {
    // filters users based on the search query
    const filtered = props.users.filter(
        (user) =>
            user.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) || user.email?.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );

    // ensures selected users appear first in the list
    return filtered.sort((a, b) => {
        const aSelected = selectedUserIds.value.includes(a.id);
        const bSelected = selectedUserIds.value.includes(b.id);
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
});

const isUserSelected = (userId: number) => selectedUserIds.value.includes(userId); // checks if a user is selected

const toggleUser = (userId: number) => {
    // adds or removes a user from the selected list
    const index = selectedUserIds.value.indexOf(userId);
    if (index === -1) {
        selectedUserIds.value.push(userId);
    } else {
        selectedUserIds.value.splice(index, 1);
    }
    searchQuery.value = ''; // clears the search input after selection
};
</script>

<template>
    <Combobox by="label" :ignore-filter="true" class="w-fit">
        <ComboboxAnchor as-child>
            <ComboboxTrigger as-child>
                <Button variant="outline" :class="cn('min-w-80 justify-between text-left', buttonClass)" tabindex="0">
                    <span class="truncate">{{ placeholder }}</span>
                    <div class="ml-2 flex h-full items-center gap-2">
                        <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                        <template v-if="selectedUserIds.length > 0">
                            <Separator orientation="vertical" class="mx-2" />
                            <Badge variant="secondary" class="rounded-sm px-1 font-normal"> {{ selectedUserIds.length }} selected </Badge>
                        </template>
                    </div>
                </Button>
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxList class="min-w-96" align="start">
            <div class="relative w-full max-w-sm items-center">
                <ComboboxInput
                    class="h-10 rounded-none border-0 border-b pl-9 focus-visible:ring-0"
                    :placeholder="searchPlaceholder"
                    v-model="searchQuery"
                    :disabled="disabled"
                />
                <span class="absolute inset-y-0 start-0 flex items-center justify-center px-3">
                    <Search class="size-4 text-muted-foreground" />
                </span>
            </div>

            <ComboboxEmpty>{{ emptyMessage }}</ComboboxEmpty>

            <ComboboxGroup>
                <ComboboxItem
                    v-for="user in filteredUsers"
                    :key="user.id"
                    :value="user"
                    @select.prevent="() => toggleUser(user.id)"
                    :disabled="disabled"
                >
                    <div class="flex flex-1 items-center gap-3">
                        <div
                            :class="
                                cn(
                                    'flex h-4 w-4 items-center justify-center rounded-sm border border-primary transition-colors',
                                    isUserSelected(user.id) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                )
                            "
                        >
                            <CheckIcon class="h-3 w-3" />
                        </div>

                        <Avatar>
                            <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                        </Avatar>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <span class="truncate text-sm font-medium leading-none">
                                {{ user.name }}
                            </span>
                            <span class="truncate text-xs text-muted-foreground">
                                {{ user.email }}
                            </span>
                        </div>
                    </div>
                </ComboboxItem>
            </ComboboxGroup>
        </ComboboxList>
    </Combobox>
</template>
