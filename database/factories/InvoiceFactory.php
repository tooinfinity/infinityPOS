<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use DateMalformedStringException;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Random\RandomException;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     *
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10, 2000);
        $discount = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        $tax = $this->faker->randomFloat(2, 0, ($subtotal - $discount) * 0.2);
        $total = round($subtotal - $discount + $tax, 2);
        $paid = $this->faker->randomElement([
            0.0,
            round($total, 2),
            round($total * $this->faker->randomFloat(2, 0.1, 0.9), 2),
        ]);

        $issuedAt = $this->faker->dateTimeBetween('-2 months', 'now');
        $dueAt = (clone $issuedAt)->modify('+'.random_int(7, 30).' days');

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'sale_id' => null,
            'client_id' => null,
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'paid_at' => $paid >= $total ? $this->faker->dateTimeBetween($issuedAt, $dueAt) : null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'paid' => $paid,
            'status' => $paid >= $total
                ? 'paid'
                : ($dueAt < new DateTimeImmutable('now') ? 'overdue' : 'sent'),
            'notes' => $this->faker->optional()->sentence(8),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    /**
     * Mark the invoice as fully paid.
     */
    public function paid(): self
    {
        return $this->state(function (array $attributes): array {
            $attributes['paid'] = $attributes['total'] ?? ($attributes['subtotal'] - ($attributes['discount'] ?? 0) + ($attributes['tax'] ?? 0));
            $attributes['paid_at'] ??= now();
            $attributes['status'] = 'paid';

            return $attributes;
        });
    }

    /**
     * Mark the invoice as overdue (unpaid past due date).
     */
    public function overdue(): self
    {
        return $this->state(function (array $attributes): array {
            $attributes['paid'] ??= 0.0;
            $attributes['status'] = 'overdue';
            $attributes['due_at'] = now()->subDays(random_int(1, 30));

            return $attributes;
        });
    }

    /**
     * Mark the invoice as partially paid.
     */
    public function partial(): self
    {
        return $this->state(function (array $attributes): array {
            $total = (float) ($attributes['total'] ?? 100.0);
            $attributes['paid'] = round($total * 0.5, 2);
            $attributes['status'] = 'sent';

            return $attributes;
        });
    }

    /**
     * Cancel the invoice.
     */
    public function cancelled(): self
    {
        return $this->state(fn (array $attributes): array => [
            ...$attributes,
            'status' => 'cancelled',
        ]);
    }
}
