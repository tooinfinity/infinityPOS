<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DeleteUser;
use App\Actions\UpdateUser;
use App\Data\DeleteUserData;
use App\Data\UpdateUserData;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserProfileController
{
    public function edit(Request $request): Response
    {
        return Inertia::render('user-profile/edit', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function update(UpdateUserData $data, #[CurrentUser] User $user, UpdateUser $action): RedirectResponse
    {
        $action->handle($user, $data);

        return to_route('user-profile.edit');
    }

    public function destroy(DeleteUserData $data, #[CurrentUser] User $user, DeleteUser $action): RedirectResponse
    {
        Auth::logout();

        $action->handle($user);

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return to_route('login');
    }
}
