<?php

namespace Modules\Grievance\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Repositories\GrievanceRepository;

class GrievanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->mergeConfigFrom(module_path('Grievance', 'config/config.php'), 'grievance');
        $this->app->bind(
            GrievanceRepositoryInterface::class,
            GrievanceRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(module_path('Grievance', 'resources/views'), 'grievance');
    }
}
