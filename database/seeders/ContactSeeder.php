<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        // Create specific contacts for demo companies
        $demoContacts = [
            [
                'first_name' => 'John',
                'last_name'  => 'Smith',
                'email'      => 'john.smith@techcorp.com',
                'phone'      => '+1-555-0111',
                'position'   => 'CEO',
                'team_id'    => 1,
            ],
            [
                'first_name' => 'Sarah',
                'last_name'  => 'Johnson',
                'email'      => 'sarah.johnson@globalmarketing.com',
                'phone'      => '+1-555-0222',
                'position'   => 'Marketing Director',
                'team_id'    => 1,
            ],
            [
                'first_name' => 'Michael',
                'last_name'  => 'Brown',
                'email'      => 'michael.brown@startupventures.com',
                'phone'      => '+1-555-0333',
                'position'   => 'Founder',
                'team_id'    => 1,
            ],
            [
                'first_name' => 'Emily',
                'last_name'  => 'Davis',
                'email'      => 'emily.davis@enterprise-systems.com',
                'phone'      => '+1-555-0444',
                'position'   => 'CTO',
                'team_id'    => 1,
            ],
            [
                'first_name' => 'David',
                'last_name'  => 'Wilson',
                'email'      => 'david.wilson@digitalagency.pro',
                'phone'      => '+1-555-0555',
                'position'   => 'Creative Director',
                'team_id'    => 1,
            ],
        ];

        // Assign demo contacts to first 5 companies
        $demoCompanies = $companies->where('team_id', 1)->take(5);
        foreach ($demoContacts as $index => $contactData) {
            $company = $demoCompanies->skip($index)->first();
            if ($company) {
                $contactData['company_id'] = $company->id;
                Contact::create($contactData);
            }
        }

        // Create additional contacts for all companies
        foreach ($companies as $company) {
            Contact::factory()
                ->count(rand(1, 4))
                ->create([
                    'company_id' => $company->id,
                    'team_id'    => $company->team_id,
                ]);
        }
    }
}
