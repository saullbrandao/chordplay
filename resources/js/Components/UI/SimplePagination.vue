<script setup>
import {
    ChevronLeftIconSolid,
    ChevronRightIconSolid,
} from '@/Components/UI/Icons';
import { Link } from '@inertiajs/vue3';

defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <ul
        v-if="pagination.last_page !== 1"
        class="mt-2 flex items-center justify-center gap-3 dark:text-white"
    >
        <li class="flex items-center justify-center">
            <Link
                :href="pagination.prev_page_url ?? ''"
                class="btn btn-xs btn-ghost"
                :class="{
                    'btn-disabled': !pagination.prev_page_url,
                }"
            >
                <ChevronLeftIconSolid class="size-3" />
            </Link>
        </li>

        <li class="flex items-center justify-center text-sm">
            {{ pagination.current_page }}/{{ pagination.last_page }}
        </li>

        <li class="flex items-center justify-center">
            <Link
                :href="pagination.next_page_url ?? ''"
                class="btn btn-xs btn-ghost"
                :class="{
                    'btn-disabled': !pagination.next_page_url,
                }"
            >
                <ChevronRightIconSolid class="size-3" />
            </Link>
        </li>
    </ul>
</template>
