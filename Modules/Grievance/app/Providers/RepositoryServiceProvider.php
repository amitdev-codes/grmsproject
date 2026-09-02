<?php

namespace Modules\Grievance\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Repositories\GrievanceRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GrievanceRepositoryInterface::class, GrievanceRepository::class);
    }
}
