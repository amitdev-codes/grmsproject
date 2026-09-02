<?php

namespace Modules\Grievance\Repositories;

use App\Repositories\BaseRepository;
use Modules\Grievance\Models\Resolution;

class ResolutionRepository extends BaseRepository
{
    public function __construct(Resolution $model)
    {
        parent::__construct($model);
    }

}
