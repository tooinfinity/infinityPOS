<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Models\Moneybox;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Moneybox>
 */
final class MoneyboxQueryBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    public function withTransactions(): self
    {
        return $this->with('transactions');
    }

    public function withPayments(): self
    {
        return $this->with('payments');
    }

    public function withExpenses(): self
    {
        return $this->with('expenses');
    }

    public function withIncomingTransfers(): self
    {
        return $this->with('incomingTransfers');
    }

    public function withOutgoingTransfers(): self
    {
        return $this->with('outgoingTransfers');
    }

    public function searchByName(string $search): self
    {
        return $this->where('name', 'like', sprintf('%%%s%%', $search));
    }

    public function orderByCurrentBalance(string $direction = 'desc'): self
    {
        return $this->orderBy('current_balance', $direction);
    }

    public function recent(int $limit = 10): self
    {
        return $this->latest('created_at')
            ->limit($limit);
    }
}
