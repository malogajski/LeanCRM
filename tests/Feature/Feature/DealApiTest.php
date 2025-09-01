<?php

namespace Tests\Feature\Feature;

use App\Events\DealStageChanged;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DealApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['team_id' => 1]);
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_deals(): void
    {
        Deal::factory()->count(3)->create(['team_id' => $this->user->team_id]);
        Deal::factory()->count(2)->create(['team_id' => 2]); // Different team

        $response = $this->getJson('/api/deals');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    public function test_can_create_deal(): void
    {
        $dealData = [
            'title' => 'New Website Project',
            'description' => 'Complete website redesign',
            'amount' => 5000.00,
            'stage' => 'prospect',
            'expected_close_date' => '2024-12-31',
        ];

        $response = $this->postJson('/api/deals', $dealData);

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'title' => 'New Website Project',
                    'amount' => '5000.00',
                    'stage' => 'prospect',
                ]);

        $this->assertDatabaseHas('deals', [
            'title' => 'New Website Project',
            'team_id' => $this->user->team_id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_show_deal(): void
    {
        $deal = Deal::factory()->create([
            'team_id' => $this->user->team_id,
            'title' => 'Test Deal',
        ]);

        $response = $this->getJson("/api/deals/{$deal->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $deal->id,
                    'title' => 'Test Deal',
                ]);
    }

    public function test_cannot_show_deal_from_different_team(): void
    {
        $deal = Deal::factory()->create(['team_id' => 999]);

        $response = $this->getJson("/api/deals/{$deal->id}");

        $response->assertStatus(403);
    }

    public function test_can_update_deal(): void
    {
        $deal = Deal::factory()->create([
            'team_id' => $this->user->team_id,
            'stage' => 'prospect',
        ]);

        $updateData = [
            'title' => 'Updated Deal Title',
            'amount' => 7500.00,
            'stage' => 'qualified',
        ];

        $response = $this->putJson("/api/deals/{$deal->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'title' => 'Updated Deal Title',
                    'amount' => '7500.00',
                    'stage' => 'qualified',
                ]);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'title' => 'Updated Deal Title',
            'stage' => 'qualified',
        ]);
    }

    public function test_stage_change_triggers_event(): void
    {
        Event::fake();

        $deal = Deal::factory()->create([
            'team_id' => $this->user->team_id,
            'stage' => 'prospect',
        ]);

        $this->putJson("/api/deals/{$deal->id}", ['stage' => 'won']);

        Event::assertDispatched(DealStageChanged::class, function ($event) use ($deal) {
            return $event->deal->id === $deal->id &&
                   $event->oldStage === 'prospect' &&
                   $event->newStage === 'won';
        });
    }

    public function test_can_delete_deal(): void
    {
        $deal = Deal::factory()->create([
            'team_id' => $this->user->team_id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/deals/{$deal->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Deal deleted successfully']);

        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    }

    public function test_validation_errors_on_create(): void
    {
        $response = $this->postJson('/api/deals', [
            'title' => '', // Required field
            'amount' => -100, // Must be positive
            'stage' => 'invalid_stage', // Must be valid enum value
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'amount', 'stage']);
    }

    public function test_can_filter_deals_by_stage(): void
    {
        Deal::factory()->create(['team_id' => $this->user->team_id, 'stage' => 'prospect']);
        Deal::factory()->create(['team_id' => $this->user->team_id, 'stage' => 'qualified']);
        Deal::factory()->create(['team_id' => $this->user->team_id, 'stage' => 'won']);

        $response = $this->getJson('/api/deals?filter[stage]=prospect');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.stage', 'prospect');
    }

    public function test_can_sort_deals_by_amount(): void
    {
        Deal::factory()->create(['team_id' => $this->user->team_id, 'amount' => 1000]);
        Deal::factory()->create(['team_id' => $this->user->team_id, 'amount' => 5000]);
        Deal::factory()->create(['team_id' => $this->user->team_id, 'amount' => 3000]);

        $response = $this->getJson('/api/deals?sort=amount');

        $response->assertStatus(200);
        $amounts = collect($response->json('data'))->pluck('amount');
        $this->assertEquals(['1000.00', '3000.00', '5000.00'], $amounts->toArray());
    }
}
