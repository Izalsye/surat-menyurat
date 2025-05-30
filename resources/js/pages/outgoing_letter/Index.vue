<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import FormModal from '@/pages/outgoing_letter/Index/FormModal.vue';
import UploadModal from '@/pages/outgoing_letter/Index/UploadModal.vue';
import type {
    BreadcrumbItem, Paginate, SharedData, OutgoingLetter as OutgoingLetterBase,
    LetterCategory, Disposition,
} from '@/types';
import { Head, router, usePage, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    useConfirm, ConfirmPopup, Button, Checkbox, Popover,
    FloatLabel, InputText, DatePicker, SelectButton, MultiSelect,
} from 'primevue';
import { dateHumanSmartFormat } from '@/lib/utils';
import { useI18n } from 'vue-i18n';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.outgoing_letter',
        href: route('outgoing-letter.index'),
    },
];

interface OutgoingLetter extends OutgoingLetterBase {
    letter_categories: LetterCategory[];
    disposition: Disposition;
}

defineProps<{
    items: Paginate<OutgoingLetter>;
    categories: LetterCategory[];
}>();

const page = usePage<SharedData>();
const { t, locale } = useI18n();

const selected = ref<OutgoingLetter[]>([]);
const modal = ref();
const uploadModal = ref();

const confirm = useConfirm();
const params = new URLSearchParams(window.location.search);
const filterForm = useForm({
    agenda_number: params.get('agenda_number'),
    letter_number: params.get('letter_number'),
    letter_date: params.get('letter_date[0]') && params.get('letter_date[1]') ? [
        new Date(params.get('letter_date[0]')),
        new Date(params.get('letter_date[1]')),
    ] : null,
    created_at: params.get('created_at[0]') && params.get('created_at[1]') ? [
        new Date(params.get('created_at[0]')),
        new Date(params.get('created_at[1]')),
    ] : null,
    recipient: params.get('recipient'),
    subject: params.get('subject'),
    status: params.get('status'),
    categories: [...params.entries()]
        .filter(([key]) => key.match(/^categories(\[\d*\])?$/))
        .map(([, value]) => value),
});
const filterPopover = ref();

const destroy = (event: MouseEvent, item: OutgoingLetter | null) => {
    event.preventDefault();
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
            const url = !item ?
                route('outgoing-letter.mass-destroy') :
                route('outgoing-letter.destroy', item?.id);

            router.delete(url, {
                preserveScroll: true,
                preserveState: true,
                data: { ids: selected.value.map(i => i.id) },
                onSuccess: () => {
                    if (!item)
                        selected.value = [];
                }
            });
        },
    });
};

const searchFilter = () => {
    const url = new URL(window.location.href);
    url.search = '';
    filterForm.get(url.toString(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilter = () => {
    filterForm.agenda_number = null;
    filterForm.letter_number = null;
    filterForm.letter_date = [];
    filterForm.created_at = [];
    filterForm.recipient = null;
    filterForm.subject = null;
    filterForm.status = null;
    filterForm.categories = [];
    searchFilter();
};

const exportCsv = () => {
    filterForm.post(route('outgoing-letter.export'), {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head :title="$t('menu.outgoing_letter')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="mb-0.5 text-base font-medium">{{ $t('menu.outgoing_letter') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ $t('label.menu_subtitle', { menu: $t('menu.outgoing_letter') }) }}</p>
                </div>
                <div class="flex gap-x-2">
                    <template v-if="selected.length > 0">
                        <Button
                            v-if="page.props.auth.allow.delete_outgoing_letter"
                            :disabled="items.total === 1"
                            icon="pi pi-trash" @click="destroy($event, null)"
                            :label="$t('action.delete')" size="small" severity="danger" />
                    </template>
                    <template v-else>
                        <Button
                            v-if="page.props.auth.allow.view_outgoing_letter"
                            @click="exportCsv" severity="secondary" icon="pi pi-file-export"
                            :loading="filterForm.processing" :disabled="filterForm.processing"
                            :label="$t('action.export_csv')" size="small" />
                        <Button
                            v-if="page.props.auth.allow.add_outgoing_letter"
                            @click="() => uploadModal?.open()"
                            :label="$t('action.new_menu', { menu: $t('menu.outgoing_letter') })" size="small" />
                    </template>
                    <Button
                        @click="(e) => filterPopover.toggle(e)"
                        icon="pi pi-filter" severity="secondary" size="small" />
                </div>
            </header>

            <div class="flex flex-col w-full text-sm" v-if="items.data.length > 0">
                <Link
                    :href="route('outgoing-letter.show', item.id)"
                    v-for="item in items.data" :key="item.id"
                    class="flex items-center gap-x-5 group w-full min-h-[40px] border-b-1 border-b-gray-200 hover:shadow px-3">
                    <div @click="(e) => { e.stopPropagation(); }">
                        <Checkbox v-model="selected" :value="item" />
                    </div>
                    <div class="w-[125px] truncate">
                        <span v-tooltip.bottom="item.agenda_number">{{ item.agenda_number }}</span>
                    </div>
                    <div class="w-[200px] truncate" v-tooltip.bottom="item.recipient">{{ item.recipient }}</div>
                    <div class="w-64 py-2 flex-1 text-gray-400" v-tooltip.bottom="item.summary ?? item.body">
                        <div class="truncate">
                            <span class="text-gray-600">
                                {{ item.subject }}
                            </span>
                            <span class="mx-2" v-if="item.subject">-</span>
                            {{ item.body }}
                        </div>
                    </div>
                    <div class="hidden group-hover:block transition duration-300">
                        <div class="flex gap-x-1 items-center justify-center">
                            <a :href="item.file_url" download target="_blank">
                                <Button
                                    v-tooltip.bottom="$t('action.download')"
                                    icon="pi pi-download" size="small" variant="text" severity="secondary"
                                    v-if="page.props.auth.allow.view_outgoing_letter" rounded></Button>
                            </a>
                            <Button
                                v-tooltip.bottom="$t('action.edit')"
                                v-if="page.props.auth.allow.edit_outgoing_letter"
                                icon="pi pi-pencil" size="small" variant="text" severity="secondary"
                                @click="(e) => { e.preventDefault(); modal?.open(item); }" rounded></Button>
                            <Button
                                v-tooltip.bottom="$t('action.delete')"
                                v-if="page.props.auth.allow.delete_outgoing_letter"
                                icon="pi pi-trash" size="small" variant="text" severity="danger"
                                @click="destroy($event, item)" rounded></Button>
                        </div>
                    </div>
                    <div class="block group-hover:hidden transition duration-300">{{ dateHumanSmartFormat(item.created_at, locale) }}</div>
                </Link>
            </div>
            <p v-else class="text-center mt-5 text-gray-500">{{ $t('label.no_data_available', { data: $t('menu.outgoing_letter') }) }}</p>

            <Pagination :paginator="items" />

            <ConfirmPopup />

            <Popover ref="filterPopover" style="width: 30rem">
                <h3 class="font-medium text-gray-500">{{ $t('label.filter') }}</h3>

                <div class="grid gap-6 my-6">
                    <div class="grid">
                        <FloatLabel variant="on">
                            <InputText
                                :fluid="true" :autofocus="true" id="filter_letter_number"
                                v-model="filterForm.letter_number" autocomplete="off" />
                            <label for="filter_letter_number" class="text-sm">{{ $t('field.letter_number') }}</label>
                        </FloatLabel>
                    </div>
                    <div class="grid">
                        <FloatLabel variant="on">
                            <InputText
                                :fluid="true" id="filter_agenda_number"
                                v-model="filterForm.agenda_number" autocomplete="off" />
                            <label for="filter_agenda_number" class="text-sm">{{ $t('field.agenda_number') }}</label>
                        </FloatLabel>
                    </div>
                    <div class="grid">
                        <FloatLabel variant="on">
                            <DatePicker
                                :manual-input="false" selection-mode="range" date-format="dd/mm/yy"
                                :fluid="true" input-id="filter_letter_date"
                                v-model="filterForm.letter_date" />
                            <label for="filter_letter_date" class="text-sm">{{ $t('field.letter_date') }}</label>
                        </FloatLabel>
                    </div>
                    <div class="grid">
                        <FloatLabel variant="on">
                            <DatePicker
                                :manual-input="false" selection-mode="range" date-format="dd/mm/yy"
                                :fluid="true" input-id="filter_created_at"
                                v-model="filterForm.created_at" />
                            <label for="filter_created_at" class="text-sm">{{ $t('field.created_at') }}</label>
                        </FloatLabel>
                    </div>
                    <div class="grid">
                        <FloatLabel variant="on">
                            <InputText
                                :fluid="true" id="filter_recipient"
                                v-model="filterForm.recipient" autocomplete="off" />
                            <label for="filter_recipient" class="text-sm">{{ $t('field.recipient') }}</label>
                        </FloatLabel>
                    </div>
                    <div class="grid max-w-full">
                        <FloatLabel variant="on">
                            <MultiSelect
                                :max-selected-labels="3" show-clear :options="categories" option-label="name" option-value="code" filter
                                fluid input-id="filter_categories" v-model="filterForm.categories" />
                            <label for="filter_categories" class="text-sm">{{ $t('menu.letter_category') }}</label>
                        </FloatLabel>
                    </div>
                    <SelectButton
                        v-model="filterForm.status" size="small" :option-label="(value) => $t(`label.${value}`)"
                        :options="['reply_letter', 'follow_up_letter']" />
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" size="small" :label="$t('action.reset')" severity="secondary" @click="resetFilter"></Button>
                    <Button
                        type="button"
                        size="small"
                        :label="$t('action.filter')"
                        @click="searchFilter"
                    ></Button>
                </div>
            </Popover>

            <FormModal
                :categories
                ref="modal" />
            <UploadModal
                :categories
                ref="uploadModal" />
        </div>
    </AppLayout>
</template>
