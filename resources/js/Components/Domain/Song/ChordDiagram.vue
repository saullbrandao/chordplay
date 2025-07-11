<script setup>
import { PlayIcon } from '@/Components/UI/Icons';
import { useSoundfont } from '@/Composables/useSoundfont';
import { useSongStore } from '@/Stores/songStore';

import { computed } from 'vue';

const props = defineProps({
    chordName: {
        type: String,
        required: true,
    },
});

const songStore = useSongStore();

const chord = computed(() => songStore.chords[props.chordName]);

const chordShape = computed(() => {
    if (!chord.value || !chord.value.shapes?.length) return null;
    return chord.value.shapes.find((s) => s.id === chord.value.defaultShapeId);
});

// Constants for SVG dimensions
const FRET_COUNT = 5;
const STRING_COUNT = 6;
const SVG_WIDTH = 220;
const SVG_HEIGHT = 240;
const PADDING = { top: 30, right: 30, bottom: 20, left: 40 };
const FRETBOARD_WIDTH = SVG_WIDTH - PADDING.left - PADDING.right;
const FRETBOARD_HEIGHT = SVG_HEIGHT - PADDING.top - PADDING.bottom;
const STRING_SPACING = FRETBOARD_WIDTH / (STRING_COUNT - 1);
const FRET_SPACING = FRETBOARD_HEIGHT / FRET_COUNT;
const DOT_RADIUS = STRING_SPACING / 2.5;

const { playChord } = useSoundfont();

// Computed values for rendering the diagram
const strings = computed(() =>
    Array.from({ length: STRING_COUNT }, (_, i) => ({
        x1: PADDING.left + i * STRING_SPACING,
        y1: PADDING.top,
        x2: PADDING.left + i * STRING_SPACING,
        y2: PADDING.top + FRETBOARD_HEIGHT,
    })),
);

const frets = computed(() =>
    Array.from({ length: FRET_COUNT + 1 }, (_, i) => ({
        x1: PADDING.left,
        y1: PADDING.top + i * FRET_SPACING,
        x2: PADDING.left + FRETBOARD_WIDTH,
        y2: PADDING.top + i * FRET_SPACING,
        isNut: i === 0 && chordShape.value.base_fret === 1,
    })),
);

const dots = computed(() => {
    if (!chordShape.value?.frets) return [];

    return chordShape.value.frets
        .map((fretPos, stringIndex) => {
            // No dot for open or muted strings
            if (fretPos <= 0) return null;

            const finger = chordShape.value.fingers[stringIndex];
            const isCoveredByBarre =
                chordShape.value.barres?.includes(fretPos) && finger === 1;

            if (isCoveredByBarre) return null;

            return {
                cx: PADDING.left + stringIndex * STRING_SPACING,
                cy: PADDING.top + fretPos * FRET_SPACING - FRET_SPACING / 2,
                finger: finger,
            };
        })
        .filter(Boolean);
});

const openMutedStrings = computed(() => {
    if (!chordShape.value?.frets) return [];
    return chordShape.value.frets
        .map((fretPos, stringIndex) => ({
            x: PADDING.left + stringIndex * STRING_SPACING,
            y: PADDING.top - 10,
            text: fretPos === 0 ? '○' : fretPos === -1 ? '×' : null,
        }))
        .filter((s) => s.text);
});

const barrePositions = computed(() => {
    if (!chordShape.value?.barres?.length) return [];

    return chordShape.value.barres
        .map((barreFret) => {
            const fingerToFind = 1;
            const firstStringIndex =
                chordShape.value.fingers.indexOf(fingerToFind);
            const lastStringIndex =
                chordShape.value.fingers.lastIndexOf(fingerToFind);

            if (firstStringIndex === -1) return null;

            const height = DOT_RADIUS * 1.5;
            const extension = DOT_RADIUS * 2;
            const fretCenterY =
                PADDING.top + barreFret * FRET_SPACING - FRET_SPACING / 2;

            return {
                x:
                    PADDING.left +
                    firstStringIndex * STRING_SPACING -
                    extension / 2,
                y: fretCenterY - height / 2,
                width:
                    (lastStringIndex - firstStringIndex) * STRING_SPACING +
                    extension,
                height: height,
                rx: height / 2,
                ry: height / 2,
            };
        })
        .filter(Boolean);
});
</script>

<template>
    <div
        class="bg-base-300 group text-base-content border-base-content/20 relative inline-block rounded border shadow"
    >
        <h3 class="text-md text-center font-sans font-bold">
            {{ chordName }}
        </h3>
        <template v-if="songStore.missingChords.has(chordName)">
            <span class="flex h-28 w-24 items-center justify-center">
                <span class="loading loading-spinner loading-xl"></span>
            </span>
        </template>
        <template v-else-if="chordShape">
            <PlayIcon
                v-if="chordShape?.midi?.length"
                class="hover:border-base-content/70 bg-base-300 border-base-content/20 absolute top-3/5 left-1/2 z-50 size-10 -translate-x-1/2 -translate-y-1/2 transform cursor-pointer rounded-full border p-2 opacity-0 shadow group-hover:opacity-100 hover:border"
                @click="() => playChord(chordShape.midi)"
            />
            <svg
                :viewBox="`0 0 ${SVG_WIDTH} ${SVG_HEIGHT}`"
                class="h-28 w-24 font-mono"
                :aria-label="`Guitar chord diagram for ${chordName}`"
            >
                <!-- Base Fret Indicator -->
                <text
                    v-if="chordShape.base_fret > 1"
                    :x="PADDING.left - 20"
                    :y="PADDING.top + FRET_SPACING / 2"
                    font-family="monospace"
                    fill="currentColor"
                    text-anchor="middle"
                    dominant-baseline="middle"
                    class="text-2xl"
                >
                    {{ chordShape.base_fret }}
                </text>

                <!-- Strings -->
                <line
                    v-for="(s, i) in strings"
                    :key="`string-${i}`"
                    v-bind="s"
                    stroke="currentColor"
                    stroke-width="1.5"
                />

                <!-- Frets -->
                <line
                    v-for="(f, i) in frets"
                    :key="`fret-${i}`"
                    v-bind="f"
                    stroke="currentColor"
                    :stroke-width="f.isNut ? '6' : '1.5'"
                />

                <!-- Open/Muted String Indicators -->
                <text
                    v-for="(s, i) in openMutedStrings"
                    :key="`status-${i}`"
                    :x="s.x"
                    :y="s.y"
                    fill="currentColor"
                    text-anchor="middle"
                    class="text-3xl"
                >
                    {{ s.text }}
                </text>

                <!-- Barre Chord Rectangles -->
                <rect
                    v-for="(barre, i) in barrePositions"
                    :key="`barre-${i}`"
                    :x="barre.x"
                    :y="barre.y"
                    :width="barre.width"
                    :height="barre.height"
                    :rx="barre.rx"
                    :ry="barre.ry"
                    fill="currentColor"
                />

                <!-- Fingering Dots -->
                <g v-for="(dot, i) in dots" :key="`dot-${i}`">
                    <circle
                        :cx="dot.cx"
                        :cy="dot.cy"
                        :r="DOT_RADIUS"
                        fill="currentColor"
                    />
                    <text
                        v-if="dot.finger > 0"
                        :x="dot.cx"
                        :y="dot.cy"
                        text-anchor="middle"
                        dominant-baseline="central"
                        fill="currentColor"
                        class="text-base-100 text-2xl font-bold"
                    >
                        {{ dot.finger }}
                    </text>
                </g>
            </svg>
        </template>
        <span
            v-else
            class="text-base-content/70 flex h-28 w-24 items-center justify-center text-center font-sans text-sm text-balance"
        >
            No diagram found
        </span>
    </div>
</template>
