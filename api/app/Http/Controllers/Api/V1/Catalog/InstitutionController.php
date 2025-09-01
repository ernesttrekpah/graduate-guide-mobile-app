<?php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Institution;

class InstitutionController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Institution::orderBy('name')->get()]);
    }
}
