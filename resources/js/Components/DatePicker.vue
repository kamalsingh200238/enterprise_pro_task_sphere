<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/Components/ui/popover';
import { Calendar } from '@/Components/ui/v-calendar';
import { cn } from '@/lib/utils';
import { format, formatISO, parseISO } from 'date-fns';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    disabled: boolean;
}

const props = defineProps<Props>();
const dateString = defineModel<string>();
const date = computed({
    get() {
        if (!dateString.value) return undefined;
        return parseISO(dateString.value);
    },
    set(newDate) {
        dateString.value = newDate ? formatISO(newDate) : '';
    },
});
const formatDate = (date: Date) => format(date, 'MMM d, yyyy');
</script>
<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="cn('justify-start', !date && 'text-muted-foreground')"
                :disabled="props.disabled"
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ date ? formatDate(date) : 'Pick a date' }}
            </Button>
        </PopoverTrigger>
        <PopoverContent align="start" class="w-auto p-0">
            <Calendar v-model="date" initial-focus />
        </PopoverContent>
    </Popover>
</template>
