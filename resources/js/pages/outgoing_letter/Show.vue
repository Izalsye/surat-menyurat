<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FormModal from '@/pages/outgoing_letter/Index/FormModal.vue';
import type {
    BreadcrumbItem, OutgoingLetter as OutgoingLetterBase,
    LetterCategory, SharedData, Disposition as DispositionBase, User, IncomingLetter
} from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Button, Chip, Image, ConfirmPopup, useConfirm,
    Fieldset, Avatar
} from 'primevue';
import { dateHumanFormat, dateHumanFormatWithTime, dateHumanSmartFormat } from '@/lib/utils';
import { useI18n } from 'vue-i18n';

interface Disposition extends DispositionBase {
    assignee: User | null;
    assigner: User;
}

interface OutgoingLetter extends OutgoingLetterBase {
    letter_categories: LetterCategory[];
    disposition: Disposition;
    incoming_letter: IncomingLetter | null;
}

interface Props {
    item: OutgoingLetter;
    next: string | null;
    prev: string | null;
    total: number;
    index: number;
    categories: LetterCategory[];
}

const page = usePage<SharedData>();
const props = defineProps<Props>();
const modal = ref();
const confirm = useConfirm();
const { t, locale } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.outgoing_letter',
        href: route('outgoing-letter.index'),
    },
    {
        title: props.item.agenda_number,
        href: route('outgoing-letter.show', props.item.id),
    },
];
const destroy = (event: MouseEvent, item: OutgoingLetter) => {
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
            router.delete(route('outgoing-letter.destroy', {
                letter: item.id, next: props.next, prev: props.prev,
            }), {
                preserveScroll: true,
                preserveState: true,
            });
        },
    });
};

const urgencyIcon = (urgency: string) => {
    switch (urgency) {
        case 'normal':
            return 'pi pi-minus-circle text-gray-500';
        case 'important':
            return 'pi pi-exclamation-circle text-yellow-500';
        case 'urgent':
            return 'pi pi-angle-up text-red-500';
        case 'immediate':
            return 'pi pi-angle-double-up text-red-500';
        case 'confidential':
            return 'pi pi-lock text-yellow-500';
        case 'top_secret':
            return 'pi pi-ban text-red-500';
    }
};

</script>

<template>
    <Head :title="$t('menu.outgoing_letter')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between">
                <div class="flex gap-x-1 items-center">
                    <Link :href="route('outgoing-letter.index')">
                        <Button
                            v-tooltip.bottom="$t('action.back')"
                            icon="pi pi-arrow-left" size="small" variant="text" severity="secondary"
                            v-if="page.props.auth.allow.view_outgoing_letter" rounded></Button>
                    </Link>
                    <Button
                        v-tooltip.bottom="$t('action.delete')"
                        v-if="page.props.auth.allow.delete_outgoing_letter"
                        icon="pi pi-trash" size="small" variant="text" severity="danger"
                        @click="destroy($event, item)" rounded></Button>
                </div>

                <div class="flex gap-x-1 items-center">
                    <div class="text-sm text-gray-500">{{ $t('label.show_of', { index, total }) }}</div>
                    <Link :href="route('outgoing-letter.show', prev ?? '')" :disabled="prev === null">
                        <Button
                            v-tooltip.bottom="$t('label.newer')"
                            :disabled="prev === null"
                            icon="pi pi-chevron-left" size="small" variant="text" severity="secondary"
                            v-if="page.props.auth.allow.view_outgoing_letter" rounded></Button>
                    </Link>
                    <Link :href="route('outgoing-letter.show', next ?? '')" :disabled="next === null">
                        <Button
                            v-tooltip.bottom="$t('label.older')"
                            :disabled="next === null"
                            icon="pi pi-chevron-right" size="small" variant="text" severity="secondary"
                            v-if="page.props.auth.allow.view_outgoing_letter" rounded></Button>
                    </Link>
                    <Button
                        v-tooltip.bottom="$t('action.edit')"
                        v-if="page.props.auth.allow.edit_outgoing_letter"
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
                    <div class="text-sm font-medium">{{ item.recipient }}</div>
                </div>
                <div class="flex gap-x-1 items-center">
                    <div class="text-sm text-gray-500" v-tooltip.bottom="dateHumanFormatWithTime(item.created_at, 0, locale)">
                        {{ dateHumanSmartFormat(item.created_at, locale) }}
                    </div>
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
                    <div v-if="item.incoming_letter && page.props.auth.allow.view_incoming_letter" class="mt-3">
                        <h3 class="font-medium">{{ $t('menu.incoming_letter') }}</h3>
                        <a class="text-sm cursor-pointer underline text-emerald-600" target="_blank"
                           :href="route('incoming-letter.show', item.incoming_letter.id)">{{ item.incoming_letter.agenda_number }}</a>
                    </div>
                    <div v-if="item.disposition && page.props.auth.allow.view_disposition" class="mt-3">
                        <h3 class="font-medium mb-3">{{ $t('menu.disposition') }}</h3>
                        <section class="flex gap-x-3 justify-start items-start" :id="item.disposition.id">
                            <div>
                                <Avatar
                                    class="w-full" size="normal"
                                    :image="item.disposition.assigner.avatar"
                                    shape="circle" />
                            </div>
                            <div class="flex-1">
                                <div class="bg-card text-card-foreground flex flex-col gap-0.5 rounded-2xl border p-3 shadow-xs">
                                    <div class="flex justify-between">
                                        <div class="flex gap-1.5 items-center">
                                            <h3 class="font-medium text-sm" :class="{ 'text-emerald-600': item.disposition.assigner.id === page.props.auth.user.id }">
                                                {{ item.disposition.assigner.name }}
                                            </h3>
                                            <template v-if="item.disposition.assignee">
                                                <i class="pi pi-arrow-right" style="font-size: 0.5rem"></i>
                                                <h3 class="font-medium text-sm" :class="{ 'text-emerald-600': item.disposition.assignee.id === page.props.auth.user.id }">
                                                    {{ item.disposition.assignee.name }}
                                                </h3>
                                            </template>
                                        </div>
                                        <span>
                                            <i v-tooltip.left="$t(`label.${item.disposition.urgency}`)"
                                               :class="urgencyIcon(item.disposition.urgency)"
                                               style="font-size: 0.75rem"></i>
                                        </span>
                                    </div>
                                    <small v-if="item.disposition.due_at && item.disposition.reply_letter" class="text-gray-600 text-xs">{{ $t('label.due_at', { due: dateHumanFormat(item.disposition.due_at) }) }}</small>
                                    <p>{{ item.disposition.description }}</p>
                                    <small class="text-end">
                                        <i
                                            v-tooltip.bottom="$t('label.replied')"
                                            class="pi pi-check text-emerald-600"
                                            style="font-size: 0.75rem"></i>
                                    </small>
                                </div>
                                <div class="flex gap-x-3 px-3 mt-1 text-xs text-gray-600">
                                    <span v-tooltip.bottom="dateHumanFormatWithTime(item.disposition.created_at, 0, page.props.auth.user.locale)">
                                        {{ dateHumanSmartFormat(item.disposition.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </section>
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

        </div>

        <ConfirmPopup />
        <FormModal
            :categories
            ref="modal" />
    </AppLayout>
</template>
