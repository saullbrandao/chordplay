<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TextLink from '@/Components/TextLink.vue';
import { ref, watch } from 'vue';
import SongContent from './partials/SongContent.vue';
import IconLink from '@/Components/IconLink.vue';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    song: Object,
    is_favorited: Boolean,
    artist: Object,
    valid_chords: Object,
    available_keys: Array,
});

const page = usePage();
const user = page.props.auth.user;
const song_key = ref(props.song.key);
const is_dual_column = ref(false);
const capo_position = ref(0);
const key_offset = ref(0);

watch(capo_position, (new_position, old_position) => {
    new_position - old_position > 0
        ? transpose('down', new_position - old_position, false)
        : transpose('up', Math.abs(new_position - old_position), false);
});

function transpose(direction, half_steps, should_change_key = true) {
    let key_index = props.available_keys.findIndex(
        (key) => key === song_key.value,
    );

    if (direction === 'down') {
        key_index =
            (key_index - half_steps + props.available_keys.length) %
            props.available_keys.length;
        key_offset.value -= half_steps;
    } else {
        key_index = (key_index + half_steps) % props.available_keys.length;
        key_offset.value += half_steps;
    }

    if (should_change_key) {
        song_key.value = props.available_keys[key_index];
    }
}

const is_loading = ref(false);

function handleFavorite() {
    is_loading.value = true;

    const method = props.is_favorited ? 'delete' : 'post';

    router.visit(
        route('songs.favorite', { artist: props.artist, song: props.song }),
        {
            method,
            only: ['is_favorited'],
            preserveState: true,
            onFinish: () => {
                is_loading.value = false;
            },
            onError: () => {
                is_loading.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="`${song.name} - ${artist.name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <PageHeader :title="song.name" />
                <IconLink
                    v-if="user"
                    :href="route('artists.songs.edit', { artist, song })"
                >
                    <i class="fa-solid fa-pencil"></i>
                </IconLink>
                <FavoriteButton
                    :is_favorited="is_favorited"
                    @favorite="handleFavorite"
                    :disabled="is_loading"
                />
            </div>
            <TextLink :href="route('artists.show', artist)">
                {{ artist.name }}
            </TextLink>
            <div class="my-6 flex flex-col gap-2">
                <div
                    class="flex items-center justify-start gap-2 text-sm dark:text-white"
                >
                    <span>Key: </span>
                    <button
                        @click="() => transpose('down', 1)"
                        class="flex size-8 cursor-pointer items-center justify-center rounded-full text-gray-900 transition-colors hover:bg-gray-300 dark:text-white dark:hover:bg-gray-800"
                    >
                        <i class="fa-solid fa-minus"></i>
                    </button>
                    <span class="text-yellow-600">
                        {{ song_key }}
                    </span>
                    <button
                        @click="() => transpose('up', 1)"
                        class="flex size-8 cursor-pointer items-center justify-center rounded-full text-gray-900 transition-colors hover:bg-gray-300 dark:text-white dark:hover:bg-gray-800"
                    >
                        <i class="fa-solid fa-add"></i>
                    </button>
                </div>

                <div
                    class="flex items-center justify-start gap-2 text-sm dark:text-white"
                >
                    <span>Capo Fret:</span>
                    <select
                        name="capo"
                        id="capo"
                        v-model="capo_position"
                        class="h-8 w-14 rounded-md border border-gray-300 bg-gray-200 p-1 px-2 text-sm text-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option
                            value="0"
                            class="px-2 dark:bg-gray-800 dark:text-white"
                        >
                            0
                        </option>
                        <option
                            v-for="n in 11"
                            :key="n"
                            :value="n"
                            class="px-2 dark:bg-gray-800 dark:text-white"
                        >
                            {{ n }}
                        </option>
                    </select>
                </div>
            </div>
            <button
                @click="is_dual_column = !is_dual_column"
                class="cursor-pointer rounded-md border border-gray-300 bg-transparent px-3 py-1.5 text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-300 hover:text-gray-900 sm:block dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                :class="{
                    'border-gray-400 bg-yellow-600 text-white hover:bg-yellow-600 hover:text-white hover:brightness-105 dark:border-gray-600 dark:bg-yellow-600 dark:text-white dark:hover:bg-yellow-600 dark:hover:brightness-110':
                        is_dual_column,
                }"
            >
                <i class="fa-solid fa-table-columns mr-1"></i>
                Dual Column
            </button>
        </template>
        <div
            class="py-6 dark:text-white"
            :class="{
                'columns-2 gap-8': is_dual_column,
            }"
        >
            <SongContent
                :original_key="song.key"
                :key_offset="key_offset"
                :available_keys="available_keys"
                :content="song.lines"
                :valid_chords="valid_chords"
            />
        </div>
    </AppLayout>
</template>
