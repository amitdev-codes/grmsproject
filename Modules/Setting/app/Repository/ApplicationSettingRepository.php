<?php

namespace Modules\Setting\Repository;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Modules\Setting\Models\ApplicationSetting;

class ApplicationSettingRepository extends BaseRepository
{
    public function __construct(ApplicationSetting $model)
    {
        parent::__construct($model);
    }
    /** There is always exactly one row — callers never null-check this. */

    public function current(): Model
    {
        return $this->model->query()->firstOrCreate(
            [],
            [
                'project_name' => config('app.name'),
                'project_slug' => str(config('app.name'))->slug(),
            ],
        );
    }

}
