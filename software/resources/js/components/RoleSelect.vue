<script setup lang="ts">
import { userRoles } from '@/lib/userRoles';
import { computed } from 'vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from './ui/select';

interface Props {
    disabled: boolean;
}

const props = defineProps<Props>();
const modelValue = defineModel<string | null>();

const selectValue = computed({
    get: () => modelValue.value ?? null,
    set: (value: string) => {
        modelValue.value = value ?? null;
    },
});
</script>

<template>
    <Select v-model="selectValue" :disabled="props.disabled">
        <SelectTrigger>
            <SelectValue placeholder="Select a Role" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel>Roles</SelectLabel>
                <SelectItem v-for="role in userRoles" :key="role.value" :value="role.value">
                    {{ role.label }}
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
