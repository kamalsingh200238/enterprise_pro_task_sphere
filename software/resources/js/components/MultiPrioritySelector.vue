<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Priority } from '@/types';
import { CheckIcon, PlusCircleIcon, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from './ui/badge';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList, ComboboxTrigger } from './ui/combobox';
import { Separator } from './ui/separator';

interface Props {
    priorities: Priority[];
    disabled?: boolean;
    buttonClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

const selectedPriorityIds = defineModel<number[]>({ required: true });
const searchQuery = ref('');

const filteredPriorities = computed(() => {
    // filters users based on the search query
    return props.priorities.filter((status) => status.name?.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const isPrioritySelected = (priorityId: number) => selectedPriorityIds.value.includes(priorityId);

const togglePrirority = (priorityId: number) => {
    const index = selectedPriorityIds.value.indexOf(priorityId);
    if (index === -1) {
        selectedPriorityIds.value.push(priorityId);
    } else {
        selectedPriorityIds.value.splice(index, 1);
    }
    searchQuery.value = '';
};
</script>

<template>
    <Combobox by="label" :ignore-filter="true" class="w-fit" multiple>
        <ComboboxAnchor as-child>
            <ComboboxTrigger as-child>
                <Button variant="outline" :class="cn('w-fit justify-start text-left', buttonClass)" :disabled="disabled">
                    <PlusCircleIcon class="mr-2 h-4 w-4" />
                    <span class="truncate">Select Priority</span>
                    <template v-if="modelValue.length > 0">
                        <Separator orientation="vertical" class="mx-2" />
                        <div class="flex space-x-1">
                            <Badge v-if="selectedPriorityIds.length > 0" variant="secondary" class="rounded-sm px-1 font-normal">
                                {{ selectedPriorityIds.length }} selected
                            </Badge>
                        </div>
                    </template>
                </Button>
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxList class="min-w-96" align="start">
            <div class="relative w-full max-w-sm items-center">
                <ComboboxInput
                    class="h-10 rounded-none border-0 border-b pl-9 focus-visible:ring-0"
                    placeholder="Search Status"
                    v-model="searchQuery"
                    :disabled="disabled"
                />
                <span class="absolute inset-y-0 start-0 flex items-center justify-center px-3">
                    <Search class="size-4 text-muted-foreground" />
                </span>
            </div>

            <ComboboxEmpty>No results found.</ComboboxEmpty>

            <ComboboxGroup>
                <ComboboxItem
                    v-for="status in filteredPriorities"
                    :key="status.id"
                    :value="status"
                    @select="() => togglePrirority(status.id)"
                    :disabled="disabled"
                >
                    <div class="flex flex-1 items-center gap-3">
                        <div
                            :class="
                                cn(
                                    'flex h-4 w-4 items-center justify-center rounded-sm border border-primary transition-colors',
                                    isPrioritySelected(status.id) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                )
                            "
                        >
                            <CheckIcon class="h-3 w-3" />
                        </div>
                        <span>{{ status.name }}</span>
                    </div>
                </ComboboxItem>
            </ComboboxGroup>
        </ComboboxList>
    </Combobox>
</template>
