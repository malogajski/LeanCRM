<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stages = ['prospect', 'qualified', 'proposal', 'won', 'lost'];

        return [
            'team_id'             => 1,
            'company_id'          => Company::factory(),
            'contact_id'          => Contact::factory(),
            'user_id'             => User::factory(),
            'title'               => $this->faker->sentence(3),
            'description'         => $this->faker->optional()->paragraph(),
            'amount'              => $this->faker->randomFloat(2, 1000, 50000),
            'stage'               => $this->faker->randomElement($stages),
            'expected_close_date' => $this->faker->optional()->dateTimeBetween('now', '+6 months'),
        ];
    }
}
