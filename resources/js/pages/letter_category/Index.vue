<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FormModal from '@/pages/letter_category/Index/FormModal.vue';
import Pagination from '@/components/Pagination.vue';
import type { BreadcrumbItem, LetterCategory, Paginate, SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import { Button, Column, ConfirmPopup, DataTable, InputText, useConfirm } from 'primevue';
import { FilterMatchMode } from '@primevue/core/api';
import { useI18n } from 'vue-i18n';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.letter_category',
        href: route('letter-category.index'),
    },
];

defineProps<{
    items: Paginate<LetterCategory>;
}>();

const page = usePage<SharedData>();
const { t } = useI18n();

const selected = ref<LetterCategory[]>([]);
const filters = ref({});
const modal = ref();
const confirm = useConfirm();

const destroy = (event: MouseEvent, item: LetterCategory | null) => {
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
                route('letter-category.mass-destroy') :
                route('letter-category.destroy', item?.id);

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

const initFilter = () => {
    filters.value = {
        code: { value: null, matchMode: FilterMatchMode.EQUALS },
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};

watch(filters, (newFilters) => {
    router.reload({
        only: ['items'],
        data: { filter: newFilters },
        replace: true,
    });
}, { deep: true });

onMounted(initFilter);
</script>

<template>
    <Head :title="$t('menu.letter_category')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="mb-0.5 text-base font-medium">{{ $t('menu.letter_category') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ $t('label.menu_subtitle', { menu: $t('menu.letter_category') }) }}</p>
                </div>
                <div class="flex gap-x-2">
                    <template v-if="selected.length > 0">
                        <Button
                            v-if="page.props.auth.allow.delete_letter_category"
                            icon="pi pi-trash" @click="destroy($event, null)"
                            :label="$t('action.delete')" size="small" severity="danger" />
                    </template>
                    <template v-else>
                        <Button
                            v-if="page.props.auth.allow.add_letter_category"
                            @click="() => modal?.open(null)"
                            :label="$t('action.new_menu', { menu: $t('menu.letter_category') })" size="small" />
                    </template>
                    <Button
                        @click="router.reload({ only: ['items'], data: { filter: null }, replace: true, onSuccess: initFilter })"
                        icon="pi pi-filter-slash" severity="secondary" size="small" />
                </div>
            </header>

            <DataTable
                :value="items.data"
                :global-filter-fields="['code', 'name']"
                v-model:selection="selected"
                v-model:filters="filters"
                table-style="min-width: 50rem"
                filter-display="menu" scrollable
                lazy striped-rows show-gridlines>
                <Column selection-mode="multiple" header-style="width: 3rem" />
                <Column field="code" :header="$t('field.code')">
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" :placeholder="$t('label.search_by_field', { field: $t('field.code') })" />
                    </template>
                </Column>
                <Column field="name" :header="$t('field.name')">
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" :placeholder="$t('label.search_by_field', { field: $t('field.name') })" />
                    </template>
                </Column>
                <Column field="description" :header="$t('field.description')">
                </Column>
                <Column class="w-24 !text-end">
                    <template #body="{ data }: { data: LetterCategory }">
                        <div class="flex gap-x-2 items-center justify-center">
                            <Button
                                icon="pi pi-pencil" size="small" variant="outlined" severity="secondary"
                                v-if="page.props.auth.allow.edit_letter_category"
                                @click="() => modal?.open(data)" rounded></Button>
                            <Button
                                v-if="page.props.auth.allow.delete_letter_category"
                                icon="pi pi-trash" size="small" variant="outlined" severity="danger"
                                @click="destroy($event, data)" rounded></Button>
                        </div>
                    </template>
                </Column>
                <template #empty>{{ $t('label.no_data_available', { data: $t('menu.letter_category') }) }}</template>
            </DataTable>

            <Pagination :paginator="items" />

            <ConfirmPopup />

            <FormModal
                ref="modal" />
        </div>
    </AppLayout>
</template>
