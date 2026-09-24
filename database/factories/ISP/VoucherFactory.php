<?php

namespace Database\Factories\ISP;

use App\Models\ISP\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
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
            'code' => $this->faker->unique()->lexify('????-????'),
            'voucher_pool_id' => 1,
            'status' => 'available',
        ];
    }
}
