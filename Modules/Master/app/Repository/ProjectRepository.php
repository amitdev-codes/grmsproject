<?php

namespace Modules\Master\Repository;

use App\Repositories\BaseRepository;
use Modules\Master\Models\Project;

class ProjectRepository extends BaseRepository
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }
}