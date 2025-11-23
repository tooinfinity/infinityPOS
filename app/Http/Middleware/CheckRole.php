<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return to_route('login');
        }

        $roleEnums = array_map(
            fn (string $role) => RoleEnum::from($role)->value,
            $roles
        );

        abort_unless($request->user()->hasAnyRole($roleEnums), 403, 'Unauthorized access. You do not have the required role.');

        return $next($request);
    }
}
