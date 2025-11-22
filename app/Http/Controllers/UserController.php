<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Actions\DeleteUser;
use App\Actions\UpdateUser;
use App\Enums\RoleEnum;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
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
            'users' => UserResource::collection($users),
            'available_roles' => RoleEnum::toArray(),
        ]);
    }

    public function store(CreateUserRequest $request, CreateUser $action): RedirectResponse
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $request->safe()->except(['password', 'role']);

        $user = $action->handle(
            $attributes,
            $request->string('password')->value(),
        );

        if ($request->has('role')) {
            $user->assignRole($request->string('role')->value());
        }

        return back();
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $action): RedirectResponse
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $request->safe()->except(['role']);

        $action->handle(
            $user,
            $attributes,
        );

        if ($request->has('role')) {
            $user->syncRoles([$request->string('role')->value()]);
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
