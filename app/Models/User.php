<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoleEnum;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, SaleReturn> $saleReturns
 * @property-read Collection<int, PurchaseReturn> $purchaseReturns
 * @property-read Collection<int, Invoice> $invoices
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, Expense> $expenses
 * @property-read Collection<int, StockMovement> $stockMovements
 * @property-read Collection<int, StockTransfer> $stockTransfers
 * @property-read Collection<int, Moneybox> $moneyboxes
 * @property-read Collection<int, MoneyboxTransaction> $moneyboxTransactions
 */
final class User extends Authenticatable
{
    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    /**
     * @return HasMany<Purchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class, 'created_by');
    }

    /**
     * @return HasMany<PurchaseReturn, $this>
     */
    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'created_by');
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    /**
     * @return HasMany<StockTransfer, $this>
     */
    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'created_by');
    }

    /**
     * @return HasMany<Moneybox, $this>
     */
    public function moneyboxes(): HasMany
    {
        return $this->hasMany(Moneybox::class, 'created_by');
    }

    /**
     * @return HasMany<MoneyboxTransaction, $this>
     */
    public function moneyboxTransactions(): HasMany
    {
        return $this->hasMany(MoneyboxTransaction::class, 'created_by');
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'password' => 'hashed',
            'remember_token' => 'string',
            'role' => RoleEnum::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
