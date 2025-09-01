<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific demo companies for team 1
        $companies = [
            [
                'name'    => 'TechCorp Solutions',
                'email'   => 'contact@techcorp.com',
                'phone'   => '+1-555-0101',
                'website' => 'https://techcorp.com',
                'address' => '123 Tech Street, Silicon Valley, CA 94000',
                'team_id' => 1,
            ],
            [
                'name'    => 'Global Marketing Inc',
                'email'   => 'hello@globalmarketing.com',
                'phone'   => '+1-555-0202',
                'website' => 'https://globalmarketing.com',
                'address' => '456 Marketing Ave, New York, NY 10001',
                'team_id' => 1,
            ],
            [
                'name'    => 'Startup Ventures LLC',
                'email'   => 'info@startupventures.com',
                'phone'   => '+1-555-0303',
                'website' => 'https://startupventures.com',
                'address' => '789 Innovation Blvd, Austin, TX 73301',
                'team_id' => 1,
            ],
            [
                'name'    => 'Enterprise Systems Co',
                'email'   => 'sales@enterprise-systems.com',
                'phone'   => '+1-555-0404',
                'website' => 'https://enterprise-systems.com',
                'address' => '321 Business Park, Chicago, IL 60601',
                'team_id' => 1,
            ],
            [
                'name'    => 'Digital Agency Pro',
                'email'   => 'team@digitalagency.pro',
                'phone'   => '+1-555-0505',
                'website' => 'https://digitalagency.pro',
                'address' => '654 Creative District, Los Angeles, CA 90210',
                'team_id' => 1,
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }

        // Create additional random companies for both teams
        Company::factory()->count(15)->create(['team_id' => 1]);
        Company::factory()->count(10)->create(['team_id' => 2]);
    }
}
