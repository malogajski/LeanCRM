<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['call', 'email', 'meeting', 'task'];
        $subjectTypes = ['App\\Models\\Deal', 'App\\Models\\Company', 'App\\Models\\Contact'];

        return [
            'team_id'      => 1,
            'user_id'      => User::factory(),
            'subject_type' => $this->faker->randomElement($subjectTypes),
            'subject_id'   => Deal::factory(),
            'type'         => $this->faker->randomElement($types),
            'title'        => $this->faker->sentence(4),
            'description'  => $this->faker->optional()->paragraph(),
            'due_date'     => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'completed'    => $this->faker->boolean(30), // 30% chance of being completed
        ];
    }
}
