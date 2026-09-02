<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Grievance\Models\GrievanceCategory;

class GrievanceCategoryLookupController extends Controller
{
    public function __invoke(Request $request): JsonResource
    {
        $query = GrievanceCategory::query()
            ->select(['id', 'name_en', 'name_st', 'division_id'])
            ->where('is_active', true)
            ->orderBy('name_en');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_st', 'like', "%{$search}%");
            });
        }



        return JsonResource::collection($query->get());
    }
}
