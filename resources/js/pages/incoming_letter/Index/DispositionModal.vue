<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Dialog, Button, FloatLabel, Message,
    Textarea, DatePicker, Select, Skeleton,
    Checkbox, RadioButton,
} from 'primevue';
import { APIResponse, Disposition, IncomingLetter, SharedData, User } from '@/types';
import axios from 'axios';

const visible = ref<boolean>(false);
const recommendationLoading = ref<boolean>(false);
const letter = ref<IncomingLetter | null>(null);
const assignees = ref<User[]>([]);

const page = usePage<SharedData>();

const form = useForm<{
    [key: string]: any;
    _method: 'POST' | 'PUT';
    id: string;
    incoming_letter_id: string;
    assignee_id: number | null;
    assigner_id: number;
    description: string;
    is_done: boolean;
    reply_letter: boolean;
    due_at: Date | null;
    parent_id: string | null;
    urgency: string;
}>({
    _method: 'POST',
    id: '',
    assignee_id: null, assigner_id: page.props.auth.user.id,
    description: '', due_at: null,
    incoming_letter_id: '', is_done: false,
    reply_letter: false, parent_id: null,
    urgency: 'normal',
});


const open = (incoming: IncomingLetter, item: Disposition | null, parent: Disposition | null = null) => {
    visible.value = true;
    letter.value = incoming;

    form.incoming_letter_id = incoming.id;
    form.parent_id = parent?.id ?? null;

    if (item === null)
        return;

    form._method = 'PUT';
    form.id = item.id;
    form.assignee_id = item.assignee_id;
    form.assigner_id = item.assigner_id;
    form.description = item.description;
    form.is_done = item.is_done;
    form.reply_letter = item.reply_letter;
    form.due_at = item.due_at;
    form.urgency = item.urgency;
};

const close = () => {
    visible.value = false;
    form.reset();
    letter.value = null;
};

const recommendation = async () => {
    if (letter.value === null)
        return;
    const url = letter.value.file_url;

    recommendationLoading.value = true;

    const response = await fetch(url);
    const blob = await response.blob();

    const name = url.split('/').pop() || 'file';
    const file = new File([blob], name, { type: blob.type });

    const body = new FormData();
    body.append('file', file);

    axios.post(route('internal-api.incoming-letter.disposition'), body)
        .then(response => {
            const data = response.data as APIResponse<{description: string; reply_letter: boolean; urgency: string}>;
            form.description = data.data.description;
            form.reply_letter = data.data.reply_letter;
            form.urgency = data.data.urgency;
        }).finally(() => {
            recommendationLoading.value = false;
        });

};

const submit = () => {
    const url = form.id === '' ?
        route('incoming-letter.disposition.store', letter.value?.id) :
        route('incoming-letter.disposition.update', { letter: letter.value?.id, disposition: form.id });
    form.post(url, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: close,
    });
};


defineExpose({
    open,
});

onMounted(() => {
    fetch(route('internal-api.user.disposition-assignee'))
        .then(response => response.json())
        .then((data: APIResponse<User[]>) => {
            assignees.value = data.data; //.filter(user => user.id !== page.props.auth.user.id);
        })
});
</script>

<template>
    <Dialog
        v-model:visible="visible" modal
        @after-hide="close" :header="$t('menu.disposition')" :style="{ width: '50rem' }">
        <div class="flex flex-col gap-6 pt-2 pb-8">
            <div class="grid w-full gap-6 lg:grid-cols-2">
                <div class="grid">
                    <FloatLabel variant="on">
                        <Select
                            :options="assignees.filter(user => user.id !== form.assigner_id)" option-label="name" option-value="id" filter
                            fluid input-id="assignee_id" v-model="form.assignee_id" />
                        <label for="assignee_id" class="text-sm">{{ $t('field.assignee') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.assignee_id" severity="error" size="small" variant="simple">
                        {{ form.errors.assignee_id }}
                    </Message>
                </div>
                <div class="grid">
                    <FloatLabel variant="on">
                        <DatePicker
                            date-format="dd/mm/yy" :disabled="!form.reply_letter"
                            fluid input-id="due_at" :min-date="new Date()" v-model="form.due_at" />
                        <label for="due_at" class="text-sm">{{ $t('field.due_date') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.due_at" severity="error" size="small" variant="simple">
                        {{ form.errors.due_at }}
                    </Message>
                </div>
            </div>
            <div class="grid relative">
                <div v-if="page.props.assistantAIAvailability" class="absolute top-1 right-1 z-50 opacity-50 hover:opacity-100">
                    <Button
                        v-tooltip.bottom="$t('label.disposition_recommendation')"
                        type="button" size="small" :disabled="recommendationLoading"
                        :icon="recommendationLoading ? `pi pi-spinner-dotted animate-spin` : `pi pi-sparkles `" rounded
                        severity="secondary" @click="recommendation"
                    ></Button>
                </div>
                <Skeleton height="4.125rem" v-if="recommendationLoading" />
                <FloatLabel variant="on" v-else>
                    <Textarea
                        fluid id="description" v-model="form.description" />
                    <label for="description" class="text-sm">{{ $t('field.description') }}</label>
                </FloatLabel>
                <Message v-if="form.errors.description" severity="error" size="small" variant="simple">
                    {{ form.errors.description }}
                </Message>
            </div>
            <div class="grid">
                <Skeleton height="2.625rem" v-if="recommendationLoading" />
                <div class="flex flex-wrap gap-4" v-else>
                    <div class="flex items-center gap-2"
                         v-for="urgency in ['normal', 'important', 'urgent', 'immediate', 'confidential', 'top_secret']"
                         :key="urgency">
                        <RadioButton v-model="form.urgency" :input-id="`label.${urgency}`" :name="urgency" :value="urgency" />
                        <label :for="`label.${urgency}`">{{ $t(`label.${urgency}`) }}</label>
                    </div>
                </div>
                <Message v-if="form.errors.urgency" severity="error" size="small" variant="simple">
                    {{ form.errors.urgency }}
                </Message>
            </div>
            <Skeleton height="1.65rem" width="183px" v-if="recommendationLoading" />
            <div class="flex items-center gap-2" v-else>
                <Checkbox
                    @value-change="(value: boolean) => { if (!value) form.due_at = null }"
                    v-model="form.reply_letter" input-id="reply_letter" binary />
                <label for="reply_letter">{{ $t('label.create_reply_letter') }}</label>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button type="button" size="small" :label="$t('action.cancel')" severity="secondary" @click="close"></Button>
            <Button type="button" size="small" :label="$t('action.submit')" :loading="form.processing" :disabled="form.processing" @click="submit"></Button>
        </div>
    </Dialog>
</template>
