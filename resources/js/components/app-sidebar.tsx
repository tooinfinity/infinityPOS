import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { dashboard } from '@/wayfinder/routes';
import customers from '@/wayfinder/routes/customers';
import purchaseReturns from '@/wayfinder/routes/purchase-returns';
import purchases from '@/wayfinder/routes/purchases';
import saleReturns from '@/wayfinder/routes/sale-returns';
import sales from '@/wayfinder/routes/sales';
import suppliers from '@/wayfinder/routes/suppliers';
import { Link } from '@inertiajs/react';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    ShoppingCart,
    UserRoundMinus,
    UserRoundPlus,
    Van,
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Customers',
        href: customers.index(),
        icon: UserRoundPlus,
    },
    {
        title: 'Sales',
        href: sales.index(),
        icon: ShoppingCart,
    },
    {
        title: 'Sales Returns',
        href: saleReturns.index(),
        icon: ShoppingCart,
    },
    {
        title: 'Suppliers',
        href: suppliers.index(),
        icon: UserRoundMinus,
    },
    {
        title: 'Purchases',
        href: purchases.index(),
        icon: Van,
    },
    {
        title: 'Purchases Returns',
        href: purchaseReturns.index(),
        icon: Van,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
