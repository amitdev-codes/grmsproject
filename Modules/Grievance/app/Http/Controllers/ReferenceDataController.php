<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Grievance\Http\Resources\DistrictResource;
use Modules\Grievance\Http\Resources\DivisionResource;
use Modules\Grievance\Http\Resources\GrievanceCategoryResource;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;

class ReferenceDataController extends Controller
{
    public function categories(): AnonymousResourceCollection
    {
        return GrievanceCategoryResource::collection(GrievanceCategory::active()->get());
    }

    public function districts(): AnonymousResourceCollection
    {
        return DistrictResource::collection(District::orderBy('name')->get());
    }
    public function divisions(): AnonymousResourceCollection
    {
        return DivisionResource::collection(Division::orderBy('name')->get());
    }
}
