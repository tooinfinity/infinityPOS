<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\UpdateUser;
use App\Data\Users\CreateUserData;
use App\Data\Users\UpdateUserData;
use App\Data\Users\UserData;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserController
{
    public function index(): Response
    {
        $users = User::with('roles')
            ->where('id', '!=', auth()->id())
            ->latest()
            ->paginate(20);

        return Inertia::render('user/index', [
            'users' => UserData::collect($users),
            'available_roles' => RoleEnum::toArray(),
        ]);
    }

    public function store(CreateUserData $data, CreateUser $action): RedirectResponse
    {
        $user = $action->handle($data);

        if ($data->role instanceof RoleEnum) {
            $user->assignRole($data->role->value);
        }

        return back();
    }

    public function update(UpdateUserData $data, User $user, UpdateUser $action): RedirectResponse
    {
        $action->handle(
            $user,
            $data,
        );

        if ($data->role instanceof RoleEnum) {
            $user->syncRoles([$data->role->value]);
        }

        return back();
    }

    public function destroy(User $user, DeleteUser $action): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['message' => __('You cannot delete your own account.')]);
        }

        $action->handle($user);

        return back();
    }
}
