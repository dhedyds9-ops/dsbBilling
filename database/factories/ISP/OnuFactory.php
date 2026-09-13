<?php

namespace Database\Factories\ISP;

use App\Models\ISP\Onu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Onu>
 */
class OnuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mac_address' => $this->faker->unique()->macAddress(),
            'sn' => $this->faker->unique()->regexify('[A-Z0-9]{12}'),
            'status' => 'online',
        ];
    }
}
