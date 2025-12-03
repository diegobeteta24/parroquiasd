<?php

namespace Database\Factories;

use App\Models\MassTimeTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\MassTimeTemplate>
 */
class MassTimeTemplateFactory extends Factory
{
    protected $model = MassTimeTemplate::class;

    public function definition(): array
    {
        $dow = $this->faker->numberBetween(1, 7); // ISO: 1=Mon ... 7=Sun
        $times = ['06:30', '07:00', '08:00', '12:00', '16:30', '18:30', '20:00'];
        return [
            'dow' => $dow,
            'time' => $this->faker->randomElement($times),
            'capacity' => $this->faker->numberBetween(8, 30),
            'active' => true,
        ];
    }
}
