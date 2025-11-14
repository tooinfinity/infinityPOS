import UserController from '@/actions/App/Http/Controllers/UserController';
import { Form, Head, router } from '@inertiajs/react';
import { Edit, LoaderCircle, Trash } from 'lucide-react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { UserInfo } from '@/components/user-info';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { BreadcrumbItem, User } from '@/types';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Account Users settings',
        href: UserController.index().url,
    },
];

interface UsersProps {
    users: {
        data: User[];
    };
}
export default function Index({ users }: UsersProps) {
    const [editingUser, setEditingUser] = useState<User | null>(null);
    const [deletingUser, setDeletingUser] = useState<User | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = () => {
        if (!deletingUser) return;

        setIsDeleting(true);

        router.delete(UserController.destroy.url({ user: deletingUser.id }), {
            preserveScroll: true,
            onSuccess: () => {
                setDeletingUser(null);
            },
            onFinish: () => {
                setIsDeleting(false);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create new account" />
            <SettingsLayout wide={true}>
                <div className="flex flex-col gap-8 xl:flex-row">
                    <div className="flex-1 space-y-6">
                        <HeadingSmall
                            title="Create new account"
                            description="Create a new user account for accessing the application"
                        />
                        <Form
                            {...UserController.store.form()}
                            resetOnSuccess={[
                                'name',
                                'email',
                                'password',
                                'password_confirmation',
                            ]}
                            disableWhileProcessing
                            className="flex flex-col gap-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-6">
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="name">
                                                    Name
                                                </Label>
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
                                            <InputError
                                                message={errors.password}
                                            />
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
                                users.data.map((u: User, idx) => (
                                    <div
                                        key={idx}
                                        className="flex items-center gap-4 p-4"
                                    >
                                        <UserInfo user={u} showEmail={true} />
                                        <button className="ml-auto text-sm text-muted-foreground hover:text-muted-foreground/80">
                                            Role
                                        </button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => setEditingUser(u)}
                                            className="h-8 w-8"
                                        >
                                            <Edit className="h-4 w-4 text-muted-foreground" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => setDeletingUser(u)}
                                            className="h-8 w-8"
                                        >
                                            <Trash className="h-4 w-4 text-destructive hover:text-destructive/80" />
                                        </Button>
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

                {/* Edit User Dialog */}
                <Dialog
                    open={!!editingUser}
                    onOpenChange={() => setEditingUser(null)}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit User</DialogTitle>
                            <DialogDescription>
                                Update user information below
                            </DialogDescription>
                        </DialogHeader>
                        {editingUser && (
                            <Form
                                {...UserController.update.form({
                                    user: editingUser.id,
                                })}
                                onSuccess={() => setEditingUser(null)}
                                disableWhileProcessing
                                className="flex flex-col gap-6"
                            >
                                {({ processing, errors }) => (
                                    <div className="grid gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="edit-name">
                                                Name
                                            </Label>
                                            <Input
                                                id="edit-name"
                                                type="text"
                                                required
                                                autoFocus
                                                autoComplete="name"
                                                name="name"
                                                defaultValue={editingUser.name}
                                                placeholder="Full name"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="edit-email">
                                                Email address
                                            </Label>
                                            <Input
                                                id="edit-email"
                                                type="email"
                                                required
                                                autoComplete="email"
                                                name="email"
                                                defaultValue={editingUser.email}
                                                placeholder="email@example.com"
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="edit-password">
                                                Password
                                                <span className="ml-2 text-xs text-muted-foreground">
                                                    (leave blank to keep
                                                    current)
                                                </span>
                                            </Label>
                                            <Input
                                                id="edit-password"
                                                type="password"
                                                autoComplete="new-password"
                                                name="password"
                                                placeholder="New password"
                                            />
                                            <InputError
                                                message={errors.password}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="edit-password-confirmation">
                                                Confirm password
                                            </Label>
                                            <Input
                                                id="edit-password-confirmation"
                                                type="password"
                                                autoComplete="new-password"
                                                name="password_confirmation"
                                                placeholder="Confirm new password"
                                            />
                                            <InputError
                                                message={
                                                    errors.password_confirmation
                                                }
                                            />
                                        </div>

                                        <div className="flex gap-2 pt-4">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="flex-1"
                                                onClick={() =>
                                                    setEditingUser(null)
                                                }
                                                disabled={processing}
                                            >
                                                Cancel
                                            </Button>
                                            <Button
                                                type="submit"
                                                className="flex flex-1 items-center justify-center gap-2"
                                                disabled={processing}
                                            >
                                                {processing && (
                                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                                )}
                                                Update User
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </Form>
                        )}
                    </DialogContent>
                </Dialog>

                {/* Delete Confirmation Dialog */}
                <AlertDialog
                    open={!!deletingUser}
                    onOpenChange={() => setDeletingUser(null)}
                >
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Delete User</AlertDialogTitle>
                            <AlertDialogDescription>
                                Are you sure you want to delete{' '}
                                <strong>{deletingUser?.name}</strong>? This
                                action cannot be undone and will permanently
                                remove the user from the system.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel disabled={isDeleting}>
                                Cancel
                            </AlertDialogCancel>
                            <AlertDialogAction
                                onClick={handleDelete}
                                disabled={isDeleting}
                                className="bg-destructive hover:bg-destructive/90"
                            >
                                {isDeleting && (
                                    <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Delete User
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </SettingsLayout>
        </AppLayout>
    );
}
