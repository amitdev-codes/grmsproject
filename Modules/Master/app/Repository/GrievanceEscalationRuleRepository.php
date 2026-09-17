<?php

namespace Modules\Master\Repository;

use App\Repositories\BaseRepository;
use Modules\Master\Models\GrievanceEscalationRule;

class GrievanceEscalationRuleRepository extends BaseRepository
{
    public function __construct(GrievanceEscalationRule $model)
    {
        parent::__construct($model);
    }
}