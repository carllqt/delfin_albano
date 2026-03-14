<?php

namespace App\Http\Controllers\ResultController;

use App\Http\Controllers\Controller;
use App\Services\TopFiveService;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TopFiveCandidateResultController extends Controller
{
    protected $service;

    public function __construct(TopFiveService $service)
    {
        $this->service = $service;
    }

    /**
     * BEAUTY OF FACE RESULTS
     */
    public function beautyOfFaceResults()
    {
        $results = $this->service->getResultsPerCategory('top_five_beauty_of_face');

        return Inertia::render('Admin/TopFiveCategories/BeautyOfFaceResult', [
            'candidates'   => $results['candidates'],
            'judgeOrder'   => $results['judgeOrder'],
            'categoryName' => 'Beauty of Face',
        ]);
    }

    /**
     * BEAUTY OF BODY RESULTS
     */
    public function beautyOfBodyResults()
    {
        $results = $this->service->getResultsPerCategory('top_five_beauty_of_body');

        return Inertia::render('Admin/TopFiveCategories/BeautyOfBodyResult', [
            'candidates'   => $results['candidates'],
            'judgeOrder'   => $results['judgeOrder'],
            'categoryName' => 'Beauty of Body',
        ]);
    }

    /**
     * POSTURE AND CARRIAGE RESULTS
     */
    public function postureAndCarriageConfidenceResults()
    {
        $results = $this->service->getResultsPerCategory('top_five_posture_and_carriage_confidence');

        return Inertia::render('Admin/TopFiveCategories/PostureAndCarriageResult', [
            'candidates'   => $results['candidates'],
            'judgeOrder'   => $results['judgeOrder'],
            'categoryName' => 'Posture and Carriage / Confidence',
        ]);
    }

    /**
     * FINAL Q & A RESULTS
     */
    public function finalQAResults()
    {
        $results = $this->service->getResultsPerCategory('top_five_final_q_and_a');

        return Inertia::render('Admin/TopFiveCategories/FinalQAResult', [
            'candidates'   => $results['candidates'],
            'judgeOrder'   => $results['judgeOrder'],
            'categoryName' => 'Final Q & A',
        ]);
    }

    /**
     * TOTAL COMBINED RESULTS
     */
    public function totalResults()
    {
        $results = $this->service->getTotalResults();

        return Inertia::render('Admin/TopFiveCategories/TotalResults', [
            'candidates'   => $results['candidates'],
            'judgeOrder'   => $results['judgeOrder'],
            'categoryName' => 'Total Combined Scores',
        ]);
    }

    // PDF Export Methods
    private function exportCategoryPdf(string $category, string $name, int $maxPoints = 15)
    {
        $results = $this->service->getResultsPerCategory($category);
        
        $pdf = Pdf::loadView('pdf.result-table', [
            'candidates' => $results['candidates'],
            'judgeOrder' => $results['judgeOrder'],
            'categoryName' => $name,
            'maxPoints' => $maxPoints,
            'isAverageScore' => true,
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($name . ' Results.pdf');
    }

    public function exportBeautyOfFacePdf()
    {
        return $this->exportCategoryPdf('top_five_beauty_of_face', 'Beauty of Face', 15);
    }

    public function exportBeautyOfBodyPdf()
    {
        return $this->exportCategoryPdf('top_five_beauty_of_body', 'Beauty of Body', 15);
    }

    public function exportPostureAndCarriagePdf()
    {
        return $this->exportCategoryPdf('top_five_posture_and_carriage_confidence', 'Posture and Carriage Confidence', 10);
    }

    public function exportFinalQAPdf()
    {
        return $this->exportCategoryPdf('top_five_final_q_and_a', 'Final Q & A', 25);
    }

    public function exportTotalResultsPdf()
    {
        $results = $this->service->getTotalResults();
        
        $pdf = Pdf::loadView('pdf.total-results', [
            'candidates' => $results['candidates'],
            'categories' => ['accumulative', 'top_five_beauty_of_face', 'top_five_beauty_of_body', 'top_five_posture_and_carriage_confidence', 'top_five_final_q_and_a'],
            'categoryLabels' => [
                'accumulative' => 'Cumulative (35 pts)',
                'top_five_beauty_of_face' => 'Beauty of Face (15 pts)',
                'top_five_beauty_of_body' => 'Beauty of Body (15 pts)',
                'top_five_posture_and_carriage_confidence' => 'Posture & Carriage (10 pts)',
                'top_five_final_q_and_a' => 'Q&A (25 pts)',
            ],
            'categoryName' => 'Total Combined Scores',
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Total Combined Results.pdf');
    }
}
