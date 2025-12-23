<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Pos\PosConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePosDeviceCookie
{
    public const string COOKIE_NAME = PosConfig::DEVICE_COOKIE_NAME;

    /**
     * Ensure each device has a stable identifier for POS register/cart binding.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var string $deviceId */
        $deviceId = $request->cookie(self::COOKIE_NAME, '');

        if ($deviceId === '') {
            $deviceId = (string) Str::uuid();

            // Ensure this request can read it immediately.
            $request->cookies->set(self::COOKIE_NAME, $deviceId);

            // Queue cookie for 5 years.
            cookie()->queue(cookie()->make(self::COOKIE_NAME, $deviceId, 60 * 24 * 365 * 5));
        }

        return $next($request);
    }
}
