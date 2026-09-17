<?php

namespace Modules\Master\Repository;

use App\Repositories\BaseRepository;
use Modules\Master\Models\ProjectType;

class ProjectTypeRepository extends BaseRepository
{
    public function __construct(ProjectType $model)
    {
        parent::__construct($model);
    }
}