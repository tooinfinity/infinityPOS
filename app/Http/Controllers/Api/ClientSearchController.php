<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ClientSearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = Client::query()->where('is_active', true);

        // Search by name, phone, or email
        if ($request->filled('search')) {
            $search = $request->string('search')->value();
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('phone', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('email', 'like', sprintf('%%%s%%', $search));
            });
        }

        /** @var int $limit */
        $limit = min($request->get('limit', 50), 100);

        $clients = $query
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'email']);

        return response()->json([
            'data' => $clients,
        ]);
    }
}
