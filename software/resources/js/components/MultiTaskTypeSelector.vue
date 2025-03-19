<script setup lang="ts">
import { taskTypes } from '@/lib/taskTypes';
import { cn } from '@/lib/utils';
import { CheckIcon, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from './ui/command';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Separator } from './ui/separator';

interface Props {
    disabled?: boolean;
    buttonClass?: string;
}

withDefaults(defineProps<Props>(), {
    disabled: false,
});

const modelValue = defineModel<string[]>({ required: true, default: [] });
const searchQuery = ref('');

const filteredTaskTypes = computed(() => {
    return taskTypes.filter((t) => t.label.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const toggleType = (value: string) => {
    const index = modelValue.value.indexOf(value);
    if (index === -1) {
        modelValue.value.push(value);
    } else {
        modelValue.value.splice(index, 1);
    }
    searchQuery.value = '';
};
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('min-w-80 justify-between text-left', buttonClass)" :disabled="disabled">
                <span class="truncate">Select Task Type</span>
                <div class="ml-2 flex h-full items-center gap-2">
                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                    <template v-if="modelValue.length > 0">
                        <Separator orientation="vertical" class="mx-2" />
                        <Badge variant="secondary" class="rounded-sm px-1 font-normal"> {{ modelValue.length }} selected </Badge>
                    </template>
                </div>
            </Button>
        </PopoverTrigger>

        <PopoverContent class="min-w-80 p-0" align="start">
            <Command :disabled="disabled">
                <CommandInput v-model="searchQuery" placeholder="Search Task Type..." />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="t in filteredTaskTypes"
                            :key="t.value"
                            :value="t.value"
                            :disabled="disabled"
                            @select.prevent="toggleType(t.value)"
                        >
                            <div class="flex flex-1 items-center gap-3">
                                <div
                                    :class="
                                        cn(
                                            'flex h-4 w-4 items-center justify-center rounded-md border border-primary transition-colors',
                                            modelValue.includes(t.value) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                        )
                                    "
                                >
                                    <CheckIcon class="h-4 w-4" />
                                </div>
                                <div>{{ t.label }}</div>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
