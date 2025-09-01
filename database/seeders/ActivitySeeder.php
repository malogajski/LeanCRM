<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deals = Deal::all();
        $users = User::all();

        // Activity types for variety
        $activityTypes = ['call', 'email', 'meeting', 'task', 'follow_up'];

        $demoActivities = [
            [
                'type'        => 'call',
                'title'       => 'Initial Discovery Call',
                'description' => 'Discuss client requirements and project scope',
                'due_date'    => now()->addDays(1),
                'completed'   => false,
            ],
            [
                'type'        => 'meeting',
                'title'       => 'Proposal Presentation',
                'description' => 'Present detailed proposal and pricing to stakeholders',
                'due_date'    => now()->addDays(5),
                'completed'   => false,
            ],
            [
                'type'        => 'email',
                'title'       => 'Send Contract Documents',
                'description' => 'Email final contract and terms for review',
                'due_date'    => now()->subDays(2),
                'completed'   => true,
            ],
            [
                'type'        => 'follow_up',
                'title'       => 'Follow up on Proposal',
                'description' => 'Check in on proposal status and answer any questions',
                'due_date'    => now()->addDays(3),
                'completed'   => false,
            ],
            [
                'type'        => 'task',
                'title'       => 'Prepare Demo Environment',
                'description' => 'Set up demo environment for client presentation',
                'due_date'    => now()->addDays(7),
                'completed'   => false,
            ],
        ];

        // Create demo activities for first 5 deals
        $demoDeals = $deals->take(5);
        foreach ($demoDeals as $index => $deal) {
            if (isset($demoActivities[$index])) {
                $activityData = $demoActivities[$index];
                $activityData['subject_type'] = 'App\\Models\\Deal';
                $activityData['subject_id'] = $deal->id;
                $activityData['user_id'] = $deal->user_id;
                $activityData['team_id'] = $deal->team_id;

                Activity::create($activityData);
            }
        }

        // Create additional random activities for all deals
        foreach ($deals as $deal) {
            $teamUsers = $users->where('team_id', $deal->team_id);

            Activity::factory()
                ->count(rand(1, 4))
                ->create([
                    'subject_type' => 'App\\Models\\Deal',
                    'subject_id'   => $deal->id,
                    'user_id'      => $teamUsers->random()->id,
                    'team_id'      => $deal->team_id,
                    'type'         => $activityTypes[array_rand($activityTypes)],
                ]);
        }
    }
}
