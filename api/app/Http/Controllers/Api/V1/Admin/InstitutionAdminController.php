<?php

// app/Http/Controllers/Api/V1/Admin/InstitutionAdminController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\InstitutionStoreRequest;
use App\Http\Requests\Api\V1\Admin\InstitutionUpdateRequest;
use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionAdminController extends Controller
{
    public function index(Request $req) {
        $q = Institution::query();
        if ($req->filled('q')) $q->where('name','like','%'.$req->q.'%');
        return response()->json($q->orderBy('name')->paginate($req->integer('per_page',20)));
    }
    public function store(InstitutionStoreRequest $req) {
        $inst = Institution::create($req->validated());
        return response()->json(['data'=>$inst], 201);
    }
    public function update(InstitutionUpdateRequest $req, Institution $institution) {
        $institution->update($req->validated());
        return response()->json(['data'=>$institution]);
    }
    public function destroy(Institution $institution) {
        $institution->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
