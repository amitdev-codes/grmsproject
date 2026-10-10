<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Setting\Http\Requests\StoreApplicationTranslationRequest;
use Modules\Setting\Http\Requests\UpdateApplicationTranslationRequest;
use Modules\Setting\Models\ApplicationTranslation;
use Modules\Setting\Services\ApplicationTranslationService;

class ApplicationTranslationController extends Controller
{
    public function __construct(protected ApplicationTranslationService $translations) {}

    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->toString());
        $filters = (array) $request->input('filters', []);
        $sesothoStatuses = array_intersect(
            ['translated', 'missing'],
            (array) ($filters['sesotho_status'] ?? []),
        );
        $sort = $request->string('sort', 'translation_key')->toString();
        $sort = in_array($sort, ['translation_key', 'english_text', 'sesotho_text', 'created_at'], true)
            ? $sort
            : 'translation_key';
        $direction = $request->string('order', 'asc')->toString();
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $items = ApplicationTranslation::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('translation_key', 'like', "%{$search}%")
                    ->orWhere('english_text', 'like', "%{$search}%")
                    ->orWhere('sesotho_text', 'like', "%{$search}%");
            }))
            ->when($sesothoStatuses !== [] && count($sesothoStatuses) < 2, fn ($query) => $query->where(function ($query) use ($sesothoStatuses) {
                if (in_array('missing', $sesothoStatuses, true)) {
                    $query->whereNull('sesotho_text')->orWhere('sesotho_text', '');
                } else {
                    $query->whereNotNull('sesotho_text')->where('sesotho_text', '<>', '');
                }
            }))
            ->orderBy($sort, $direction)
            ->paginate(max(1, min($request->integer('per_page', 15), 100)))
            ->withQueryString();

        return Inertia::render('Setting::Settings/ApplicationTranslations/Index', [
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Setting::Settings/ApplicationTranslations/Form', [
            'translation' => null,
        ]);
    }

    public function edit(ApplicationTranslation $translation): Response
    {
        return Inertia::render('Setting::Settings/ApplicationTranslations/Form', [
            'translation' => $translation,
        ]);
    }

    public function store(StoreApplicationTranslationRequest $request): RedirectResponse
    {
        ApplicationTranslation::create($request->validated());
        $this->translations->clearCache();

        return redirect()->route('settings.translations.index')->with('success', 'Translation added.');
    }

    public function update(
        UpdateApplicationTranslationRequest $request,
        ApplicationTranslation $translation,
    ): RedirectResponse {
        $translation->update($request->validated());
        $this->translations->clearCache();

        return redirect()->route('settings.translations.index')->with('success', 'Translation updated.');
    }

    public function destroy(ApplicationTranslation $translation): RedirectResponse
    {
        $translation->delete();
        $this->translations->clearCache();

        return back()->with('success', 'Translation deleted.');
    }
}
