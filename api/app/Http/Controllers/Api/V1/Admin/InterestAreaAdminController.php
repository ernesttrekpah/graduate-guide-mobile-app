<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterestArea;
use Illuminate\Http\Request;

class InterestAreaAdminController extends Controller
{
    public function index(Request $req)
    {
        $q    = trim((string) $req->query('q', ''));
        $rows = InterestArea::query()
            ->when($q !== '', fn($qr) => $qr->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json(['data' => $rows]);
    }

    public function store(Request $req)
    {
        $data = $req->validate(['name' => ['required', 'string', 'max:255', 'unique:interest_areas,name']]);
        $row  = InterestArea::create($data);
        return response()->json(['message' => 'Created', 'data' => $row], 201);
    }

    public function update(Request $req, InterestArea $area)
    {
        $data = $req->validate(['name' => ['required', 'string', 'max:255', 'unique:interest_areas,name,' . $area->id]]);
        $area->update($data);
        return response()->json(['message' => 'Updated', 'data' => $area]);
    }

    public function destroy(InterestArea $area)
    {
        $area->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
