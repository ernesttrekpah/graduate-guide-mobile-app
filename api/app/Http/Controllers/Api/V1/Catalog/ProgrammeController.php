<?php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    public function index(Request $req)
    {
        $q = Programme::query()
            ->with([
                'faculty:id,name,institution_id',
                'faculty.institution:id,name',
                'interestArea:id,name',
                'flags:id,code,label',
            ]);

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

    public function show(Programme $programme)
    {
        $programme->load([
            'faculty:id,name,institution_id',
            'faculty.institution:id,name,short_name,region,website',
            'interestArea:id,name',
            'flags:id,code,label',
            'jobProspects:id,programme_id,title,description',
            'requirementSets.items.subject:id,code,name,group',
            'requirementSets.items.choiceGroup.subjects:id,code,name,group',
            'requirementSets.items.constraints.scale:id,name',
        ]);

        return response()->json(['data' => $programme]);
    }

}
