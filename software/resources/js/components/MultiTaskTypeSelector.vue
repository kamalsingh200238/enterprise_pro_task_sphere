<script setup lang="ts">
import { taskType } from '@/lib/taskType';
import { cn } from '@/lib/utils';
import { CheckIcon, PlusCircleIcon, Search } from 'lucide-vue-next';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { Combobox, ComboboxAnchor, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList, ComboboxTrigger } from './ui/combobox';
import ComboboxEmpty from './ui/combobox/ComboboxEmpty.vue';
import { Separator } from './ui/separator';

interface Props {
    disabled?: boolean;
    buttonClass?: string;
}

withDefaults(defineProps<Props>(), {
    disabled: false,
});
const modelValue = defineModel<string[]>({ required: true });
const isTypeSelected = (value: string) => {
    return modelValue.value.includes(value);
};
</script>

<template>
    <Combobox by="label" v-model="modelValue" class="w-fit" multiple>
        <ComboboxAnchor as-child>
            <ComboboxTrigger as-child>
                <Button variant="outline" :class="cn('w-fit justify-start text-left', buttonClass)" :disabled="disabled">
                    <PlusCircleIcon class="mr-2 h-4 w-4" />
                    <span class="truncate">Task Type</span>
                    <template v-if="modelValue.length > 0">
                        <Separator orientation="vertical" class="mx-2" />
                        <div class="flex space-x-1">
                            <Badge v-if="modelValue.length > 0" variant="secondary" class="rounded-sm px-1 font-normal">
                                {{ modelValue.length }} selected
                            </Badge>
                        </div>
                    </template>
                </Button>
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxList class="min-w-60" align="start">
            <div class="relative w-full max-w-sm items-center">
                <ComboboxInput class="h-10 rounded-none border-0 border-b pl-9 focus-visible:ring-0" placeholder="Search Task Type" />
                <span class="absolute inset-y-0 start-0 flex items-center justify-center px-3">
                    <Search class="size-4 text-muted-foreground" />
                </span>
            </div>

            <ComboboxEmpty>No results found.</ComboboxEmpty>

            <ComboboxGroup>
                <ComboboxItem v-for="t in taskType" :key="t.label" :text-value="t.label" :value="t.value" :disabled="disabled">
                    <div class="flex flex-1 items-center gap-3">
                        <div
                            :class="
                                cn(
                                    'flex h-4 w-4 items-center justify-center rounded-sm border border-primary transition-colors',
                                    isTypeSelected(t.value) ? 'bg-primary text-primary-foreground' : 'opacity-50 [&_svg]:invisible',
                                )
                            "
                        >
                            <CheckIcon class="h-3 w-3" />
                        </div>
                        <span>
                            {{ t.label }}
                        </span>
                    </div>
                </ComboboxItem>
            </ComboboxGroup>
        </ComboboxList>
    </Combobox>
</template>
