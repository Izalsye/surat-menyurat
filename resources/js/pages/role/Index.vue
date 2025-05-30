<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import FormModal from '@/pages/role/Index/FormModal.vue';
import type { BreadcrumbItem, Paginate, Role as RoleBase, Permission, SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    useConfirm, ConfirmPopup, DataTable, Column,
    Button, InputText,
} from 'primevue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.role',
        href: route('role.index'),
    },
];

interface Role extends RoleBase {
    permissions: Permission[];
}

defineProps<{
    items: Paginate<Role>;
    modules: string[];
    actions: string[];
}>();

const page = usePage<SharedData>();
const { t } = useI18n();

const selected = ref<Role[]>([]);
const filters = ref({});
const modal = ref();
const confirm = useConfirm();

const destroy = (event: MouseEvent, item: Role | null) => {
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
                route('role.mass-destroy') :
                route('role.destroy', item?.id);

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
    <Head :title="$t('menu.role')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="mb-0.5 text-base font-medium">{{ $t('menu.role') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ $t('label.menu_subtitle', { menu: $t('menu.role') }) }}</p>
                </div>
                <div class="flex gap-x-2">
                    <template v-if="selected.length > 0">
                        <Button
                            v-if="page.props.auth.allow.delete_role"
                            :disabled="items.total === 1"
                            icon="pi pi-trash" @click="destroy($event, null)"
                            :label="$t('action.delete')" size="small" severity="danger" />
                    </template>
                    <template v-else>
                        <Button
                            v-if="page.props.auth.allow.add_role"
                            @click="() => modal?.open(null)"
                            :label="$t('action.new_menu', { menu: $t('menu.role') })" size="small" />
                    </template>
                    <Button
                        @click="router.reload({ only: ['items'], data: { filter: null }, replace: true, onSuccess: initFilter })"
                        icon="pi pi-filter-slash" severity="secondary" size="small" />
                </div>
            </header>

            <DataTable
                :value="items.data"
                :global-filter-fields="['name']"
                v-model:selection="selected"
                v-model:filters="filters"
                table-style="min-width: 50rem"
                filter-display="menu" scrollable
                lazy striped-rows show-gridlines>
                <Column selection-mode="multiple" header-style="width: 3rem" />
                <Column field="name" :header="$t('field.name')">
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" :placeholder="$t('label.search_by_field', { field: $t('field.name') })" />
                    </template>
                </Column>
                <Column class="w-24 !text-end">
                    <template #body="{ data }: { data: Role }">
                        <div class="flex gap-x-2 items-center justify-center">
                            <Button
                                icon="pi pi-pencil" size="small" variant="outlined" severity="secondary"
                                v-if="page.props.auth.allow.edit_role"
                                @click="() => modal?.open(data)" rounded></Button>
                            <Button
                                :disabled="items.total === 1"
                                v-if="page.props.auth.allow.delete_role"
                                icon="pi pi-trash" size="small" variant="outlined" severity="danger"
                                @click="destroy($event, data)" rounded></Button>
                        </div>
                    </template>
                </Column>
                <template #empty>{{ $t('label.no_data_available', { data: $t('menu.role') }) }}</template>
            </DataTable>

            <Pagination :paginator="items" />

            <ConfirmPopup />

            <FormModal
                :modules :actions
                ref="modal" />
        </div>
    </AppLayout>
</template>
