<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('CUST-####'),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => 'active',
            'address' => $this->faker->address(),
        ];
    }
}
