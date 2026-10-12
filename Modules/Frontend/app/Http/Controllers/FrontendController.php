<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Frontend\Services\PublicAnalyticsService;
use Modules\Frontend\Services\SiteVisitCounter;
use Modules\Grievance\Models\VoiceGrievance;
use Modules\Setting\Models\Faq;

class FrontendController extends Controller
{
    public function __construct(
        protected PublicAnalyticsService $analytics,
        protected SiteVisitCounter $siteVisits,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->siteVisits->record($request);

        return Inertia::render('Frontend::landing', [
            'landingStats' => $this->analytics->landingData(),
        ]);
    }

    public function faq(Request $request): Response
    {
        $this->siteVisits->record($request);
        $locale = in_array(app()->getLocale(), ['en', 'st'], true) ? app()->getLocale() : 'en';
        $otherLocale = $locale === 'en' ? 'st' : 'en';
        $faqs = Faq::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Faq $faq): array => [
                'id' => $faq->id,
                'question' => $faq->{"question_{$locale}"} ?: $faq->{"question_{$otherLocale}"} ?: '',
                'answer' => $faq->{"answer_{$locale}"} ?: $faq->{"answer_{$otherLocale}"} ?: '',
                'sort_order' => $faq->sort_order,
            ])
            ->values();

        return Inertia::render('Frontend::faq', [
            'faqs' => $faqs,
        ]);
    }

    public function contact(Request $request): Response
    {
        $this->siteVisits->record($request);

        return Inertia::render('Frontend::contact');
    }

    public function fileGrievance(Request $request): Response
    {
        $this->siteVisits->record($request);

        return Inertia::render('Frontend::file-grievance', [
            'speechToText' => [
                'enabled' => (bool) config('grievance.speech_to_text.enabled'),
                'provider' => config('grievance.speech_to_text.provider'),
                'language' => config('grievance.speech_to_text.language'),
            ],
            'aiCategorySuggestionEnabled' => (bool) config('grievance.ai.enabled'),
        ]);
    }

    public function voiceGrievance(Request $request): Response
    {
        $this->siteVisits->record($request);

        return Inertia::render('Frontend::voice-grievance');
    }

    public function voiceGrievanceReceived(string $reference): Response
    {
        $voiceGrievance = VoiceGrievance::query()
            ->where('reference_no', $reference)
            ->firstOrFail();

        return Inertia::render('Frontend::voice-grievance', [
            'received' => true,
            'referenceNo' => $voiceGrievance->reference_no,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('frontend::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('frontend::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('frontend::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
