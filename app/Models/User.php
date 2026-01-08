<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read int|null $store_id
 * @property-read bool $is_active
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return HasMany<Purchase, $this>
     */
    public function createdPurchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }

    /**
     * @return HasMany<Sale, $this>
     */
    public function cashierSales(): HasMany
    {
        return $this->hasMany(Sale::class, 'cashier_id');
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * @return HasMany<InvoicePayment, $this>
     */
    public function recordedInvoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'recorded_by');
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function processedReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class, 'processed_by');
    }

    /**
     * @return HasMany<StockAdjustment, $this>
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class, 'adjusted_by');
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function recordedExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'recorded_by');
    }

    /**
     * @return HasMany<CashTransaction, $this>
     */
    public function createdCashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'created_by');
    }

    /**
     * @return HasMany<RegisterSession, $this>
     */
    public function openedRegisterSessions(): HasMany
    {
        return $this->hasMany(RegisterSession::class, 'opened_by');
    }

    /**
     * @return HasMany<RegisterSession, $this>
     */
    public function closedRegisterSessions(): HasMany
    {
        return $this->hasMany(RegisterSession::class, 'closed_by');
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'store_id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
