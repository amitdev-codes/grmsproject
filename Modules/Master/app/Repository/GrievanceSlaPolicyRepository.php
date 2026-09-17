<?php

namespace Modules\Master\Repository;

use App\Repositories\BaseRepository;
use Modules\Master\Models\GrievanceSlaPolicy;

class GrievanceSlaPolicyRepository extends BaseRepository
{
    public function __construct(GrievanceSlaPolicy $model)
    {
        parent::__construct($model);
    }
}