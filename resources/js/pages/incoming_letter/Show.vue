<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FormModal from '@/pages/incoming_letter/Index/FormModal.vue';
import DispositionList from '@/pages/incoming_letter/Show/Disposition.vue';
import DispositionModal from '@/pages/incoming_letter/Index/DispositionModal.vue';
import type {
    BreadcrumbItem, IncomingLetter as IncomingLetterBase,
    LetterCategory, SharedData, Disposition as DispositionBase, User, OutgoingLetter
} from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Button, Chip, Image, ConfirmPopup, useConfirm,
    Fieldset,
} from 'primevue';
import { dateHumanFormat, dateHumanFormatWithTime, dateHumanSmartFormat } from '@/lib/utils';
import { useI18n } from 'vue-i18n';

interface Disposition extends DispositionBase {
    assignee: User | null;
    assigner: User;
    children: Disposition[];
    replies: OutgoingLetter[];
}

interface IncomingLetter extends IncomingLetterBase {
    letter_categories: LetterCategory[];
    dispositions: Disposition[];
    replies: OutgoingLetter[];
}

interface Props {
    item: IncomingLetter;
    next: string | null;
    prev: string | null;
    total: number;
    index: number;
    categories: LetterCategory[];
}

const page = usePage<SharedData>();
const props = defineProps<Props>();
const modal = ref();
const dispositionModal = ref();
const confirm = useConfirm();
const { t, locale } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.incoming_letter',
        href: route('incoming-letter.index'),
    },
    {
        title: props.item.agenda_number,
        href: route('incoming-letter.show', props.item.id),
    },
];

const destroy = (event: MouseEvent, item: IncomingLetter) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        message: t('label.are_you_sure_delete'),
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: t('action.cancel'),
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: t('action.delete'),
            severity: 'danger',
        },
        accept: () => {
            router.delete(route('incoming-letter.destroy', {
                letter: item.id, next: props.next, prev: props.prev,
            }), {
                preserveScroll: true,
                preserveState: true,
            });
        },
    });
};

const markAsUnread = (item: IncomingLetter) => {
    router.post(route('incoming-letter.mark-unread', item.id), {
        index: true,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head :title="$t('menu.incoming_letter')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between">
                <div class="flex gap-x-1 items-center">
                    <Link :href="route('incoming-letter.index')">
                        <Button
                            v-tooltip.bottom="$t('action.back')"
                            icon="pi pi-arrow-left" size="small" variant="text" severity="secondary"
                            v-if="page.props.auth.allow.view_incoming_letter" rounded></Button>
                    </Link>
                    <Button
                        v-tooltip.bottom="$t('action.delete')"
                        v-if="page.props.auth.allow.delete_incoming_letter"
                        icon="pi pi-trash" size="small" variant="text" severity="danger"
                        @click="destroy($event, item)" rounded></Button>
                    <span class="text-gray-300">|</span>
                    <Button
                        v-tooltip.bottom="$t('action.mark_unread')"
                        v-if="page.props.auth.allow.view_incoming_letter"
                        icon="pi pi-info-circle" size="small" variant="text" severity="secondary"
                        @click="markAsUnread(item)" rounded></Button>
                </div>

                <div class="flex gap-x-1 items-center">
                    <div class="text-sm text-gray-500">{{ $t('label.show_of', { index, total }) }}</div>
                    <Link :href="route('incoming-letter.show', prev ?? '')" :disabled="prev === null">
                        <Button
                            v-tooltip.bottom="$t('label.newer')"
                            :disabled="prev === null"
                            icon="pi pi-chevron-left" size="small" variant="text" severity="secondary"
                            v-if="page.props.auth.allow.view_incoming_letter" rounded></Button>
                    </Link>
                    <Link :href="route('incoming-letter.show', next ?? '')" :disabled="next === null">
                        <Button
                            v-tooltip.bottom="$t('label.older')"
                            :disabled="next === null"
                            icon="pi pi-chevron-right" size="small" variant="text" severity="secondary"
                            v-if="page.props.auth.allow.view_incoming_letter" rounded></Button>
                    </Link>
                    <Button
                        v-tooltip.bottom="$t('action.edit')"
                        v-if="page.props.auth.allow.edit_incoming_letter"
                        icon="pi pi-pencil" size="small" variant="text" severity="secondary"
                        @click="() => modal?.open(item)" rounded></Button>
                </div>
            </div>
            <div class="flex justify-between items-baseline">
                <div class="flex items-baseline gap-2">
                    <h1 class="text-2xl">{{ item.subject }}</h1>
                    <span class="text-sm text-gray-500">{{ dateHumanFormat(item.letter_date, 0, locale) }}</span>
                </div>
                <span class="text-sm text-gray-500">{{ item.letter_number }}</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex flex-col">
                    <div class="text-sm font-medium">{{ item.sender }}</div>
                    <div class="text-xs text-gray-500">{{ item.institution }}</div>
                </div>
                <div class="flex gap-x-1 items-center">
                    <div class="text-sm text-gray-500" v-tooltip.bottom="dateHumanFormatWithTime(item.created_at, 0, locale)">
                        {{ dateHumanSmartFormat(item.created_at, locale) }}
                    </div>
                    <Button
                        v-tooltip.bottom="$t('action.disposition')"
                        v-if="page.props.auth.allow.add_disposition"
                        icon="pi pi-reply -scale-x-100" size="small" variant="text" severity="secondary"
                        @click="() => dispositionModal?.open(item, null, null)" rounded></Button>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 lg:direction-rtl gap-6 m-0">
                <div class="lg:col-span-2 text-gray-600 flex flex-col gap-3">
                    <div v-if="item.summary" class="mb-6">
                        <Fieldset toggleable>
                            <template #legend>
                                <div class="flex items-center pl-2">
                                    <i class="pi pi-sparkles"></i>
                                    <span class="font-bold p-2">{{ $t('label.summary_by_ai') }}</span>
                                </div>
                            </template>
                            <p class="m-0" v-html="item.summary"></p>
                        </Fieldset>
                    </div>
                    <p v-html="item.body"></p>
                    <div v-if="item.letter_categories.length > 0" class="flex flex-wrap gap-2">
                        <Chip
                            v-for="category in item.letter_categories"
                            :key="category.id" class="text-xs"
                            :label="category.name" />
                    </div>
                    <div v-if="item.replies.length > 0 && page.props.auth.allow.view_outgoing_letter" class="mt-3">
                        <h3 class="font-medium">{{ $t('label.reply_letter') }}</h3>
                        <div class="flex flex-wrap gap-3 mt-2">
                            <a v-for="reply in item.replies" :key="reply.id"
                               class="text-sm cursor-pointer underline text-emerald-600" target="_blank"
                               :href="route('outgoing-letter.show', reply.id)">{{ reply.agenda_number }}</a>
                        </div>
                    </div>
                </div>
                <div>
                    <iframe
                        v-if="item.file_url.endsWith('pdf')"
                        :src="item.file_url" width="100%"
                        height="500px"></iframe>
                    <Image
                        v-else
                        :src="item.file_url" :alt="item.subject"
                        preview class="h-[500px]" />
                </div>
            </div>

            <template v-if="page.props.auth.allow.view_disposition">
                <div class="flex justify-between mt-5">
                    <h3 class="font-medium text-gray-600">{{ $t('menu.disposition') }}</h3>
                    <Button
                        v-tooltip.bottom="$t('action.disposition')"
                        v-if="page.props.auth.allow.add_disposition"
                        icon="pi pi-reply -scale-x-100" size="small" variant="text" severity="secondary"
                        @click="() => dispositionModal?.open(item, null, null)" rounded></Button>
                </div>
                <DispositionList
                    :categories
                    :letter="item" />
            </template>
        </div>

        <ConfirmPopup />
        <FormModal
            :categories
            ref="modal" />
        <DispositionModal
            ref="dispositionModal" />
    </AppLayout>
</template>
