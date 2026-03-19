<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\PurchaseBuilder;
use App\Enums\MediaCollection;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read int $id
 * @property-read int $supplier_id
 * @property-read int $warehouse_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read PurchaseStatusEnum $status
 * @property-read CarbonInterface $purchase_date
 * @property-read int $total_amount
 * @property-read int $paid_amount
 * @property-read int $due_amount
 * @property-read PaymentStatusEnum $payment_status
 * @property-read string|null $note
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Supplier $supplier
 * @property-read Warehouse $warehouse
 * @property-read User|null $user
 * @property-read Collection<int, PurchaseItem> $items
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, StockMovement> $stockMovements
 * @property-read Collection<int, PurchaseReturn> $returns
 *
 * @method static PurchaseBuilder query()
 */
final class Purchase extends Model implements HasMedia
{
    /** @use HasFactory<PurchaseFactory> */
    use HasFactory;

    use InteractsWithMedia;

    public function newEloquentBuilder(mixed $query): PurchaseBuilder
    {
        return new PurchaseBuilder($query);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<PurchaseItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function activePayments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')->active();
    }

    /**
     * @return MorphMany<StockMovement, $this>
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * @return HasMany<PurchaseReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'supplier_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'status' => PurchaseStatusEnum::class,
            'purchase_date' => 'datetime',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => PaymentStatusEnum::class,
            'note' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::PurchaseAttachment->value)
            ->acceptsMimeTypes(MediaCollection::PurchaseAttachment->allowedMimeTypes())
            ->singleFile();
    }

    /**
     * @return Attribute<int, null>
     */
    protected function dueAmount(): Attribute
    {
        return Attribute::make(
            get: fn (): int => max(0, $this->total_amount - $this->paid_amount),
        );
    }

    /**
     * @return Attribute<array{id: int, name: string, url: string, size: string, mime: string, extension: string, is_image: bool}|null, never>
     */
    protected function attachment(): Attribute
    {
        return Attribute::make(
            get: fn (): ?array => $this->getAttachmentData(),
        );
    }

    /**
     * @return array{id: int, name: string, url: string, size: string, mime: string, extension: string, is_image: bool}|null
     */
    private function getAttachmentData(): ?array
    {
        $media = $this->getFirstMedia(MediaCollection::PurchaseAttachment->value);

        if (! $media instanceof Media) {
            return null;
        }

        return [
            'id' => $media->id,
            'name' => $media->file_name,
            'url' => $media->getUrl(),
            'size' => $media->human_readable_size,
            'mime' => $media->mime_type,
            'extension' => $media->extension,
            'is_image' => str_starts_with($media->mime_type, 'image/'),
        ];
    }
}
