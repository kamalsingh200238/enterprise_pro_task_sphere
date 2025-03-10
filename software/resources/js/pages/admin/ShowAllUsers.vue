<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
} from '@/components/ui/pagination';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { PaginatedData, User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { ref, watch } from 'vue';

interface Props {
    users: PaginatedData<User>;
    search?: string;
}

const props = defineProps<Props>();
const searchQuery = ref(props.search ?? '');

const updateSearch = debounce(() => {
    router.get(route('users.show-all'), { search: searchQuery.value, page: 1 }, { preserveState: true, replace: true });
}, 300);

const updatePage = debounce((page: number) => {
    router.get(route('users.show-all'), { search: searchQuery.value, page: page }, { preserveState: true, replace: true });
}, 50);

watch(searchQuery, updateSearch);
</script>

<template>
    <AppLayout>
        <div class="space-y-4 p-4">
            <!-- Search Input -->
            <Input v-model="searchQuery" placeholder="Search users..." class="w-full rounded-md border p-2" />

            <!-- User List -->
            <div class="space-y-4">
                <div v-for="user in users.data" :key="user.id" class="flex items-center gap-4 rounded-md border p-4 shadow-sm">
                    <Avatar>
                        <AvatarFallback>{{ user.name.charAt(0) }}</AvatarFallback>
                    </Avatar>
                    <div>
                        <Link :href="`/users/${user.id}`" class="block text-lg font-semibold hover:text-blue-500">
                            {{ user.name }}
                        </Link>
                        <span class="text-gray-600">{{ user.email }}</span>
                    </div>
                </div>
            </div>

            <Pagination
                v-slot="{ page }"
                :items-per-page="users.per_page"
                :total="users.total"
                :sibling-count="1"
                show-edges
                :default-page="users.current_page"
                @update:page="(e) => updatePage(e)"
            >
                <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                    <PaginationFirst />
                    <PaginationPrev />

                    <template v-for="(item, index) in items">
                        <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                            <Button class="h-9 w-9 p-0" :variant="item.value === page ? 'default' : 'outline'">
                                {{ item.value }}
                            </Button>
                        </PaginationListItem>
                        <PaginationEllipsis v-else :key="item.type" :index="index" />
                    </template>

                    <PaginationNext />
                    <PaginationLast />
                </PaginationList>
            </Pagination>
        </div>
    </AppLayout>
</template>
