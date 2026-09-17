<?php

namespace Modules\Master\Repository;

use App\Repositories\BaseRepository;
use Modules\Master\Models\ServiceProvider;

class ServiceProviderRepository extends BaseRepository
{
    public function __construct(ServiceProvider $model)
    {
        parent::__construct($model);
    }
}