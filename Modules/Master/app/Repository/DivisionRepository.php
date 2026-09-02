<?php

namespace Modules\Master\Repository;

use App\Repositories\BaseRepository;
use Modules\Master\Models\Division;

class DivisionRepository extends BaseRepository
{
    public function __construct(Division $model)
    {
        parent::__construct($model);
    }

}
