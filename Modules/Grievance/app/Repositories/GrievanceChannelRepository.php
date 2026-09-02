<?php

namespace Modules\Grievance\Repositories;

use App\Repositories\BaseRepository;
use Modules\Grievance\Models\GrievanceChannel;

class GrievanceChannelRepository extends BaseRepository
{
    public function __construct(GrievanceChannel $model)
    {
        parent::__construct($model);
    }

}
