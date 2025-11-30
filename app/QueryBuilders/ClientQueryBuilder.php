<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Client>
 */
final class ClientQueryBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    public function withBalance(): self
    {
        return $this->where('balance', '>', 0);
    }

    public function withDebit(): self
    {
        return $this->where('balance', '<', 0);
    }

    public function withCredit(): self
    {
        return $this->where('balance', '>', 0);
    }

    public function withZeroBalance(): self
    {
        return $this->where('balance', '=', 0);
    }

    public function balanceGreaterThan(float $amount): self
    {
        return $this->where('balance', '>', $amount);
    }

    public function balanceLessThan(float $amount): self
    {
        return $this->where('balance', '<', $amount);
    }

    public function balanceBetween(float $min, float $max): self
    {
        return $this->whereBetween('balance', [$min, $max]);
    }

    public function withBusinessIdentifier(): self
    {
        return $this->whereNotNull('business_identifier_id')
            ->with('businessIdentifier');
    }

    public function withoutBusinessIdentifier(): self
    {
        return $this->whereNull('business_identifier_id');
    }

    public function hasSales(): self
    {
        return $this->has('sales');
    }

    public function hasInvoices(): self
    {
        return $this->has('invoices');
    }

    public function hasReturns(): self
    {
        return $this->has('saleReturns');
    }

    public function withSalesCount(): self
    {
        return $this->withCount('sales');
    }

    public function withInvoicesCount(): self
    {
        return $this->withCount('invoices');
    }

    public function withReturnsCount(): self
    {
        return $this->withCount('saleReturns');
    }

    public function withAllCounts(): self
    {
        return $this->withCount(['sales', 'invoices', 'saleReturns']);
    }

    public function searchByName(string $search): self
    {
        return $this->where('name', 'like', sprintf('%%%s%%', $search));
    }

    public function searchByPhone(string $search): self
    {
        return $this->where('phone', 'like', sprintf('%%%s%%', $search));
    }

    public function searchByEmail(string $search): self
    {
        return $this->where('email', 'like', sprintf('%%%s%%', $search));
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search): void {
            $query->where('name', 'like', sprintf('%%%s%%', $search))
                ->orWhere('phone', 'like', sprintf('%%%s%%', $search))
                ->orWhere('email', 'like', sprintf('%%%s%%', $search));
        });
    }

    public function topClients(int $limit = 10): self
    {
        return $this->withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->limit($limit);
    }

    public function recentlyActive(int $days = 30): self
    {
        return $this->whereHas('sales', function ($query) use ($days): void {
            $query->where('created_at', '>=', now()->subDays($days));
        });
    }

    public function inactiveClient(int $days = 90): self
    {
        return $this->whereDoesntHave('sales', function ($query) use ($days): void {
            $query->where('created_at', '>=', now()->subDays($days));
        });
    }

    public function orderByBalance(string $direction = 'desc'): self
    {
        return $this->orderBy('balance', $direction);
    }
}
