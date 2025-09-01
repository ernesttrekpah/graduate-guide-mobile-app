<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FeedbackStoreRequest;
use App\Models\Feedback;
use App\Models\RecommendationItem;
use App\Models\RecommendationRun;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(FeedbackStoreRequest $req, RecommendationItem $item)
    {
        // Ownership: item must belong to a run of this user
        $run = RecommendationRun::findOrFail($item->run_id);
        abort_unless($run->user_id === $req->user()->id, 403);

        $fb = Feedback::updateOrCreate(
            ['user_id' => $req->user()->id, 'recommendation_item_id' => $item->id],
            ['rating_1_5' => $req->rating_1_5, 'comment' => $req->comment]
        );

        return response()->json(['data' => $fb], 201);
    }

    public function myFeedback(Request $req)
    {
        $list = Feedback::where('user_id', $req->user()->id)
            ->with('item.programme:id,name', 'item.run:id,generated_at')
            ->latest()->paginate($req->integer('per_page', 20));

        return response()->json($list);
    }
}
