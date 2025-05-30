<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Role, User as UserBase } from '@/types';
import {
    Dialog, Button, FloatLabel, InputText, Message,
    MultiSelect,
} from 'primevue';

interface User extends UserBase {
    roles: Role[];
}

const visible = ref<boolean>(false);
defineProps<{
    roles: string[];
}>();

const form = useForm<{
    [key: string]: any;
    _method: 'POST' | 'PUT';
    id: number;
    name: string;
    email: string;
    roles: string[];
}>({
    _method: 'POST',
    id: 0,
    name: '',
    email: '',
    roles: [],
});


const open = (user: User | null) => {
    visible.value = true;
    if (user === null)
        return;

    form._method = 'PUT';
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.roles = user.roles.map(role => role.name);
};

const close = () => {
    visible.value = false;
    form.reset();
};

const submit = () => {
    const url = form.id === 0 ?
        route('user.store') :
        route('user.update', form.id);
    form.post(url, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: close,
    });
};


defineExpose({
    open,
});
</script>

<template>
    <Dialog
        v-model:visible="visible" modal
        @after-hide="close" :header="$t('menu.user')" :style="{ width: '50rem' }">
        <div class="flex flex-col gap-6 pt-2 pb-8">
            <div class="grid lg:grid-cols-2 gap-x-6 w-full">
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
                <div class="grid">
                    <FloatLabel variant="on">
                        <InputText
                            :fluid="true" id="email" :disabled="form.id > 0"
                            v-model="form.email" type="text" autocomplete="off" />
                        <label for="email" class="text-sm">{{ $t('field.email') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.email" severity="error" size="small" variant="simple">
                        {{ form.errors.email }}
                    </Message>
                </div>
            </div>
            <div class="grid">
                <FloatLabel variant="on">
                    <MultiSelect
                        v-model="form.roles" :options="roles" filter input-id="roles" fluid
                        :max-selected-labels="3" class="w-full md:w-80" />
                    <label for="roles" class="text-sm">{{ $t('field.role') }}</label>
                </FloatLabel>
                <Message v-if="form.errors.roles" severity="error" size="small" variant="simple">
                    {{ form.errors.roles }}
                </Message>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button type="button" size="small" :label="$t('action.cancel')" severity="secondary" @click="close"></Button>
            <Button type="button" size="small" :label="$t('action.submit')" :loading="form.processing" :disabled="form.processing" @click="submit"></Button>
        </div>
    </Dialog>
</template>
