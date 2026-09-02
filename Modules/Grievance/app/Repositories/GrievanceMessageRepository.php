<?php

namespace Modules\Grievance\Repositories;

use App\Repositories\BaseRepository;
use Modules\Grievance\Models\GrievanceMessage;

class GrievanceMessageRepository extends BaseRepository
{
    public function __construct(GrievanceMessage $model)
    {
        parent::__construct($model);
    }

}
