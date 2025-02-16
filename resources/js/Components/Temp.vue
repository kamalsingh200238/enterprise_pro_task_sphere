<script setup lang="ts">
import { Icon } from '@iconify/vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxLabel,
    ComboboxRoot,
    ComboboxSeparator,
    ComboboxTrigger,
    ComboboxViewport,
} from 'radix-vue';
import { ref } from 'vue';

const v = ref('');
const options = [
    'Apple',
    'Banana',
    'Blueberry',
    'Grapes',
    'Pineapple',
    'りんご',
];
const vegetables = ['Aubergine', 'Broccoli', 'Carrot', 'Courgette', 'Leek'];
</script>

<template>
    <ComboboxRoot v-model="v" class="relative" open>
        <ComboboxAnchor
            class="inline-flex h-[35px] min-w-[160px] items-center justify-between gap-1.5 rounded bg-white px-4 text-sm leading-none text-green-700 shadow-md outline-none hover:bg-gray-100 focus:shadow-lg focus:ring-2 focus:ring-black"
        >
            <ComboboxTrigger>
                <div class="flex flex-1 justify-between h-full">
                    <ComboboxInput
                        class="!bg-transparent text-green-700 placeholder-gray-400 outline-none selection:bg-green-300"
                        placeholder="Placeholder..."
                    />
                    <Icon
                        icon="radix-icons:chevron-down"
                        class="h-4 w-4 text-green-700"
                    />
                </div>
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxContent
            class="absolute z-10 mt-2 w-full min-w-[160px] overflow-hidden rounded bg-white shadow-lg"
        >
            <ComboboxViewport class="p-1.5">
                <ComboboxEmpty
                    class="py-2 text-center text-xs font-medium text-gray-400"
                />

                <ComboboxGroup>
                    <ComboboxLabel class="px-6 text-xs leading-6 text-gray-600">
                        Fruits
                    </ComboboxLabel>

                    <ComboboxItem
                        v-for="(option, index) in options"
                        :key="index"
                        class="relative flex h-6 select-none items-center rounded px-6 pr-8 text-sm leading-none text-green-700 hover:bg-green-200 focus:bg-green-300"
                        :value="option"
                    >
                        <ComboboxItemIndicator
                            class="absolute left-0 inline-flex w-6 items-center justify-center"
                        >
                            <Icon icon="radix-icons:check" />
                        </ComboboxItemIndicator>
                        <span>
                            {{ option }}
                        </span>
                    </ComboboxItem>
                    <ComboboxSeparator class="m-1.5 h-px bg-green-400" />
                </ComboboxGroup>

                <ComboboxGroup>
                    <ComboboxLabel class="px-6 text-xs leading-6 text-gray-600">
                        Vegetables
                    </ComboboxLabel>
                    <ComboboxItem
                        v-for="(option, index) in vegetables"
                        :key="index"
                        class="relative flex h-6 select-none items-center rounded px-6 pr-8 text-sm leading-none text-green-700 hover:bg-green-200 focus:bg-green-300"
                        :value="option"
                    >
                        <ComboboxItemIndicator
                            class="absolute left-0 inline-flex w-6 items-center justify-center"
                        >
                            <Icon icon="radix-icons:check" />
                        </ComboboxItemIndicator>
                        <span>
                            {{ option }}
                        </span>
                    </ComboboxItem>
                </ComboboxGroup>
            </ComboboxViewport>
        </ComboboxContent>
    </ComboboxRoot>
</template>
