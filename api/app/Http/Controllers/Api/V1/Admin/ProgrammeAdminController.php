<?php

// app/Http/Controllers/Api/V1/Admin/ProgrammeAdminController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ProgrammeStoreRequest;
use App\Http\Requests\Api\V1\Admin\ProgrammeUpdateRequest;
use App\Models\Programme;
use App\Models\RequirementFlag;
use Illuminate\Http\Request;

class ProgrammeAdminController extends Controller
{
    public function index(Request $req)
    {
        $q = Programme::with('faculty:id,name,institution_id', 'faculty.institution:id,name', 'interestArea:id,name', 'flags:id,code,label');
        if ($req->filled('q')) {
            $q->where('name', 'like', '%' . $req->q . '%');
        }

        if ($req->filled('institution_id')) {
            $q->whereHas('faculty', fn($x) => $x->where('institution_id', $req->institution_id));
        }

        if ($req->filled('faculty_id')) {
            $q->where('faculty_id', $req->faculty_id);
        }

        if ($req->filled('interest_area_id')) {
            $q->where('interest_area_id', $req->interest_area_id);
        }

        if ($req->filled('course_type')) {
            $q->where('course_type', $req->course_type);
        }

        return response()->json($q->orderBy('name')->paginate($req->integer('per_page', 20)));
    }

    public function store(ProgrammeStoreRequest $req)
    {
        $p = Programme::create($req->validated());
        if ($req->filled('flag_codes')) {
            $ids = RequirementFlag::whereIn('code', $req->flag_codes)->pluck('id')->all();
            $p->flags()->sync($ids);
        }
        return response()->json(['data' => $p->load('faculty.institution', 'interestArea', 'flags')], 201);
    }

    public function update(ProgrammeUpdateRequest $req, Programme $programme)
    {
        $programme->update($req->validated());
        if ($req->has('flag_codes')) {
            $ids = RequirementFlag::whereIn('code', $req->flag_codes ?? [])->pluck('id')->all();
            $programme->flags()->sync($ids);
        }
        return response()->json(['data' => $programme->load('faculty.institution', 'interestArea', 'flags')]);
    }

    public function destroy(Programme $programme)
    {
        $programme->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
