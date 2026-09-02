<?php

namespace Modules\Frontend\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Master\Models\District;

class DistrictLookupController extends Controller
{
    public function __invoke(Request $request): JsonResource
    {
        $query = District::query()
            ->select(['id', 'code', 'name', 'name_st'])
            ->orderBy('name');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return JsonResource::collection($query->get());
    }
}

