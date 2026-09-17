<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreServiceProviderRequest;
use Modules\Master\Http\Requests\UpdateServiceProviderRequest;
use Modules\Master\Models\ServiceProvider;
use Modules\Master\Services\ServiceProviderService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServiceProviderController extends Controller
{
    public function __construct(protected ServiceProviderService $serviceProviderService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::ServiceProvider/Index', [
            ...$this->serviceProviderService->table($request)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::ServiceProvider/ServiceProviderForm', $this->serviceProviderService->forCreate());
    }

    public function edit(ServiceProvider $serviceProvider): Response
    {
        return Inertia::render('Master::ServiceProvider/ServiceProviderForm', $this->serviceProviderService->forEdit($serviceProvider));
    }

    public function store(StoreServiceProviderRequest $request): RedirectResponse
    {
        $this->serviceProviderService->store($request->validated());

        return redirect()->route('service-providers.index')->with('success', 'Service provider created successfully.');
    }

    public function update(UpdateServiceProviderRequest $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        $this->serviceProviderService->update($serviceProvider, $request->validated());

        return redirect()->route('service-providers.index')->with('success', 'Service provider updated successfully.');
    }

    public function destroy(ServiceProvider $serviceProvider): RedirectResponse
    {
        $this->serviceProviderService->destroy($serviceProvider);

        return back()->with('success', 'Service provider deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:service_providers,id',
        ]);

        $this->serviceProviderService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected service providers deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->serviceProviderService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->serviceProviderService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} service providers.")
            ->with('import_failures', $result['failures']);
    }
}