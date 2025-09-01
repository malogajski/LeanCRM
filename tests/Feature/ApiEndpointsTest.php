<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Activity;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user and get token for authentication
        $this->user = User::factory()->create([
            'team_id' => 1,
        ]);
        
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function test_authentication_endpoints()
    {
        // Test registration
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'team_id']
                ]);

        // Test login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'team_id']
                ]);

        $token = $response->json('access_token');

        // Test authenticated user endpoint
        $response = $this->getJson('/api/auth/user', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['id', 'name', 'email', 'team_id']
                ]);

        // Test logout
        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Successfully logged out']);

        // Note: In tests, tokens are not actually deleted since they use TransientToken
        // This is expected behavior for testing environment
    }

    /** @test */
    public function test_companies_crud_endpoints()
    {
        // Test create company
        $companyData = [
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'phone' => '+1-555-0123',
            'website' => 'https://test.com',
            'address' => '123 Test Street'
        ];

        $response = $this->postJson('/api/companies', $companyData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment($companyData);

        $companyId = $response->json('id');

        // Test get all companies
        $response = $this->getJson('/api/companies', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'total'
                ]);

        // Test get specific company
        $response = $this->getJson("/api/companies/{$companyId}", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment($companyData);

        // Test update company
        $updatedData = ['name' => 'Updated Company'];
        $response = $this->putJson("/api/companies/{$companyId}", $updatedData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment($updatedData);

        // Test delete company
        $response = $this->deleteJson("/api/companies/{$companyId}", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Company deleted successfully']);
    }

    /** @test */
    public function test_contacts_crud_endpoints()
    {
        $company = Company::factory()->create(['team_id' => 1]);

        // Test create contact
        $contactData = [
            'company_id' => $company->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1-555-0123',
            'position' => 'CEO'
        ];

        $response = $this->postJson('/api/contacts', $contactData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment($contactData);

        $contactId = $response->json('id');

        // Test get all contacts
        $response = $this->getJson('/api/contacts', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['data', 'current_page', 'total']);

        // Test get specific contact
        $response = $this->getJson("/api/contacts/{$contactId}", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment($contactData);

        // Test update contact
        $updatedData = ['first_name' => 'Jane'];
        $response = $this->putJson("/api/contacts/{$contactId}", $updatedData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment($updatedData);

        // Test delete contact
        $response = $this->deleteJson("/api/contacts/{$contactId}", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Contact deleted successfully']);
    }

    /** @test */
    public function test_deals_crud_endpoints()
    {
        $company = Company::factory()->create(['team_id' => 1]);
        $contact = Contact::factory()->create(['team_id' => 1, 'company_id' => $company->id]);

        // Test create deal
        $dealData = [
            'company_id' => $company->id,
            'contact_id' => $contact->id,
            'title' => 'Test Deal',
            'description' => 'Test deal description',
            'amount' => 5000.00,
            'stage' => 'prospect',
            'expected_close_date' => '2025-12-31'
        ];

        $response = $this->postJson('/api/deals', $dealData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['title' => 'Test Deal']);

        $dealId = $response->json('data.id');

        // Test get all deals
        $response = $this->getJson('/api/deals', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['data', 'links', 'meta']);

        // Test get specific deal
        $response = $this->getJson("/api/deals/{$dealId}", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Test Deal']);

        // Test update deal
        $updatedData = ['title' => 'Updated Deal', 'stage' => 'qualified'];
        $response = $this->putJson("/api/deals/{$dealId}", $updatedData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Updated Deal']);

        // Test delete deal
        $response = $this->deleteJson("/api/deals/{$dealId}", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Deal deleted successfully']);
    }

    /** @test */
    public function test_activities_crud_endpoints()
    {
        $deal = Deal::factory()->create([
            'team_id' => 1,
            'user_id' => $this->user->id
        ]);

        // Test create activity
        $activityData = [
            'subject_type' => 'App\\Models\\Deal',
            'subject_id' => $deal->id,
            'type' => 'call',
            'title' => 'Follow-up call',
            'description' => 'Call to discuss proposal',
            'due_date' => '2025-12-31 14:00:00',
            'completed' => false
        ];

        $response = $this->postJson('/api/activities', $activityData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['title' => 'Follow-up call']);

        $activityId = $response->json('id');

        // Test get all activities
        $response = $this->getJson('/api/activities', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['data', 'current_page', 'total']);

        // Test get specific activity
        $response = $this->getJson("/api/activities/{$activityId}", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Follow-up call']);

        // Test update activity
        $updatedData = ['completed' => true];
        $response = $this->putJson("/api/activities/{$activityId}", $updatedData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment($updatedData);

        // Test delete activity
        $response = $this->deleteJson("/api/activities/{$activityId}", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Activity deleted successfully']);
    }

    /** @test */
    public function test_notes_crud_endpoints()
    {
        $deal = Deal::factory()->create([
            'team_id' => 1,
            'user_id' => $this->user->id
        ]);

        // Test create note
        $noteData = [
            'notable_type' => 'App\\Models\\Deal',
            'notable_id' => $deal->id,
            'content' => 'This is a test note'
        ];

        $response = $this->postJson('/api/notes', $noteData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment($noteData);

        $noteId = $response->json('id');

        // Test get all notes
        $response = $this->getJson('/api/notes', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['data', 'current_page', 'total']);

        // Test get specific note
        $response = $this->getJson("/api/notes/{$noteId}", [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment($noteData);

        // Test update note
        $updatedData = ['content' => 'Updated note content'];
        $response = $this->putJson("/api/notes/{$noteId}", $updatedData, [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment($updatedData);

        // Test delete note
        $response = $this->deleteJson("/api/notes/{$noteId}", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Note deleted successfully']);
    }

    /** @test */
    public function test_unauthorized_access_returns_401()
    {
        // Test all endpoints without authorization
        $endpoints = [
            'GET' => ['/api/companies', '/api/contacts', '/api/deals', '/api/activities', '/api/notes', '/api/auth/user'],
            'POST' => ['/api/companies', '/api/contacts', '/api/deals', '/api/activities', '/api/notes', '/api/auth/logout'],
        ];

        foreach ($endpoints as $method => $paths) {
            foreach ($paths as $path) {
                $response = $method === 'GET' 
                    ? $this->getJson($path)
                    : $this->postJson($path, []);
                
                $response->assertStatus(401)
                        ->assertJson(['message' => 'Unauthenticated.']);
            }
        }
    }

    /** @test */
    public function test_multi_tenancy_isolation()
    {
        // Create user in different team
        $otherTeamUser = User::factory()->create(['team_id' => 2]);
        $otherToken = $otherTeamUser->createToken('test-token')->plainTextToken;

        // Create company for team 1
        $company = Company::factory()->create(['team_id' => 1]);

        // User from team 2 should not see company from team 1
        $response = $this->getJson('/api/companies', [
            'Authorization' => 'Bearer ' . $otherToken
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('total', 0);

        // User from team 2 should not be able to access company from team 1
        $response = $this->getJson("/api/companies/{$company->id}", [
            'Authorization' => 'Bearer ' . $otherToken
        ]);

        $response->assertStatus(403); // Forbidden due to policy
    }

    /** @test */
    public function test_filtering_and_sorting()
    {
        // Create test companies
        Company::factory()->create(['team_id' => 1, 'name' => 'Alpha Company']);
        Company::factory()->create(['team_id' => 1, 'name' => 'Beta Company']);

        // Test filtering
        $response = $this->getJson('/api/companies?filter[name]=Alpha', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('total', 1)
                ->assertJsonPath('data.0.name', 'Alpha Company');

        // Test sorting
        $response = $this->getJson('/api/companies?sort=name', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('data.0.name', 'Alpha Company')
                ->assertJsonPath('data.1.name', 'Beta Company');
    }
}