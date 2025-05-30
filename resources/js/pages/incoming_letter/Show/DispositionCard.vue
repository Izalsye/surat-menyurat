<script setup lang="ts">
import type {
    Disposition as DispositionBase, IncomingLetter, LetterCategory, OutgoingLetter, SharedData, User
} from '@/types';
import OutgoingLetterModal from '@/pages/outgoing_letter/Index/UploadModal.vue';
import DispositionModal from '@/pages/incoming_letter/Index/DispositionModal.vue';
import { Avatar, useConfirm } from 'primevue';
import { dateHumanFormat, dateHumanFormatWithTime, dateHumanSmartFormat } from '@/lib/utils';
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const page = usePage<SharedData>();
const dispositionModal = ref();
const outgoingModal = ref();

interface Disposition extends DispositionBase {
    assignee: User | null;
    assigner: User;
    children: Disposition[];
    replies: OutgoingLetter[];
}

const props = defineProps<{
    disposition: Disposition;
    letter: IncomingLetter;
    categories: LetterCategory[];
}>();

const confirm = useConfirm();
const { t } = useI18n();

const destroy = (event: MouseEvent, item: Disposition) => {
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
            router.delete(route('incoming-letter.disposition.destroy', {
                letter: props.letter.id,
                disposition: item.id,
            }), {
                preserveScroll: true,
                preserveState: true,
            });
        },
    });
};

const markAsDone = (item: Disposition) => {
    router.post(route('incoming-letter.disposition.mark-as-done', {
        letter: props.letter.id,
        disposition: item.id,
    }), {}, {
        preserveScroll: true,
        preserveState: true,
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
    <div>
        <section class="flex gap-x-3 justify-start items-start" :id="disposition.id">
            <div>
                <Avatar
                    class="w-full" size="normal"
                    :image="disposition.assigner.avatar"
                    shape="circle" />
            </div>
            <div class="flex-1">
                <div class="bg-card text-card-foreground flex flex-col gap-0.5 rounded-2xl border p-3 shadow-xs">
                    <div class="flex justify-between">
                        <div class="flex gap-1.5 items-center">
                            <h3 class="font-medium text-sm" :class="{ 'text-emerald-600': disposition.assigner.id === page.props.auth.user.id }">
                                {{ disposition.assigner.name }}
                            </h3>
                            <template v-if="disposition.assignee">
                                <i class="pi pi-arrow-right" style="font-size: 0.5rem"></i>
                                <h3 class="font-medium text-sm" :class="{ 'text-emerald-600': disposition.assignee.id === page.props.auth.user.id }">
                                    {{ disposition.assignee.name }}
                                </h3>
                            </template>
                        </div>
                        <span>
                            <i v-tooltip.left="$t(`label.${disposition.urgency}`)"
                               :class="urgencyIcon(disposition.urgency)"
                               style="font-size: 0.75rem"></i>
                        </span>
                    </div>
                    <small v-if="disposition.due_at && disposition.reply_letter" class="text-gray-600 text-xs">{{ $t('label.due_at', { due: dateHumanFormat(disposition.due_at) }) }}</small>
                    <p>{{ disposition.description }}</p>
                    <small class="text-end" v-if="disposition.reply_letter">
                        <a v-if="disposition.replies.length > 0 && page.props.auth.allow.view_outgoing_letter"
                           class="text-gray-500 me-2 cursor-pointer hover:underline hover:text-emerald-600" target="_blank"
                           :href="route('outgoing-letter.show', disposition.replies[0].id)">
                            {{ disposition.replies[0].agenda_number }}
                        </a>
                        <i
                            v-tooltip.bottom="$t(disposition.replies.length > 0 ? 'label.replied' : 'label.no_reply')"
                            class="pi pi-check"
                            :class="{ 'text-emerald-600': disposition.replies.length > 0, 'text-gray-500': disposition.replies.length === 0 }"
                            style="font-size: 0.75rem"></i>
                    </small>
                    <small class="text-end" v-else-if="disposition.is_done">
                        <i
                            v-tooltip.bottom="$t('label.disposition_done')"
                            class="pi pi-check font-semibold text-emerald-600"
                            style="font-size: 0.75rem"></i>
                    </small>
                </div>
                <div class="flex gap-x-3 px-3 mt-1 text-xs text-gray-600">
                    <span v-tooltip.bottom="dateHumanFormatWithTime(disposition.created_at, 0, page.props.auth.user.locale)">
                        {{ dateHumanSmartFormat(disposition.created_at) }}
                    </span>
                    <button
                        class="cursor-pointer hover:underline" @click="() => dispositionModal?.open(letter, disposition, null)"
                        v-if="(disposition.assignee?.id === page.props.auth.user.id || page.props.auth.allow.edit_disposition) && !disposition.is_done">
                        {{ $t('action.edit') }}
                    </button>
                    <button
                        class="cursor-pointer hover:underline" @click="() => dispositionModal?.open(letter, null, disposition)"
                        v-if="(disposition.assignee?.id === page.props.auth.user.id && page.props.auth.allow.add_disposition) && !disposition.is_done">
                        {{ $t('action.disposition') }}
                    </button>
                    <button
                        class="cursor-pointer hover:underline" @click="() => outgoingModal?.open(disposition.id, letter.id)"
                        v-if="(disposition.assignee?.id === page.props.auth.user.id || (disposition.assignee === null || disposition.assigner_id === page.props.auth.user.id)) && page.props.auth.allow.add_outgoing_letter && disposition.reply_letter && disposition.replies.length === 0">
                        {{ $t('action.reply_letter') }}
                    </button>
                    <button
                        class="cursor-pointer hover:underline" @click="markAsDone(disposition)"
                        v-if="(disposition.assignee?.id === page.props.auth.user.id || page.props.auth.allow.edit_disposition) && !disposition.is_done">
                        {{ $t('action.mark_as_done') }}
                    </button>
                    <button
                        class="cursor-pointer text-red-600 hover:underline" @click="destroy($event, disposition)"
                        v-if="disposition.assigner.id === page.props.auth.user.id || page.props.auth.allow.delete_disposition">
                        {{ $t('action.delete') }}
                    </button>
                </div>

            </div>
        </section>

        <DispositionModal
            ref="dispositionModal" />
        <OutgoingLetterModal
            :categories
            ref="outgoingModal" />

        <div v-if="disposition.children.length > 0" class="ms-10 mt-4 flex flex-col gap-y-4">
            <DispositionCard
                v-for="child in disposition.children" :key="child.id"
                :disposition="child" :letter :categories />
        </div>
    </div>

</template>
