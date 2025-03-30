<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/v-calendar';
import { cn } from '@/lib/utils';
import { format, formatISO, parseISO } from 'date-fns';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

const startDateModel = defineModel<string>('startDate', { required: true });
const endDateModel = defineModel<string>('endDate', { required: true });

// Convert ISO strings from model to Date objects for v-calendar
const dateRange = computed({
    get() {
        return {
            start: parseISO(startDateModel.value),
            end: parseISO(endDateModel.value),
        };
    },
    set(newValue: { start: Date | null; end: Date | null }) {
        if (newValue.start) {
            startDateModel.value = formatISO(newValue.start);
        } else {
            startDateModel.value = '';
        }

        if (newValue.end) {
            endDateModel.value = formatISO(newValue.end);
        } else {
            endDateModel.value = '';
        }
    },
});

// Format the display string for the selected date range
const dateRangeText = computed(() => {
    const { start, end } = dateRange.value;

    if (!start) return 'Pick a date';

    if (!end) {
        return format(start, 'dd MMM, yyyy HH:mm:ss');
    }

    return `${format(start, 'dd MMM, yyyy HH:mm:ss')} - ${format(end, 'dd MMM, yyyy HH:mm:ss')}`;
});
</script>

<template>
    <div :class="cn('grid gap-2', $attrs.class ?? '')">
        <Popover>
            <PopoverTrigger as-child>
                <Button
                    id="date-range"
                    :variant="'outline'"
                    :class="cn('w-fit justify-start text-left', !dateRange.start && 'text-muted-foreground')"
                    :disabled="props.disabled"
                >
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    <span>{{ dateRangeText }}</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="start">
                <Calendar v-model.range="dateRange" is-range is24hr mode="datetime" :columns="1" :time-accuracy='3' />
            </PopoverContent>
        </Popover>
    </div>
</template>
