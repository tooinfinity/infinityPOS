<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\PosRegister;
use App\Services\Pos\PosConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePosRegisterConfigured
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access to setup endpoints.
        if ($request->routeIs('pos.register.*')) {
            return $next($request);
        }

        /** @var string $deviceId */
        $deviceId = $request->cookie(PosConfig::DEVICE_COOKIE_NAME, '');
        if ($deviceId === '') {
            // Device cookie middleware should run before this.
            return $next($request);
        }

        $register = PosRegister::query()->where('device_id', $deviceId)->first();

        // Not created yet -> send to setup.
        if (! $register instanceof PosRegister) {
            return to_route('pos.register.edit');
        }

        if ($register->configured_at === null) {
            return to_route('pos.register.edit');
        }

        return $next($request);
    }
}
