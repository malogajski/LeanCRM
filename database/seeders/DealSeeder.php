<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $companies = Company::all();
        $contacts = Contact::all();

        // Create specific demo deals
        $demoDeals = [
            [
                'title'               => 'Website Redesign Project',
                'description'         => 'Complete website redesign with modern UI/UX and mobile optimization',
                'amount'              => 15000.00,
                'stage'               => 'qualified',
                'expected_close_date' => now()->addDays(30),
                'team_id'             => 1,
            ],
            [
                'title'               => 'Digital Marketing Campaign',
                'description'         => '6-month comprehensive digital marketing strategy and execution',
                'amount'              => 25000.00,
                'stage'               => 'proposal',
                'expected_close_date' => now()->addDays(15),
                'team_id'             => 1,
            ],
            [
                'title'               => 'Cloud Migration Services',
                'description'         => 'Migration of legacy systems to modern cloud infrastructure',
                'amount'              => 50000.00,
                'stage'               => 'prospect',
                'expected_close_date' => now()->addDays(45),
                'team_id'             => 1,
            ],
            [
                'title'               => 'Enterprise Software License',
                'description'         => 'Annual license for enterprise management software',
                'amount'              => 35000.00,
                'stage'               => 'won',
                'expected_close_date' => now()->subDays(5),
                'team_id'             => 1,
            ],
            [
                'title'               => 'Mobile App Development',
                'description'         => 'Custom mobile application for iOS and Android platforms',
                'amount'              => 75000.00,
                'stage'               => 'lost',
                'expected_close_date' => now()->subDays(10),
                'team_id'             => 1,
            ],
        ];

        // Create demo deals with proper relationships
        $team1Companies = $companies->where('team_id', 1);
        $team1Users = $users->where('team_id', 1);

        foreach ($demoDeals as $index => $dealData) {
            $company = $team1Companies->skip($index)->first();
            $contact = $contacts->where('company_id', $company->id)->first();
            $user = $team1Users->random();

            $dealData['company_id'] = $company->id;
            $dealData['contact_id'] = $contact ? $contact->id : null;
            $dealData['user_id'] = $user->id;

            Deal::create($dealData);
        }

        // Create additional random deals
        foreach ($companies as $company) {
            $companyContacts = $contacts->where('company_id', $company->id);
            $teamUsers = $users->where('team_id', $company->team_id);

            Deal::factory()
                ->count(rand(1, 3))
                ->create([
                    'company_id' => $company->id,
                    'contact_id' => $companyContacts->random()?->id,
                    'user_id'    => $teamUsers->random()->id,
                    'team_id'    => $company->team_id,
                ]);
        }
    }
}
