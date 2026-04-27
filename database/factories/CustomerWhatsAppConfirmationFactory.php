<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerWhatsAppConfirmation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerWhatsAppConfirmation>
 */
class CustomerWhatsAppConfirmationFactory extends Factory
{
    public function definition(): array
    {
        $confirmedAt = $this->faker->dateTimeBetween('-6 months', '-1 day');

        return [
            'customer_id' => Customer::factory(),
            'phone' => '628'.$this->faker->numerify('#########'),
            'confirmed_at' => $confirmedAt,
            'last_received_at' => $this->faker->dateTimeBetween($confirmedAt, 'now'),
        ];
    }
}
