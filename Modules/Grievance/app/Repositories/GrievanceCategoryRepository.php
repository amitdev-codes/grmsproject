<?php

namespace Modules\Grievance\Repositories;

use App\Repositories\BaseRepository;
use Modules\Grievance\Models\GrievanceCategory;

class GrievanceCategoryRepository extends BaseRepository
{
    public function __construct(GrievanceCategory $model)
    {
        parent::__construct($model);
    }

}
