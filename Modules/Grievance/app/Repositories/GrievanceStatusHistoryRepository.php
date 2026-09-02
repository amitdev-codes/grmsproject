<?php

namespace Modules\Grievance\Repositories;

use App\Repositories\BaseRepository;
use Modules\Grievance\Models\GrievanceStatusHistory;

class GrievanceStatusHistoryRepository extends BaseRepository
{
    public function __construct(GrievanceStatusHistory $model)
    {
        parent::__construct($model);
    }


}
