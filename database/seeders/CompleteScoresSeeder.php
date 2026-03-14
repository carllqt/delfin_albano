<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Candidate;
use App\Models\TopFiveSelectionScore;
use App\Models\TopFiveCandidates;
use App\Models\TopFiveScore;

class CompleteScoresSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Clearing existing scores...');
        
        // Clear all existing scores
        TopFiveScore::query()->delete();
        TopFiveCandidates::query()->delete();
        TopFiveSelectionScore::query()->delete();
        
        $this->command->info('Existing scores cleared!');
        
        // Get all judges
        $judges = User::where('role', 'judge')->get();
        
        if ($judges->count() !== 5) {
            $this->command->error('Expected 5 judges, found ' . $judges->count());
            return;
        }

        // Get all candidates
        $candidates = Candidate::all();
        
        if ($candidates->count() !== 15) {
            $this->command->error('Expected 15 candidates, found ' . $candidates->count());
            return;
        }

        $this->command->info('Seeding Top 5 Selection Scores...');
        
        // Seed Top 5 Selection scores for all 15 candidates
        foreach ($candidates as $candidate) {
            foreach ($judges as $judge) {
                TopFiveSelectionScore::create([
                    'candidate_id' => $candidate->id,
                    'judge_id' => $judge->id,
                    'creative_attire' => rand(70, 100),
                    'casual_wear' => rand(70, 100),
                    'swim_wear' => rand(70, 100),
                    'filipiniana_attire' => rand(70, 100),
                ]);
            }
        }

        $this->command->info('Top 5 Selection scores seeded successfully!');
        $this->command->info('Calculating Top 5 candidates...');

        // Calculate and set Top 5 candidates
        $service = app(\App\Services\TopFiveSelectionService::class);
        $topFive = $service->getTopFiveAccumulative();

        // Clear existing Top 5 candidates
        TopFiveCandidates::query()->delete();

        // Create Top 5 candidates with accumulative scores
        foreach ($topFive as $data) {
            TopFiveCandidates::create([
                'candidate_id' => $data['candidate']->id,
                'accumulative' => $data['accumulative'],
            ]);
        }

        $this->command->info('Top 5 candidates set successfully!');
        $this->command->info('Seeding Top 5 Finalist Scores...');

        // Get the Top 5 candidates
        $topFiveCandidates = TopFiveCandidates::all();

        // Seed Top 5 Finalist scores
        foreach ($topFiveCandidates as $topFiveCandidate) {
            foreach ($judges as $judge) {
                TopFiveScore::create([
                    'top_five_id' => $topFiveCandidate->id,
                    'judge_id' => $judge->id,
                    'top_five_beauty_of_face' => rand(10, 15),      // Max 15 points
                    'top_five_beauty_of_body' => rand(10, 15),      // Max 15 points
                    'top_five_posture_and_carriage_confidence' => rand(7, 10),  // Max 10 points
                    'top_five_final_q_and_a' => rand(18, 25),       // Max 25 points
                ]);
            }
        }

        $this->command->info('Top 5 Finalist scores seeded successfully!');
        $this->command->info('');
        $this->command->info('=== SEEDING SUMMARY ===');
        $this->command->info('Top 5 Selection: 15 candidates × 5 judges = 75 score records');
        $this->command->info('Top 5 Finalists: 5 candidates × 5 judges = 25 score records');
        $this->command->info('');
        $this->command->info('Top 5 Candidates:');
        
        foreach ($topFiveCandidates as $index => $topFiveCandidate) {
            $candidate = $topFiveCandidate->candidate;
            $selectionTotal = $topFive[$index]['selection_total'] ?? 0;
            $this->command->info(
                ($index + 1) . '. #' . $candidate->candidate_number . ' ' . 
                $candidate->first_name . ' ' . $candidate->last_name . 
                ' (Top 5 Selection: ' . number_format($selectionTotal, 2) . '/100' .
                ' = Accumulative: ' . number_format($topFiveCandidate->accumulative, 2) . '/100)'
            );
        }
    }
}
