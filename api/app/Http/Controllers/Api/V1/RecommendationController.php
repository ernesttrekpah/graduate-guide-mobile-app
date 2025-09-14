<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecommendationItem;
use App\Models\RecommendationRun;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function generate(Request $req, RecommendationService $svc)
    {
        $req->validate(['top_n' => ['nullable', 'integer', 'between:1,50']]);

        $run = $svc->runForUser($req->user()->id, $req->integer('top_n', 10));

        // Preload + shape the items just like the app expects
        $items = $run->items()
            ->with([
                'programme.faculty:id,name,institution_id',
                'programme.faculty.institution:id,name',
                'programme.interestArea:id,name',
            ])
            ->orderByDesc('total_score')
            ->get()
            ->map(fn($it) => [
                'id'               => $it->id,
                'programme'        => $it->programme,           // includes faculty, institution, interestArea
                'score'            => (float) $it->total_score, // alias for the app
                'component_scores' => (array) $it->component_scores_json,
                'explanations'     => (array) $it->explanation_json,
                'action_plan_text' => $it->action_plan_text,
                'created_at'       => $it->created_at,
                'updated_at'       => $it->updated_at,
            ]);

        // (Optional) mark saved status for this user
        $savedProgrammeIds = $req->user()->savedProgrammes()->pluck('programmes.id')->all();
        $items             = $items->map(function ($row) use ($savedProgrammeIds) {
            $row['is_saved'] = in_array($row['programme']['id'], $savedProgrammeIds, true);
            return $row;
        });

        return response()->json([
            'run_id'       => $run->id,
            'generated_at' => $run->generated_at,
            'items'        => $items,
        ], 201);
    }

    public function index(Request $req)
    {
        $runs = RecommendationRun::where('user_id', $req->user()->id)
            ->with('version.ruleSet:id,name')
            ->latest('generated_at')
            ->paginate($req->integer('per_page', 10));

        return response()->json($runs);
    }

    public function show(Request $req, RecommendationRun $run)
    {
        abort_unless($run->user_id === $req->user()->id, 403);

        $run->load('version.ruleSet:id,name');

        $items = $run->items()
            ->with([
                'programme.faculty:id,name,institution_id',
                'programme.faculty.institution:id,name',
                'programme.interestArea:id,name',
            ])
            ->orderByDesc('total_score')
            ->get()
            ->map(fn($it) => [
                'id'               => $it->id,
                'programme'        => $it->programme,
                'score'            => (float) $it->total_score,
                'component_scores' => (array) $it->component_scores_json,
                'explanations'     => (array) $it->explanation_json,
                'action_plan_text' => $it->action_plan_text,
                'created_at'       => $it->created_at,
                'updated_at'       => $it->updated_at,
            ]);

        // (Optional) saved status
        $savedProgrammeIds = $req->user()->savedProgrammes()->pluck('programmes.id')->all();
        $items             = $items->map(function ($row) use ($savedProgrammeIds) {
            $row['is_saved'] = in_array($row['programme']['id'], $savedProgrammeIds, true);
            return $row;
        });

        // Attach shaped items back onto the run so /show returns { data: { ..., items: [...] } }
        $run->setRelation('items', $items);

        return response()->json(['data' => $run]);
    }

    public function showItem(Request $req, RecommendationItem $item)
    {
        // owner check via the run relationship
        abort_unless($item->run && $item->run->user_id === $req->user()->id, 403);

        // eager load programme relations
        $item->load(['programme.faculty.institution', 'programme.interestArea', 'programme.jobProspects']);

        // decorate with is_saved like in the run list
        $isSaved = $req->user()
            ->savedProgrammes()
            ->where('programmes.id', $item->programme_id)
            ->exists();

        // Option 1: attach dynamically (no schema change)
        $arr             = $item->toArray();
        $arr['is_saved'] = $isSaved;

        return response()->json(['data' => $arr]);
    }
}
