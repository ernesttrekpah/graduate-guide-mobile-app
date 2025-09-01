<?php

// app/Http/Controllers/Api/V1/Admin/FacultyAdminController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\FacultyStoreRequest;
use App\Http\Requests\Api\V1\Admin\FacultyUpdateRequest;
use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyAdminController extends Controller
{
    public function index(Request $req)
    {
        $q = Faculty::with('institution:id,name');
        if ($req->filled('institution_id')) {
            $q->where('institution_id', $req->institution_id);
        }

        if ($req->filled('q')) {
            $q->where('name', 'like', '%' . $req->q . '%');
        }

        return response()->json($q->orderBy('name')->paginate($req->integer('per_page', 20)));
    }
    public function store(FacultyStoreRequest $req)
    {
        $fac = Faculty::create($req->validated());
        return response()->json(['data' => $fac->load('institution:id,name')], 201);
    }
    public function update(FacultyUpdateRequest $req, Faculty $faculty)
    {
        $faculty->update($req->validated());
        return response()->json(['data' => $faculty->load('institution:id,name')]);
    }
    public function destroy(Faculty $faculty)
    {
        $faculty->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
