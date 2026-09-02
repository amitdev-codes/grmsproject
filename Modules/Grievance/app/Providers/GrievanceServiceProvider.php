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
        $this->app->bind(
            GrievanceRepositoryInterface::class,
            GrievanceRepository::class
        );
    }

    public function boot(): void
    {
        // ...
    }
}
