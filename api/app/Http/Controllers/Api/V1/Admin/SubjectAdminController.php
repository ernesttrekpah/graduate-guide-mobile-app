<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectAlias;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectAdminController extends Controller
{
    public function index(Request $req)
    {
        $q    = trim((string) $req->query('q', ''));
        $rows = Subject::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'group']);
        return response()->json(['data' => $rows]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'code'  => ['required', 'string', 'max:50', 'unique:subjects,code'],
            'name'  => ['required', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:50'], // Core/Elective or null
        ]);
        $s = Subject::create($data);
        return response()->json(['message' => 'Created', 'data' => $s], 201);
    }

    public function update(Request $req, Subject $subject)
    {
        $data = $req->validate([
            'code'  => ['sometimes', 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'name'  => ['sometimes', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:50'],
        ]);
        $subject->update($data);
        return response()->json(['message' => 'Updated', 'data' => $subject]);
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function aliases(Subject $subject)
    {
        $aliases = $subject->aliases()->orderBy('alias')->get(['id', 'subject_id', 'alias']);
        return response()->json(['data' => $aliases]);
    }

    public function addAlias(Request $req, Subject $subject)
    {
        $data = $req->validate([
            'alias' => ['required', 'string', 'max:255', 'unique:subject_aliases,alias'],
        ]);
        $alias = $subject->aliases()->create($data);
        return response()->json(['message' => 'Alias added', 'data' => $alias], 201);
    }

    public function deleteAlias(SubjectAlias $alias)
    {
        $alias->delete();
        return response()->json(['message' => 'Alias deleted']);
    }
}
