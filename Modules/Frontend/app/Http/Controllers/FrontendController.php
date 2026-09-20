<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Frontend\Services\PublicAnalyticsService;
use Modules\Setting\Models\Faq;

class FrontendController extends Controller
{
    public function __construct(protected PublicAnalyticsService $analytics) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $this->incrementSiteVisits();

        return Inertia::render('Frontend::landing', [
            'landingStats' => $this->analytics->landingData(),
        ]);
    }

    public function faq(): Response
    {
        $this->incrementSiteVisits();
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

    public function contact(): Response
    {
        $this->incrementSiteVisits();

        return Inertia::render('Frontend::contact');
    }

    public function fileGrievance(): Response
    {
        $this->incrementSiteVisits();

        return Inertia::render('Frontend::file-grievance');
    }

    private function incrementSiteVisits(): void
    {
        if (DB::table('page_view_counters')->where('key', 'site_total')->exists()) {
            DB::table('page_view_counters')
                ->where('key', 'site_total')
                ->increment('count');

            return;
        }

        DB::table('page_view_counters')->insert([
            'key' => 'site_total',
            'count' => 1,
            'created_at' => now(),
            'updated_at' => now(),
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
