<?php

namespace Modules\Grievance\Repositories;

use App\Repositories\BaseRepository;
use Modules\Grievance\Models\GrievanceEscalation;

class GrievanceEscalationRepository extends BaseRepository
{
    public function __construct(GrievanceEscalation $model)
    {
        parent::__construct($model);
    }

}
