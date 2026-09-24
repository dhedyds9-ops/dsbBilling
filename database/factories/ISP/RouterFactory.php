<?php

namespace Database\Factories\ISP;

use App\Models\ISP\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Router>
 */
class RouterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'code' => $this->faker->unique()->word,
            'ip_address' => $this->faker->ipv4,
            'username' => 'admin',
            'password' => 'password',
            'api_port' => 8728,
            'status' => 'active',
        ];
    }
}
