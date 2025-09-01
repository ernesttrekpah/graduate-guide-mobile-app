<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConsentStoreRequest;
use App\Models\Consent;
use Illuminate\Support\Carbon;

class ConsentController extends Controller
{
    public function store(ConsentStoreRequest $req)
    {
        $user    = $req->user();
        $consent = Consent::create([
            'user_id'        => $user->id,
            'policy_version' => $req->policy_version,
            'granted_at'     => Carbon::now(),
        ]);

        return response()->json(['consent' => $consent], 201);
    }
}
