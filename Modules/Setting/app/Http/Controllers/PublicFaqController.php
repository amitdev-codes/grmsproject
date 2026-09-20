<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Setting\Models\Faq;

class PublicFaqController extends Controller
{
    public function index(): JsonResponse
    {
        $locale = in_array(app()->getLocale(), ['en', 'st'], true) ? app()->getLocale() : 'en';
        $otherLocale = $locale === 'en' ? 'st' : 'en';

        $faqs = Faq::published()
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

        return response()->json(['data' => $faqs]);
    }
}
