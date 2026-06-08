<?php

namespace Database\Factories;

use App\Models\Mom;
use App\Models\MomType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mom>
 */
class MomFactory extends Factory
{
    protected $model = Mom::class;

    public function definition(): array
    {
        return [
            'mom_type_id'  => MomType::factory(),
            'user_id'      => User::factory(),
            'mom_number'   => 'MOM-' . $this->faker->unique()->numerify('####'),
            'agenda'       => $this->faker->sentence(),
            'remarks'      => $this->faker->sentence(),
            'meeting_date' => $this->faker->date(),
            'status'       => 'submitted',
        ];
    }
}
