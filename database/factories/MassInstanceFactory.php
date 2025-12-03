<?php

namespace Database\Factories;

use App\Models\MassInstance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<\App\Models\MassInstance>
 */
class MassInstanceFactory extends Factory
{
    protected $model = MassInstance::class;

    public function definition(): array
    {
        $startsAt = Carbon::now()->startOfDay()->addDays($this->faker->numberBetween(0, 14))->addMinutes($this->faker->numberBetween(6*60, 20*60));
        $capacity = $this->faker->numberBetween(8, 30);
        $occupied = $this->faker->numberBetween(0, $capacity);
        return [
            'starts_at' => $startsAt,
            'capacity' => $capacity,
            'occupied' => $occupied,
            'status' => 'scheduled',
            'source' => 'template',
        ];
    }
}
