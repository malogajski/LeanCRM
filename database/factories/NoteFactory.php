<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notableTypes = ['App\\Models\\Deal', 'App\\Models\\Company', 'App\\Models\\Contact'];

        return [
            'team_id'      => 1,
            'user_id'      => User::factory(),
            'notable_type' => $this->faker->randomElement($notableTypes),
            'notable_id'   => Deal::factory(),
            'content'      => $this->faker->paragraph(),
        ];
    }
}
