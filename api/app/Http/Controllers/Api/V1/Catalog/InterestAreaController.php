<?php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Models\InterestArea;

class InterestAreaController extends Controller
{
    public function index()
    {
        return response()->json(['data' => InterestArea::orderBy('name')->get()]);
    }
}
