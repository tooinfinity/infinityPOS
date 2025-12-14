<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatusEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;
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
        $subtotal = $this->faker->numberBetween(10000, 200000); // cents
        $discount = $this->faker->numberBetween(0, (int) ($subtotal * 0.2));
        $tax = (int) (($subtotal - $discount) * 0.2);
        $total = $subtotal - $discount + $tax;
        $paid = $this->faker->randomElement([
            0,
            $total,
            (int) ($total * 0.9),
        ]);

        $issuedAt = $this->faker->dateTimeBetween('-2 months', 'now');
        $dueAt = (clone $issuedAt)->modify('+'.random_int(7, 30).' days');

        return [
            'reference' => mb_strtoupper(Str::random(10)),
            'sale_id' => Sale::factory(),
            'client_id' => Client::factory(),
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'paid_at' => $paid >= $total ? $this->faker->dateTimeBetween($issuedAt, $dueAt) : null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'paid' => $paid,
            'status' => $paid >= $total
                ? InvoiceStatusEnum::PAID
                : ($dueAt < new DateTimeImmutable('now') ? InvoiceStatusEnum::PENDING : InvoiceStatusEnum::DRAFT),
            'notes' => $this->faker->optional()->sentence(8),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
