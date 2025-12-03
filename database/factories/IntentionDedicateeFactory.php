<?php

namespace Database\Factories;

use App\Models\IntentionDedicatee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\IntentionDedicatee>
 */
class IntentionDedicateeFactory extends Factory
{
    protected $model = IntentionDedicatee::class;

    public function definition(): array
    {
        $relations = ['espos@','madre','padre','hij@','familia','amig@'];
        return [
            'intention_id' => null,
            'name' => $this->faker->name(),
            'relation' => $this->faker->optional()->randomElement($relations),
        ];
    }
}
