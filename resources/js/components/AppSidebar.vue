<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Folder, LayoutGrid, Users2Icon, Briefcase,
    InboxIcon, LayoutPanelLeftIcon, SendIcon,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData>();

const mainNavItems: NavItem[] = [
    {
        title: 'menu.dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
        isAvailable: true,
        isActive: route().current('dashboard')
    },
    {
        title: 'menu.incoming_letter',
        href: route('incoming-letter.index'),
        icon: InboxIcon,
        isAvailable: page.props.auth.allow.view_incoming_letter,
        isActive: route().current('incoming-letter.*')
    },
    {
        title: 'menu.outgoing_letter',
        href: route('outgoing-letter.index'),
        icon: SendIcon,
        isAvailable: page.props.auth.allow.view_outgoing_letter,
        isActive: route().current('outgoing-letter.*')
    },
    {
        title: 'menu.letter_category',
        href: route('letter-category.index'),
        icon: LayoutPanelLeftIcon,
        isAvailable: page.props.auth.allow.view_letter_category,
        isActive: route().current('letter-category.*')
    },
    {
        title: 'menu.user',
        href: route('user.index'),
        icon: Users2Icon,
        isAvailable: page.props.auth.allow.view_user,
        isActive: route().current('user.*')
    },
    {
        title: 'menu.role',
        href: route('role.index'),
        icon: Briefcase,
        isAvailable: page.props.auth.allow.view_role,
        isActive: route().current('role.*')
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Nagara',
        href: 'https://nagara.site',
        icon: Folder,
        isAvailable: true,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
