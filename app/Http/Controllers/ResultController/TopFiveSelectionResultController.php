<?php

namespace App\Http\Controllers\ResultController;

use App\Http\Controllers\Controller;
use App\Services\TopFiveSelectionService;
use App\Models\TopFiveCandidates;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TopFiveSelectionResultController extends Controller
{
    protected $service;

    public function __construct(TopFiveSelectionService $service)
    {
        $this->service = $service;
    }

    private function renderCategory(string $category, string $name, string $view)
    {
        $results = $this->service->getResultsPerCategory($category);

        return Inertia::render($view, [
            'candidates'   => $results['candidates'],
            'judgeOrder'   => $results['judgeOrder'],
            'categoryName' => $name,
        ]);
    }

    public function creativeAttireResults()
    {
        return $this->renderCategory(
            'creative_attire',
            'Bangkarera Creative Attire',
            'Admin/CreativeAttireResult'
        );
    }

    public function casualWearResults()
    {
        return $this->renderCategory(
            'casual_wear',
            'Casual Wear',
            'Admin/CasualWearResult'
        );
    }

    public function swimWearResults()
    {
        return $this->renderCategory(
            'swim_wear',
            'Swim Wear',
            'Admin/SwimWearResult'
        );
    }

    public function filipinianaAttireResults()
    {
        return $this->renderCategory(
            'filipiniana_attire',
            'Evening Long Gown',
            'Admin/FilipinianaAttireResult'
        );
    }

    public function beautyOfFaceAuraResults()
    {
        return $this->renderCategory(
            'beauty_of_face_aura',
            'Beauty of Face / Aura',
            'Admin/BeautyOfFaceAuraResult'
        );
    }

    public function beautyOfBodyResults()
    {
        return $this->renderCategory(
            'beauty_of_body',
            'Beauty of Body',
            'Admin/BeautyOfBodyResult'
        );
    }

    public function postureAndCarriageConfidenceResults()
    {
        return $this->renderCategory(
            'posture_and_carriage_confidence',
            'Posture and Carriage / Confidence',
            'Admin/PostureAndCarriageConfidenceResult'
        );
    }

    public function topFiveSelectionResults()
    {
        $results = $this->service->getTopFiveSelectionResults();

        return Inertia::render('Admin/TopFiveSelectionResult', [
            'candidates'   => $results['candidates'],
            'categories'   => $results['categories'],
            'categoryName' => 'Top Five Selection',
        ]);
    }

    public function setTopFive()
    {
        TopFiveCandidates::query()->delete();

        $topFive = $this->service->getTopFiveAccumulative();

        foreach ($topFive as $data) {
            TopFiveCandidates::create([
                'candidate_id' => $data['candidate']->id,
                'accumulative' => $data['accumulative'],
            ]);
        }

        return redirect()->back()->with(
            'success',
            'Top 5 candidates saved with accumulative scores successfully!'
        );
    }

    // PDF Export Methods
    public function exportCategoryPdf(string $category, string $name)
    {
        $results = $this->service->getResultsPerCategory($category);
        
        $pdf = Pdf::loadView('pdf.result-table', [
            'candidates' => $results['candidates'],
            'judgeOrder' => $results['judgeOrder'],
            'categoryName' => $name,
            'maxPoints' => 100,
            'isAverageScore' => false,
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($name . ' Results.pdf');
    }

    public function exportCreativeAttirePdf()
    {
        return $this->exportCategoryPdf('creative_attire', 'Bangkarera Creative Attire');
    }

    public function exportCasualWearPdf()
    {
        return $this->exportCategoryPdf('casual_wear', 'Casual Wear');
    }

    public function exportSwimWearPdf()
    {
        return $this->exportCategoryPdf('swim_wear', 'Swim Wear');
    }

    public function exportFilipinianaPdf()
    {
        return $this->exportCategoryPdf('filipiniana_attire', 'Evening Long Gown');
    }

    public function exportTopFiveSelectionPdf()
    {
        $results = $this->service->getTopFiveSelectionResults();
        
        $pdf = Pdf::loadView('pdf.top-five-selection-table', [
            'candidates' => $results['candidates'],
            'categories' => $results['categories'],
            'categoryLabels' => [
                'creative_attire' => 'Bangkarera Creative Attire',
                'casual_wear' => 'Casual Wear',
                'swim_wear' => 'Swim Wear',
                'filipiniana_attire' => 'Evening Long Gown',
            ],
            'categoryName' => 'Top Five Selection',
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Top Five Selection Results.pdf');
    }
}
