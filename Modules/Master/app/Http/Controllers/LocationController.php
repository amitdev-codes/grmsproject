<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;

class LocationController extends Controller
{
    /** GET /locations/divisions?district_id=1 */
    public function divisions(Request $request)
    {
        $request->validate(['district_id' => 'required|integer|exists:districts,id']);

        return Division::where('district_id', $request->integer('district_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** GET /locations/sections?division_id=1 */
    public function sections(Request $request)
    {
        $request->validate(['division_id' => 'required|integer|exists:divisions,id']);

        return Section::where('division_id', $request->integer('division_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
