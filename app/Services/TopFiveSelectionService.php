<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\TopFiveSelectionScore;

class TopFiveSelectionService
{
    protected array $categories = [
        'creative_attire',
        'casual_wear',
        'swim_wear',
        'filipiniana_attire',
    ];

    protected array $judgeOrder = [
        'judge_1',
        'judge_2',
        'judge_3',
        'judge_4',
        'judge_5',
    ];

    protected array $weights = [
        'creative_attire' => 30,
        'casual_wear' => 20,
        'swim_wear' => 20,
        'filipiniana_attire' => 30,
    ];

    protected array $categoryLabels = [
        'creative_attire' => 'Bangkarera Creative Attire',
        'casual_wear' => 'Casual Wear',
        'swim_wear' => 'Swim Wear',
        'filipiniana_attire' => 'Evening Long Gown',
    ];

    private function computeCategoryFinalScore(string $category, $mean): float
    {
        $mean = (float) $mean;

        if (isset($this->weights[$category])) {
            return round(($mean / 100) * $this->weights[$category], 2);
        }

        return round($mean, 2);
    }

    /**
     * Compute cumulative performance score
     * Based on Top 5 Selection total (out of 100)
     * Creative Attire (30) + Casual Wear (20) + Swim Wear (20) + Evening Long Gown (30) = 100
     * 
     * The accumulative is stored as the full total (out of 100)
     * It will be scaled to 35 points in the Final Ranking calculation
     * 
     * Returns array with both total and accumulative (same value, out of 100)
     */
    private function computePerformanceCumulative(array $means): array
    {
        $total =
            ($means['creative_attire'] ?? 0) +
            ($means['casual_wear'] ?? 0) +
            ($means['swim_wear'] ?? 0) +
            ($means['filipiniana_attire'] ?? 0);

        // Accumulative is the same as total (out of 100)
        $accumulative = round($total, 2);

        return [
            'total' => round($total, 2),
            'accumulative' => $accumulative,
        ];
    }

    public function getResultsPerCategory(string $category): array
    {
        $candidates = Candidate::all();
        $scores = TopFiveSelectionScore::with('judge')->get();

        $processed = [];

        foreach ($candidates as $candidate) {
            $judgeScores = array_fill_keys($this->judgeOrder, 0);

            foreach ($scores->where('candidate_id', $candidate->id) as $score) {
                if ($score->judge && in_array($score->judge->name, $this->judgeOrder)) {
                    $judgeScores[$score->judge->name] = $score->{$category} ?? 0;
                }
            }

            $rawScores = array_values($judgeScores);
            $mean = count($rawScores) > 0 ? array_sum($rawScores) / count($rawScores) : 0;

            $processed[] = [
                'candidate' => $candidate,
                'scores' => $judgeScores,
                'mean' => round($mean, 2),
                'converted' => $this->computeCategoryFinalScore($category, $mean),
                'total' => round(array_sum($judgeScores), 2),
                'rank' => 0,
            ];
        }

        return [
            'candidates' => $this->assignRankingByCategory($processed, $category),
            'judgeOrder' => $this->judgeOrder,
            'categoryLabel' => $this->categoryLabels[$category] ?? $category,
        ];
    }

    public function getTopFiveSelectionResults(): array
    {
        $candidates = Candidate::all();
        $scores = TopFiveSelectionScore::with('judge')->get();

        $processed = [];

        foreach ($candidates as $candidate) {
            $categoryTotals = array_fill_keys($this->categories, 0);

            foreach ($this->categories as $cat) {
                $candidateCategoryScores = $scores
                    ->where('candidate_id', $candidate->id)
                    ->pluck($cat)
                    ->filter(fn($score) => $score !== null);

                $mean = $candidateCategoryScores->count() > 0
                    ? $candidateCategoryScores->avg()
                    : 0;

                $categoryTotals[$cat] = $this->computeCategoryFinalScore($cat, $mean);
            }

            $cumulativeData = $this->computePerformanceCumulative($categoryTotals);

            $processed[] = [
                'candidate' => $candidate,
                'scores' => $categoryTotals,
                'performance_cumulative' => $cumulativeData['accumulative'],
                'selection_total' => $cumulativeData['total'],
                'total' => round(array_sum($categoryTotals), 2),
                'rank' => 0,
            ];
        }

        return [
            'candidates' => $this->assignRanking($processed),
            'categories' => $this->categories,
            'categoryLabels' => $this->categoryLabels,
        ];
    }

    private function assignRanking(array $candidates): array
    {
        usort($candidates, fn($a, $b) => $b['total'] <=> $a['total']);

        $rank = 1;
        $lastTotal = null;

        foreach ($candidates as $index => &$candidate) {
            if ($lastTotal !== null && $candidate['total'] === $lastTotal) {
                $candidate['rank'] = $rank;
            } else {
                $rank = $index + 1;
                $candidate['rank'] = $rank;
                $lastTotal = $candidate['total'];
            }
        }

        return $candidates;
    }

    private function assignRankingByCategory(array $candidates, string $category): array
    {
        usort($candidates, function ($a, $b) {
            return $b['converted'] <=> $a['converted'];
        });

        $rank = 1;
        $lastTotal = null;

        foreach ($candidates as $index => &$candidate) {
            if ($lastTotal !== null && $candidate['converted'] === $lastTotal) {
                $candidate['rank'] = $rank;
            } else {
                $rank = $index + 1;
                $candidate['rank'] = $rank;
                $lastTotal = $candidate['converted'];
            }
        }

        return $candidates;
    }

    public function getTopFiveAccumulative(?array $candidateIds = null): array
    {
        $results = $this->getTopFiveSelectionResults();
        $candidates = collect($results['candidates']);

        if ($candidateIds) {
            $candidates = $candidates->filter(function ($item) use ($candidateIds) {
                return in_array($item['candidate']->id, $candidateIds);
            });
        }

        return $candidates
            ->sortByDesc('performance_cumulative')
            ->take(5)
            ->map(function ($item) {
                return [
                    'candidate' => $item['candidate'],
                    'total' => $item['total'],
                    'selection_total' => $item['selection_total'],
                    'accumulative' => $item['performance_cumulative'],
                ];
            })
            ->values()
            ->toArray();
    }
}
