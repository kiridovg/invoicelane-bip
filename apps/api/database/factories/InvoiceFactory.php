<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $net = $this->faker->randomFloat(2, 100, 50000);
        $vat = round($net * 0.20, 2);

        $issueDate = $this->faker->dateTimeBetween('-90 days', 'now');
        $dueDate   = (clone $issueDate)->modify('+' . $this->faker->numberBetween(7, 45) . ' days');

        return [
            'number'          => 'INV-2026-' . $this->faker->unique()->numberBetween(100, 9999),
            'supplier_name'   => $this->faker->company(),
            'supplier_tax_id' => $this->faker->numerify('##########'),
            'net_amount'      => $net,
            'vat_amount'      => $vat,
            'gross_amount'    => round($net + $vat, 2),
            'currency'        => 'UAH',
            'status'          => $this->faker->randomElement(InvoiceStatus::cases()),
            'issue_date'      => $issueDate,
            'due_date'        => $dueDate,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => InvoiceStatus::Pending]);
    }
}
