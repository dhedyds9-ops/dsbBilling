<?php

namespace Database\Factories;

use App\Models\ISP\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouterFactory extends Factory
{
    protected $model = Router::class;

    public function definition(): array
    {
        return [
            'code' => 'RTR-' . $this->faker->unique()->numberBetween(100, 999),
            'name' => $this->faker->company . ' Router',
            'description' => $this->faker->sentence(),
            'model' => $this->faker->word(),
            'serial_number' => $this->faker->unique()->bothify('SN-####-####'),
            'ip_address' => $this->faker->ipv4(),
            'api_port' => 8728,
            'use_ssl' => false,
            'timeout' => 30,
            'username' => 'admin',
            'status' => 'active',
        ];
    }
}
