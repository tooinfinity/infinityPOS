<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Actions\DeleteUser;
use App\Actions\UpdateUser;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserController
{
    public function index(): Response
    {
        return Inertia::render('user/index', [
            'users' => User::query()->latest()->paginate(10),
        ]);
    }

    public function store(CreateUserRequest $request, CreateUser $action): RedirectResponse
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $request->safe()->except('password');

        $action->handle(
            $attributes,
            $request->string('password')->value(),
        );

        return back();
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $action): RedirectResponse
    {
        $action->handle(
            $user,
            $request->validated(),
        );

        return back();
    }

    public function destroy(User $user, DeleteUser $action): RedirectResponse
    {
        $action->handle($user);

        return back();
    }
}
