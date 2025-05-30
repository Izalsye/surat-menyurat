import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    allow: Allow;
}

export interface Allow {
    view_user: boolean;
    add_user: boolean;
    edit_user: boolean;
    delete_user: boolean;
    view_role: boolean;
    add_role: boolean;
    edit_role: boolean;
    delete_role: boolean;
    view_incoming_letter: boolean;
    add_incoming_letter: boolean;
    edit_incoming_letter: boolean;
    delete_incoming_letter: boolean;
    view_outgoing_letter: boolean;
    add_outgoing_letter: boolean;
    edit_outgoing_letter: boolean;
    delete_outgoing_letter: boolean;
    view_letter_category: boolean;
    add_letter_category: boolean;
    edit_letter_category: boolean;
    delete_letter_category: boolean;
    view_disposition: boolean;
    add_disposition: boolean;
    edit_disposition: boolean;
    delete_disposition: boolean;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    isAvailable: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    flash: { success: string|null; error: string|null; };
    assistantAIAvailability: boolean;
}

export interface Link {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginate<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Link[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface APIResponse<T> {
    data: T;
    message: string;
    error: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    locale: 'id' | 'en' | 'ko' | 'ja' | 'ar' | 'zh-CN';
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Session {
    id: string;
    ip_address: string;
    user_agent: string;
    is_current: boolean;
    last_active: Date;
}

export interface ParsedUserAgent {
    os: string;
    browser: string;
    version: string;
    deviceType: 'Desktop' | 'Mobile' | 'Tablet' | 'Unknown';
}

export interface Role {
    id: number;
    name: string;
    guard_name: 'web';
    created_at: Date;
    updated_at: Date;
}

export interface Permission {
    id: number;
    name: string;
    created_at: Date;
    updated_at: Date;
}

export interface IncomingLetter {
    id: string;
    letter_number: string | null;
    agenda_number: string;
    letter_date: Date | string | null;
    sender: string | null;
    institution: string | null;
    subject: string | null;
    body: string | null;
    summary: string | null;
    is_draft: boolean;
    file: string;
    file_url: string;
    created_by: number | null;
    created_at: Date;
    updated_at: Date;
}

export interface ActivityLog {
    id: int;
    log_name: string;
    description: string;
    subject_id: string | number;
    subject_type: string;
    causer_id: number;
    causer_type: string;
    module: string;
    properties: object;
    created_at: Date;
    updated_at: Date;
}

export interface LetterCategory {
    id: number;
    code: string;
    name: string;
    description: string;
    created_at: Date;
    updated_at: Date;
}

export interface Disposition {
    id: string;
    incoming_letter_id: string;
    assignee_id: number;
    assigner_id: number;
    description: string;
    is_done: boolean;
    reply_letter: boolean;
    urgency: string;
    due_at: Date | null;
    created_at: Date;
    updated_at: Date;
}

export interface OutgoingLetter {
    id: string;
    letter_number: string | null;
    agenda_number: string;
    letter_date: Date | string | null;
    recipient: string | null;
    subject: string | null;
    body: string | null;
    summary: string | null;
    is_draft: boolean;
    file: string;
    file_url: string;
    created_by: number | null;
    disposition_id: string | null;
    incoming_letter_id: string | null;
    created_at: Date;
    updated_at: Date;
}
