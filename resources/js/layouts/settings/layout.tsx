import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useLanguage } from '@/hooks/use-language';
import { usePermissions } from '@/hooks/use-permissions';
import { cn } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editPassword } from '@/routes/password';
import { edit } from '@/routes/user-profile';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface SettingsLayoutProps extends PropsWithChildren {
    wide?: boolean;
}

export default function SettingsLayout({
    children,
    wide = false,
}: SettingsLayoutProps) {
    const { __ } = useLanguage();
    const { can, isRole } = usePermissions();
    const { auth } = usePage<SharedData>().props;

    console.log('User permissions:', auth.user?.permissions);
    console.log('User roles:', auth.user?.roles);

    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const sidebarNavItems: NavItem[] = [
        ...(can('edit_profile')
            ? [
                  {
                      title: __('Profile'),
                      href: edit(),
                      icon: null,
                  },
              ]
            : []),
        ...(isRole('admin')
            ? [
                  {
                      title: __('Users'),
                      href: '/users',
                      icon: null,
                  },
              ]
            : []),
        ...(can('edit_password')
            ? [
                  {
                      title: __('Password'),
                      href: editPassword(),
                      icon: null,
                  },
              ]
            : []),
        ...(can('edit_appearance')
            ? [
                  {
                      title: __('Appearance'),
                      href: editAppearance(),
                      icon: null,
                  },
              ]
            : []),
    ];

    const currentPath = window.location.pathname;

    return (
        <div className="px-4 py-6">
            <Heading
                title={__('Settings')}
                description={__('Manage your profile and account settings')}
            />
            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${typeof item.href === 'string' ? item.href : item.href.url}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted':
                                        currentPath ===
                                        (typeof item.href === 'string'
                                            ? item.href
                                            : item.href.url),
                                })}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>
                <Separator className="my-6 lg:hidden" />
                <div className={cn('flex-1', wide ? 'w-full' : 'md:max-w-2xl')}>
                    <section
                        className={cn(
                            'space-y-12',
                            wide ? 'w-full' : 'max-w-xl',
                        )}
                    >
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
