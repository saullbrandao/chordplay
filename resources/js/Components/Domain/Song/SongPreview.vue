<script setup>
const props = defineProps({
    content: { type: String, default: '' },
});

function separateLines() {
    const lines = props.content.split('\n').map((line) => {
        const trimmedLine = line.trim();
        if (trimmedLine.startsWith('[')) {
            return { contentType: 'chords', content: line };
        } else if (trimmedLine === '') {
            return { contentType: 'empty', content: trimmedLine };
        } else {
            return { contentType: 'lyrics', content: trimmedLine };
        }
    });

    return lines;
}

function extractBrackets(line) {
    let result = '';
    let token = '';
    let inside = false;

    for (const char of line) {
        if (char === '[') {
            inside = true;
            token = '';
        } else if (char === ']' && inside) {
            result += token;
            inside = false;
            token = '';
        } else if (inside && char !== ' ') {
            token += char;
        } else if (!inside && char === ' ') {
            result += char;
        }
    }
    return result;
}
</script>

<template>
    <div
        v-for="(line, lineIndex) in separateLines()"
        :key="lineIndex"
        class="w-full font-mono text-sm leading-5 tracking-tighter"
        :class="{
            'break-after-avoid': line.contentType === 'chords',
            'break-after-auto': line.contentType !== 'chords',
        }"
    >
        <p
            v-if="line.contentType === 'chords'"
            class="text-accent font-bold whitespace-pre"
        >
            {{ extractBrackets(line.content) }}
        </p>
        <p v-else-if="line.contentType === 'lyrics'" class="whitespace-pre">
            {{ line.content }}
        </p>
        <br v-else />
    </div>
</template>
