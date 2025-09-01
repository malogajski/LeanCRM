<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deals = Deal::all();
        $users = User::all();

        $demoNotes = [
            'Client expressed strong interest in our premium package. They have budget approved for Q1.',
            'Meeting went well. Decision maker will be available next week for final discussions.',
            'Competitor pricing is 20% higher. We have good positioning for this deal.',
            'Technical requirements discussion scheduled. Need to prepare architecture diagrams.',
            'Contract negotiations in progress. Legal team reviewing terms.',
            'Client requested additional features. Preparing revised proposal.',
            'Follow-up call scheduled for Friday. Key stakeholder will join.',
            'Demo was successful. Moving to proposal stage.',
            'Budget constraints discussed. Exploring phased implementation approach.',
            'References provided. Client will contact them this week.',
        ];

        // Create notes for deals
        foreach ($deals as $deal) {
            $teamUsers = $users->where('team_id', $deal->team_id);
            $noteCount = rand(1, 3);

            for ($i = 0; $i < $noteCount; $i++) {
                Note::create([
                    'notable_type' => 'App\\Models\\Deal',
                    'notable_id'   => $deal->id,
                    'user_id'      => $teamUsers->random()->id,
                    'team_id'      => $deal->team_id,
                    'content'      => $demoNotes[array_rand($demoNotes)],
                ]);
            }
        }
    }
}
