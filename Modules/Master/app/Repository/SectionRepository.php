<?php

namespace Modules\Master\Repository;

use App\DataTables\BaseDataTable;
use App\Repositories\BaseRepository;
use Modules\Master\Models\Section;

class SectionRepository extends BaseRepository
{
    public function __construct(Section $model)
    {
        parent::__construct($model);
    }

}
