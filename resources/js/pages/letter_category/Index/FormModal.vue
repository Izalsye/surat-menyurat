<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog, Button, FloatLabel, InputText, Message,
    Textarea,
} from 'primevue';
import type { LetterCategory } from '@/types';

const visible = ref<boolean>(false);

const form = useForm<{
    [key: string]: any;
    _method: 'POST' | 'PUT';
    id: number;
    code: string;
    name: string;
    description: string;
}>({
    _method: 'POST',
    id: 0,
    code: '',
    name: '',
    description: '',
});


const open = (category: LetterCategory | null) => {
    visible.value = true;
    if (category === null)
        return;

    form._method = 'PUT';
    form.id = category.id;
    form.code = category.code;
    form.name = category.name;
    form.description = category.description;
};

const close = () => {
    visible.value = false;
    form.reset();
};

const submit = () => {
    const url = form.id === 0 ?
        route('letter-category.store') :
        route('letter-category.update', form.id);
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
        @after-hide="close" :header="$t('menu.letter_category')" :style="{ width: '50rem' }">
        <div class="flex flex-col gap-6 pt-2 pb-8">
            <div class="grid lg:grid-cols-3 gap-6">
                <div class="grid">
                    <FloatLabel variant="on">
                        <InputText
                            :fluid="true" :autofocus="true" id="code" :disabled="form._method === 'PUT'"
                            v-model="form.code" type="text" autocomplete="off" />
                        <label for="code" class="text-sm">{{ $t('field.code') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.code" severity="error" size="small" variant="simple">
                        {{ form.errors.code }}
                    </Message>
                </div>
                <div class="grid lg:col-span-2">
                    <FloatLabel variant="on">
                        <InputText
                            :fluid="true" id="name"
                            v-model="form.name" type="text" autocomplete="off" />
                        <label for="name" class="text-sm">{{ $t('field.name') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.name" severity="error" size="small" variant="simple">
                        {{ form.errors.name }}
                    </Message>
                </div>
            </div>
            <div class="grid lg:col-span-2">
                <FloatLabel variant="on">
                    <Textarea
                        fluid id="description"
                        v-model="form.description" />
                    <label for="name" class="text-sm">{{ $t('field.description') }}</label>
                </FloatLabel>
                <Message v-if="form.errors.description" severity="error" size="small" variant="simple">
                    {{ form.errors.description }}
                </Message>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button type="button" size="small" :label="$t('action.cancel')" severity="secondary" @click="close"></Button>
            <Button type="button" size="small" :label="$t('action.submit')" :loading="form.processing" :disabled="form.processing" @click="submit"></Button>
        </div>
    </Dialog>
</template>
