import UserController from '@/actions/App/Http/Controllers/UserController';
import { Form, Head } from '@inertiajs/react';
import { Edit, LoaderCircle, Trash } from 'lucide-react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { BreadcrumbItem, User } from '@/types';
import { UserInfo } from '@/components/user-info';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Account Users settings',
        href: UserController.create().url,
    },
];

interface UsersProps {
    users: {
        data: User[];
    };
}
export default function Register({ users }: UsersProps) {
    console.log(users);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create new account" />
            <SettingsLayout wide={true}>
                <div className="flex flex-col xl:flex-row gap-8">
                    <div className="flex-1 space-y-6">
                        <HeadingSmall
                            title="Create new account"
                            description="Create a new user account for accessing the application"
                        />
                        <Form
                            {...UserController.store.form()}
                            resetOnSuccess={['name', 'email', 'password', 'password_confirmation']}
                            disableWhileProcessing
                            className="flex flex-col gap-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-6">
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="name">Name</Label>
                                                <Input
                                                    id="name"
                                                    type="text"
                                                    required
                                                    autoFocus
                                                    tabIndex={1}
                                                    autoComplete="name"
                                                    name="name"
                                                    placeholder="Full name"
                                                />
                                                <InputError
                                                    message={errors.name}
                                                    className="mt-2"
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="email">
                                                    Email address
                                                </Label>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    required
                                                    tabIndex={2}
                                                    autoComplete="email"
                                                    name="email"
                                                    placeholder="email@example.com"
                                                />
                                                <InputError
                                                    message={errors.email}
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="password">
                                                Password
                                            </Label>
                                            <Input
                                                id="password"
                                                type="password"
                                                required
                                                tabIndex={3}
                                                autoComplete="new-password"
                                                name="password"
                                                placeholder="Password"
                                            />
                                            <InputError message={errors.password} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="password_confirmation">
                                                Confirm password
                                            </Label>
                                            <Input
                                                id="password_confirmation"
                                                type="password"
                                                required
                                                tabIndex={4}
                                                autoComplete="new-password"
                                                name="password_confirmation"
                                                placeholder="Confirm password"
                                            />
                                            <InputError
                                                message={
                                                    errors.password_confirmation
                                                }
                                            />
                                        </div>

                                        <Button
                                            type="submit"
                                            className="mt-2 flex w-full items-center justify-center gap-2 md:w-auto md:self-start"
                                            tabIndex={5}
                                            data-test="register-user-button"
                                        >
                                            {processing && (
                                                <LoaderCircle className="h-4 w-4 animate-spin" />
                                            )}
                                            Create account
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                    <div className="flex-1 space-y-6">
                        <HeadingSmall
                            title="List of users"
                            description="Below is the list of all users with access to the application"
                        />
                        <div className="rounded-lg">
                            {users && users.data.length > 0 ? (
                                users.data.map((u : User,idx) => (
                                    <div key={idx} className="flex items-center gap-4 p-4">
                                        <UserInfo user={u} showEmail={true} />
                                        <button className="ml-auto text-sm text-muted-foreground hover:text-muted-foreground/80">Role</button>
                                        <Edit className={"h-4 w-4 text-muted-foreground"} />
                                        <Trash className="h-4 w-4 text-destructive hover:text-destructive/80" />
                                    </div>
                                ))
                            ) : (
                                <span className="text-sm text-muted-foreground">
                                    No users yet
                                </span>
                            )}
                        </div>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
