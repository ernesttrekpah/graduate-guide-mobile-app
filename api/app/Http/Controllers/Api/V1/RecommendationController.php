<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecommendationRun;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function generate(Request $req, RecommendationService $svc)
    {
        $req->validate(['top_n' => ['nullable', 'integer', 'between:1,50']]);
        $run = $svc->runForUser($req->user()->id, $req->integer('top_n', 10));

        return response()->json([
            'run_id'       => $run->id,
            'generated_at' => $run->generated_at,
            'items'        => $run->items()->with('programme.faculty.institution', 'programme.interestArea')->orderByDesc('total_score')->get(),
        ], 201);
    }

    public function index(Request $req)
    {
        $runs = RecommendationRun::where('user_id', $req->user()->id)
            ->with('version.ruleSet:id,name')
            ->latest('generated_at')->paginate($req->integer('per_page', 10));

        return response()->json($runs);
    }

    public function show(Request $req, RecommendationRun $run)
    {
        abort_unless($run->user_id === $req->user()->id, 403);
        $run->load('items.programme.faculty.institution', 'version.ruleSet', 'items.programme.interestArea');
        return response()->json(['data' => $run]);
    }
}
