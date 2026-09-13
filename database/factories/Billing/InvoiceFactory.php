<?php

namespace Database\Factories\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'invoice_number' => $this->faker->unique()->numerify('INV-####'),
            'total_amount' => 100000,
            'status' => 'unpaid',
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
        ];
    }
}
