<?php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Subject::orderBy('group')->orderBy('name')->get()]);
    }
}
