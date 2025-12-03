<?php

namespace Database\Factories;

use App\Models\Intention;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Intention>
 */
class IntentionFactory extends Factory
{
    protected $model = Intention::class;

    public function definition(): array
    {
        $types = ['acción de gracias','salud','difunto','familia','vocación'];
        $channels = ['web', 'counter'];
        $statuses = ['held','confirmed','paid'];
        $pmethods = ['cash','transfer','card'];
        $status = $this->faker->randomElement($statuses);
        $held = $status === 'held' ? now()->addMinutes(30) : null;
        return [
            'mass_instance_id' => null, // to be set in seeder/relationship
            'type' => $this->faker->randomElement($types),
            'public_text' => $this->faker->boolean(80) ? $this->faker->sentence(10) : null,
            'donor_name' => $this->faker->optional()->name(),
            'phone' => $this->faker->optional()->e164PhoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'amount' => $this->faker->optional()->randomFloat(2, 50, 400),
            'payment_method' => $this->faker->randomElement($pmethods),
            'status' => $status,
            'channel' => $this->faker->randomElement($channels),
            'hold_expires_at' => $held,
            'code' => Str::upper(Str::random(8)),
        ];
    }
}
