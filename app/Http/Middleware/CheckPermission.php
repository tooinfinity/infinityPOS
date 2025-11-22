<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\PermissionEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! $request->user()) {
            return to_route('login');
        }

        $permissionEnums = array_map(
            fn (string $permission) => PermissionEnum::from($permission)->value,
            $permissions
        );

        abort_unless($request->user()->hasAnyPermission($permissionEnums), 403, 'Unauthorized access. You do not have the required permission.');

        return $next($request);
    }
}
