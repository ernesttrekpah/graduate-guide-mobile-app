<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequirementFlag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RequirementFlagAdminController extends Controller
{
    public function index(Request $req)
    {
        $q    = trim((string) $req->query('q', ''));
        $rows = RequirementFlag::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'description']);

        return response()->json(['data' => $rows]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'code'        => ['required', 'string', 'max:100', 'unique:requirement_flags,code'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $row = RequirementFlag::create($data);
        return response()->json(['message' => 'Created', 'data' => $row], 201);
    }

    public function update(Request $req, RequirementFlag $flag)
    {
        $data = $req->validate([
            'code'        => ['sometimes', 'string', 'max:100', Rule::unique('requirement_flags', 'code')->ignore($flag->id)],
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $flag->update($data);
        return response()->json(['message' => 'Updated', 'data' => $flag]);
    }

    public function destroy(RequirementFlag $flag)
    {
        $flag->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
