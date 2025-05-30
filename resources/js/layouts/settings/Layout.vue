<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: 'menu.profile',
        href: route('profile.edit'),
        isAvailable: true,
        isActive: route().current('profile.*'),
    },
    {
        title: 'menu.password',
        href: route('password.edit'),
        isAvailable: true,
        isActive: route().current('password.*'),
    },
    {
        title: 'menu.session',
        href: route('session.index'),
        isAvailable: true,
        isActive: route().current('session.*'),
    },
    {
        title: 'menu.appearance',
        href: route('appearance'),
        isAvailable: true,
        isActive: route().current('appearance'),
    },
];
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            :title="$t('menu.setting')"
            :description="$t('label.setting_subtitle')" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-x-0 space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': item.isActive }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ $t(item.title) }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
