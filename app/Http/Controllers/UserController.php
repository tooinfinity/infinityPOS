<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Actions\DeleteUser;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserController
{
    public function create(): Response
    {
        return Inertia::render('user/create', [
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

    public function destroy(DeleteUserRequest $request, #[CurrentUser] User $user, DeleteUser $action): RedirectResponse
    {
        $action->handle($user);

        return back();
    }
}
