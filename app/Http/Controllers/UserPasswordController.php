<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Authentication\CreateUserPassword;
use App\Actions\Authentication\UpdateUserPassword;
use App\Data\Authentication\CreateUserPasswordData;
use App\Data\Authentication\UpdateUserPasswordData;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserPasswordController
{
    public function create(Request $request): Response
    {
        return Inertia::render('user-password/create', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    public function store(CreateUserPasswordData $data, CreateUserPassword $action): RedirectResponse
    {
        /** @var array<string, mixed> $credentials */
        $credentials = [
            'email' => $data->email,
            'password' => $data->password,
            'password_confirmation' => $data->password_confirmation,
            'token' => $data->token,
        ];

        $status = $action->handle(
            $credentials,
            $data->password
        );

        throw_if($status !== Password::PASSWORD_RESET, ValidationException::withMessages([
            'email' => [__(is_string($status) ? $status : '')],
        ]));

        return to_route('login')->with('status', __('passwords.reset'));
    }

    public function edit(): Response
    {
        return Inertia::render('user-password/edit');
    }

    public function update(UpdateUserPasswordData $data, #[CurrentUser] User $user, UpdateUserPassword $action): RedirectResponse
    {
        $action->handle($user, $data->password);

        return back();
    }
}
