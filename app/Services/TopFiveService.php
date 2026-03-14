<?php

namespace App\Services;

use App\Models\TopFiveScore;
use App\Models\TopFiveCandidates;

class TopFiveService
{
    protected array $categories = [
        'accumulative',
        'top_five_beauty_of_face',
        'top_five_beauty_of_body',
        'top_five_posture_and_carriage_confidence',
        'top_five_final_q_and_a',
    ];

    protected array $judgeOrder = [
        'judge_1',
        'judge_2',
        'judge_3',
        'judge_4',
        'judge_5',
    ];

    /**
     * Get results per category
     */
    public function getResultsPerCategory(string $category): array
    {
        $candidatesList = TopFiveCandidates::with('candidate')
            ->get()
            ->map(fn($item) => [
                'candidate'   => $item->candidate,
                'top_five_id' => $item->id,
                'accumulative' => $item->accumulative ?? 0,
            ]);

        $scores = TopFiveScore::with('judge')->get();

        return [
            'candidates' => $this->processCandidates(
                $candidatesList,
                $scores,
                $category,
                $this->judgeOrder
            ),
            'judgeOrder' => $this->judgeOrder,
        ];
    }

    protected function processCandidates($candidatesList, $scores, string $category, array $judgeOrder): array
    {
        $processed = [];

        foreach ($candidatesList as $index => $item) {
            $candidate = $item['candidate'];
            $topFiveId = $item['top_five_id'];
            $accumulative = $item['accumulative'];

            $candidateScores = array_fill_keys($judgeOrder, 0);

            if ($category === 'accumulative') {
                $candidateScores['judge_1'] = round($accumulative, 2);

                $processed[] = [
                    'candidate'        => $candidate,
                    'scores'           => $candidateScores,
                    'total'            => round($accumulative, 2),
                    'rank'             => 0,
                    'candidate_number' => $index + 1,
                ];

                continue;
            }

            foreach ($scores->where('top_five_id', $topFiveId) as $score) {
                if ($score->judge && in_array($score->judge->name, $judgeOrder)) {
                    $candidateScores[$score->judge->name] = $score->{$category} ?? 0;
                }
            }

            // Calculate average instead of sum
            $judgeScoresArray = array_values($candidateScores);
            $average = count($judgeScoresArray) > 0 ? array_sum($judgeScoresArray) / count($judgeScoresArray) : 0;

            $processed[] = [
                'candidate'        => $candidate,
                'scores'           => $candidateScores,
                'total'            => round($average, 2),
                'rank'             => 0,
                'candidate_number' => $index + 1,
            ];
        }

        return $this->assignRanking($processed);
    }

    /**
     * Get total combined results
     * Formula:
     * 1. Cumulative (35 max) = Average of 4 performances scaled to 35
     * 2. Beauty of Face (15 max) = Direct score from judges (already out of 15)
     * 3. Beauty of Body (15 max) = Direct score from judges (already out of 15)
     * 4. Posture & Carriage (10 max) = Direct score from judges (already out of 10)
     * 5. Q&A (25 max) = Direct score from judges (already out of 25)
     * Total = 100 points
     */
    public function getTotalResults(): array
    {
        $candidates = TopFiveCandidates::with('candidate')->get();
        $scores = TopFiveScore::with('judge')->get();

        $processed = [];

        foreach ($candidates as $index => $item) {
            $beautyOfFaceSum = 0;
            $beautyOfBodySum = 0;
            $postureAndCarriageSum = 0;
            $finalQASum = 0;

            foreach ($scores->where('top_five_id', $item->id) as $score) {
                $beautyOfFaceSum += $score->top_five_beauty_of_face ?? 0;
                $beautyOfBodySum += $score->top_five_beauty_of_body ?? 0;
                $postureAndCarriageSum += $score->top_five_posture_and_carriage_confidence ?? 0;
                $finalQASum += $score->top_five_final_q_and_a ?? 0;
            }

            $judgeCount = $scores->where('top_five_id', $item->id)->count();
            $judgeCount = $judgeCount > 0 ? $judgeCount : 1; // Avoid division by zero

            // Cumulative is already the average of 4 performances, scale to 35 max
            $accumulative = $item->accumulative ?? 0;
            $cumulativeScaled = ($accumulative / 100) * 35;

            // Average the judges' scores to get the final score for each category
            // Beauty of Face: max 15 points
            $beautyOfFaceTotal = round($beautyOfFaceSum / $judgeCount, 2);
            
            // Beauty of Body: max 15 points
            $beautyOfBodyTotal = round($beautyOfBodySum / $judgeCount, 2);
            
            // Posture & Carriage: max 10 points
            $postureAndCarriageTotal = round($postureAndCarriageSum / $judgeCount, 2);
            
            // Q&A: max 25 points
            $finalQATotal = round($finalQASum / $judgeCount, 2);

            $totalScore = $cumulativeScaled + $beautyOfFaceTotal + $beautyOfBodyTotal + 
                         $postureAndCarriageTotal + $finalQATotal;

            $processed[] = [
                'candidate' => $item->candidate,
                'scores' => [
                    'accumulative' => round($cumulativeScaled, 2),
                    'top_five_beauty_of_face' => round($beautyOfFaceTotal, 2),
                    'top_five_beauty_of_body' => round($beautyOfBodyTotal, 2),
                    'top_five_posture_and_carriage_confidence' => round($postureAndCarriageTotal, 2),
                    'top_five_final_q_and_a' => round($finalQATotal, 2),
                ],
                'total' => round($totalScore, 2),
                'rank' => 0,
                'candidate_number' => $index + 1,
            ];
        }

        return [
            'candidates' => $this->assignRanking($processed),
            'judgeOrder' => [],
        ];
    }

    protected function processTotalPerCategory($candidatesList, $scores): array
    {
        $processed = [];

        foreach ($candidatesList as $index => $item) {
            $candidate = $item['candidate'];
            $topFiveId = $item['top_five_id'];
            $accumulative = $item['accumulative'] ?? 0;

            $categoryTotals = array_fill_keys($this->categories, 0);
            $categoryTotals['accumulative'] = $accumulative;

            foreach ($scores->where('top_five_id', $topFiveId) as $score) {
                foreach ($this->categories as $cat) {
                    if ($cat === 'accumulative') {
                        continue;
                    }

                    $categoryTotals[$cat] += $score->{$cat} ?? 0;
                }
            }

            $processed[] = [
                'candidate'        => $candidate,
                'scores'           => $categoryTotals,
                'total'            => round(array_sum($categoryTotals), 2),
                'rank'             => 0,
                'candidate_number' => $index + 1,
            ];
        }

        return $this->assignRanking($processed);
    }

    private function assignRanking(array $candidates): array
    {
        usort($candidates, fn($a, $b) => $b['total'] <=> $a['total']);

        $rank = 1;
        $lastTotal = null;

        foreach ($candidates as $index => &$c) {
            if ($lastTotal !== null && $c['total'] === $lastTotal) {
                $c['rank'] = $rank;
            } else {
                $rank = $index + 1;
                $c['rank'] = $rank;
                $lastTotal = $c['total'];
            }
        }

        return $candidates;
    }
}
