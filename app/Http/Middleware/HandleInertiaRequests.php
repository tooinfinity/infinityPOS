<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Override;

final class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * @see https://inertiajs.com/asset-versioning
     */
    #[Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function share(Request $request): array
    {
        $quote = Inspiring::quotes()->random();
        assert(is_string($quote));

        [$message, $author] = str($quote)->explode('-');

        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => mb_trim((string) $message), 'author' => mb_trim((string) $author)],
            'auth' => [
                'user' => $user ? $this->serializeUser($user) : null,
            ],
            'locale' => app()->getLocale(),
            'language' => $this->loadTranslations(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Serialize user with permissions and roles for frontend.
     *
     * @return array<string, mixed>
     */
    private function serializeUser(\App\Models\User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->toArray(),
            'roles' => $user->getRoleNames()->values()->toArray(),
        ];
    }

    /**
     * Load and merge all translation files into one flat array.
     *
     * @return array<string, string>
     */
    private function loadTranslations(): array
    {
        $locale = app()->getLocale();
        $path = base_path('lang/'.$locale);

        /** @var array<string, string> */
        return collect(glob($path.'/*.php') ?: [])
            ->flatMap(fn (string $file): array => (array) require $file)
            ->filter(fn (mixed $value): bool => is_string($value))
            ->all();
    }
}
