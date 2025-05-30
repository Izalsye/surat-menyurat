<script setup lang="ts">
import type { Disposition as DispositionBase, IncomingLetter as IncomingLetterBase, LetterCategory, User } from '@/types';
import DispositionCard from '@/pages/incoming_letter/Show/DispositionCard.vue';

interface Disposition extends DispositionBase {
    assignee: User | null;
    assigner: User;
    children: Disposition[];
}

interface IncomingLetter extends IncomingLetterBase {
    letter_categories: LetterCategory[];
    dispositions: Disposition[];
}

defineProps<{
    letter: IncomingLetter;
}>();
</script>

<template>
    <template v-if="letter.dispositions.length > 0">
        <DispositionCard
            v-for="disposition in letter.dispositions" :key="disposition.id"
            :letter :disposition />
    </template>
    <p v-else class="text-gray-500">{{ $t('label.no_disposition') }}</p>
</template>
