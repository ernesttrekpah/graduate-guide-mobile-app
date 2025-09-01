<?php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index(Request $req)
    {
        $q = Faculty::with('institution:id,name');
        if ($req->filled('institution_id')) {
            $q->where('institution_id', $req->institution_id);
        }

        return response()->json(['data' => $q->orderBy('name')->get()]);
    }
}
