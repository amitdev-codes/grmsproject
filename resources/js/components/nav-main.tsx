import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useTranslation } from '@/hooks/use-translation';
import type {NavItem} from '@/types';

function isActiveHref(currentUrl: string, href: NavItem['href']) {
    const url = typeof href === 'string' ? href : href.url;

    return url !== '#' && currentUrl.startsWith(url);
}

function hasActiveChild(currentUrl: string, item: NavItem): boolean {
    if (isActiveHref(currentUrl, item.href)) {
        return true;
    }

    return (
        item.items?.some((child) => hasActiveChild(currentUrl, child)) ?? false
    );
}

export function NavMain({
    items = [],
    label,
}: {
    items: NavItem[];
    label?: string;
}) {
    const page = usePage();

    const { t } = useTranslation();

    return (
        <SidebarGroup className="px-2 py-0">
            {label && (
                <SidebarGroupLabel className="text-[11px] font-medium tracking-wider text-muted-foreground/60 uppercase">
                    {t(label)}
                </SidebarGroupLabel>
            )}
            <SidebarMenu>
                {items.map((item) => {
                    // Parent item with children -> collapsible with a nested submenu
                    if (item.items && item.items.length > 0) {
                        const open = hasActiveChild(page.url, item);

                        return (
                            <Collapsible
                                key={item.title}
                                asChild
                                defaultOpen={open}
                                className="group/collapsible"
                            >
                                <SidebarMenuItem>
                                    <CollapsibleTrigger asChild>
                                        <SidebarMenuButton
                                            tooltip={{
                                                children: t(item.title),
                                            }}
                                            className="text-[13px] font-medium"
                                            isActive={open}
                                        >
                                            {item.icon && <item.icon />}
                                            <span>{t(item.title)}</span>
                                            <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                                        </SidebarMenuButton>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <SidebarMenuSub>
                                            {item.items.map((child) => (
                                                <SidebarMenuSubItem
                                                    key={child.title}
                                                >
                                                    <SidebarMenuSubButton
                                                        asChild
                                                        isActive={isActiveHref(
                                                            page.url,
                                                            child.href,
                                                        )}
                                                        className="text-[13px] font-medium"
                                                    >
                                                        <Link
                                                            href={child.href}
                                                            prefetch
                                                        >
                                                            {child.icon && (
                                                                <child.icon />
                                                            )}
                                                            <span>
                                                                {t(child.title)}
                                                            </span>
                                                        </Link>
                                                    </SidebarMenuSubButton>
                                                </SidebarMenuSubItem>
                                            ))}
                                        </SidebarMenuSub>
                                    </CollapsibleContent>
                                </SidebarMenuItem>
                            </Collapsible>
                        );
                    }

                    // Plain leaf item
                    return (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isActiveHref(page.url, item.href)}
                                tooltip={{ children: t(item.title) }}
                                className="text-[13px] font-medium"
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{t(item.title)}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
