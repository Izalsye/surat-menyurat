<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Permission, Role as RoleBase } from '@/types';
import {
    Dialog, Button, FloatLabel, InputText,
    DataTable, Column, Checkbox, Message,
} from 'primevue';

interface Role extends RoleBase {
    permissions: Permission[];
}

const visible = ref<boolean>(false);
const props = defineProps<{
    modules: string[];
    actions: string[];
}>();

const form = useForm<{
    [key: string]: any;
    _method: 'POST' | 'PUT';
    id: number;
    name: string;
    permissions: string[];
}>({
    _method: 'POST',
    id: 0,
    name: '',
    permissions: [],
});


const open = (role: Role | null) => {
    visible.value = true;
    if (role === null)
        return;

    form._method = 'PUT';
    form.id = role.id;
    form.name = role.name;
    form.permissions = role.permissions.map(permission => permission.name);
};

const close = () => {
    visible.value = false;
    form.reset();
};

const submit = () => {
    const url = form.id === 0 ?
        route('role.store') :
        route('role.update', form.id);
    form.post(url, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: close,
    });
};

const selectAll = () => {
    if (form.permissions.length === (props.modules.length * props.actions.length)) {
        form.permissions = [];
    } else {
        const permission: string[] = [];
        for (const module of props.modules) {
            for (const action of props.actions) {
                permission.push(`${action}_${module}`);
            }
        }
        form.permissions = permission;
    }
};

defineExpose({
    open,
});
</script>

<template>
    <Dialog
        v-model:visible="visible" modal
        @after-hide="close" :header="$t('menu.role')" :style="{ width: '50rem' }">
        <div class="flex flex-col gap-6 pt-2 pb-8">
            <div class="grid">
                <FloatLabel variant="on">
                    <InputText
                        :fluid="true" :autofocus="true" id="name"
                        v-model="form.name" type="text" autocomplete="off" />
                    <label for="name" class="text-sm">{{ $t('field.name') }}</label>
                </FloatLabel>
                <Message v-if="form.errors.name" severity="error" size="small" variant="simple">
                    {{ form.errors.name }}
                </Message>
            </div>
            <DataTable :value="modules" tableStyle="min-width: 50rem">
                <Column field="pivot" header="">
                    <template #body="{ data }: { data: string }">
                        {{ $t(`menu.${data}`) }}
                    </template>
                    <template #header>
                        <Button
                            type="button" size="small" :disabled="form.permissions.length === (props.modules.length * props.actions.length)"
                            :label="$t('action.select_all')" @click="selectAll"></Button>
                        <Button
                            type="button" size="small" :disabled="form.permissions.length === 0"
                            :label="$t('action.select_none')" severity="secondary" @click="selectAll"></Button>
                    </template>
                </Column>
                <Column v-for="action in actions" :key="action" :header="$t(`action.${action}`)" :field="action">
                    <template #body="{ data }: { data: string }">
                        <Checkbox
                            v-model="form.permissions" :input-id="`${action}_${data}`"
                            :name="`${action}_${data}`" :value="`${action}_${data}`" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <div class="flex justify-end gap-2">
            <Button type="button" size="small" :label="$t('action.cancel')" severity="secondary" @click="close"></Button>
            <Button type="button" size="small" :label="$t('action.submit')" :loading="form.processing" :disabled="form.processing" @click="submit"></Button>
        </div>
    </Dialog>
</template>
