<script setup>
import SongForm from '@/Components/Domain/Song/SongForm.vue';
import SongPreview from '@/Components/Domain/Song/SongPreview.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    song: {
        type: Object,
        required: true,
    },
    artist: {
        type: Object,
        required: true,
    },
    availableKeys: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: props.song.name,
    key: props.song.key,
    content: props.song.content,
});

function submitForm() {
    form.patch(
        route('song-submissions.update', {
            songSubmission: props.song,
        }),
    );
}
</script>

<template>
    <Head :title="`${artist.name} - ${song.name} - Edit`" />

    <AppLayout dusk="song-submissions-edit-page">
        <template #header>
            <PageHeader title="Edit song submission" />

            <Link :href="route('artists.show', artist)">
                {{ artist.name }}
            </Link>
        </template>

        <div class="mt-6 grid grid-cols-2 justify-between gap-12 py-6">
            <SongForm
                :available-keys="availableKeys"
                :initial-data="form"
                submit-label="Update"
                @submit="submitForm"
            />

            <section class="dark:text-white">
                <h3 class="text-md mb-6">Preview</h3>
                <SongPreview :content="form.content" />
            </section>
        </div>
    </AppLayout>
</template>
