<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Company>
 */
final class CompanyQueryBuilder extends Builder
{
    public function withBusinessIdentifier(): self
    {
        return $this->whereNotNull('business_identifier_id')
            ->with('businessIdentifier');
    }

    public function withoutBusinessIdentifier(): self
    {
        return $this->whereNull('business_identifier_id');
    }

    public function withLogo(): self
    {
        return $this->whereNotNull('logo');
    }

    public function withoutLogo(): self
    {
        return $this->whereNull('logo');
    }

    public function searchByName(string $search): self
    {
        return $this->where('name', 'like', sprintf('%%%s%%', $search));
    }

    public function searchByEmail(string $search): self
    {
        return $this->where('email', 'like', sprintf('%%%s%%', $search));
    }

    public function searchByPhone(string $search): self
    {
        return $this->where(function (Builder $query) use ($search): void {
            $query->where('phone', 'like', sprintf('%%%s%%', $search))
                ->orWhere('phone_secondary', 'like', sprintf('%%%s%%', $search));
        });
    }

    public function search(string $search): self
    {
        return $this->where(function (Builder $query) use ($search): void {
            $query->where('name', 'like', sprintf('%%%s%%', $search))
                ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                ->orWhere('phone', 'like', sprintf('%%%s%%', $search))
                ->orWhere('city', 'like', sprintf('%%%s%%', $search))
                ->orWhere('country', 'like', sprintf('%%%s%%', $search));
        });
    }

    public function inCountry(string $country): self
    {
        return $this->where('country', $country);
    }

    public function inCity(string $city): self
    {
        return $this->where('city', $city);
    }

    public function inState(string $state): self
    {
        return $this->where('state', $state);
    }

    public function withCurrency(string $currency): self
    {
        return $this->where('currency', $currency);
    }

    public function inTimezone(string $timezone): self
    {
        return $this->where('timezone', $timezone);
    }

    public function hasWebsite(): self
    {
        return $this->whereNotNull('website');
    }

    public function hasCompleteAddress(): self
    {
        return $this->whereNotNull('address')
            ->whereNotNull('city')
            ->whereNotNull('country');
    }
}
