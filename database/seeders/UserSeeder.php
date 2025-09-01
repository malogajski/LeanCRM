<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin users for different teams
        $adminUser1 = User::create([
            'name'              => 'Admin User Team 1',
            'email'             => 'admin@team1.com',
            'password'          => Hash::make('password123'),
            'team_id'           => 1,
            'email_verified_at' => now(),
        ]);

        $adminUser2 = User::create([
            'name'              => 'Admin User Team 2',
            'email'             => 'admin@team2.com',
            'password'          => Hash::make('password123'),
            'team_id'           => 2,
            'email_verified_at' => now(),
        ]);

        // Create demo users for public testing
        $demoUser1 = User::create([
            'name'              => 'Demo Sales Rep',
            'email'             => 'demo@leancrm.com',
            'password'          => Hash::make('demo123'),
            'team_id'           => 1,
            'email_verified_at' => now(),
        ]);

        $demoUser2 = User::create([
            'name'              => 'Demo Manager',
            'email'             => 'manager@leancrm.com',
            'password'          => Hash::make('demo123'),
            'team_id'           => 1,
            'email_verified_at' => now(),
        ]);

        // Create additional users for team 1
        User::factory()->count(3)->create(['team_id' => 1]);

        // Create additional users for team 2
        User::factory()->count(3)->create(['team_id' => 2]);
    }
}
