<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeMapping;
use App\Models\GradeScale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeScaleAdminController extends Controller
{
    public function index(Request $req)
    {
        $q    = trim((string) $req->query('q', ''));
        $rows = GradeScale::query()
            ->when($q !== '', fn($qr) => $qr->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%"))
            ->orderBy('name')
            ->get(['id', 'name', 'description']);
        return response()->json(['data' => $rows]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:grade_scales,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $row = GradeScale::create($data);
        return response()->json(['message' => 'Created', 'data' => $row], 201);
    }

    public function update(Request $req, GradeScale $scale)
    {
        $data = $req->validate([
            'name'        => ['sometimes', 'string', 'max:255', Rule::unique('grade_scales', 'name')->ignore($scale->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $scale->update($data);
        return response()->json(['message' => 'Updated', 'data' => $scale]);
    }

    public function destroy(GradeScale $scale)
    {
        // cascading delete of mappings if you set FK onDelete('cascade'); otherwise:
        // GradeMapping::where('scale_id', $scale->id)->delete();
        $scale->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function mappings(GradeScale $scale)
    {
        $rows = $scale->mappings()->orderBy('numeric_value')->get(['id', 'scale_id', 'label', 'numeric_value']);
        return response()->json(['data' => $rows]);
    }

    public function addMapping(Request $req, GradeScale $scale)
    {
        $data = $req->validate([
            'label'         => ['required', 'string', 'max:50',
                Rule::unique('grade_mappings', 'label')->where(fn($q) => $q->where('scale_id', $scale->id))],
            'numeric_value' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);
        $row = $scale->mappings()->create($data);
        return response()->json(['message' => 'Created', 'data' => $row], 201);
    }

    public function updateMapping(Request $req, GradeMapping $mapping)
    {
        $data = $req->validate([
            'label'         => ['sometimes', 'string', 'max:50',
                Rule::unique('grade_mappings', 'label')
                    ->where(fn($q) => $q->where('scale_id', $mapping->scale_id))
                    ->ignore($mapping->id)],
            'numeric_value' => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ]);
        $mapping->update($data);
        return response()->json(['message' => 'Updated', 'data' => $mapping]);
    }

    public function deleteMapping(GradeMapping $mapping)
    {
        $mapping->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
