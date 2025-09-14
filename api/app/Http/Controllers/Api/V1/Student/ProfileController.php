<?php
namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfileUpdateRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $req)
    {
        $profile = $req->user()->profile()
            ->with(['currentExamResult.subjects.subject:id,code,name,group'])
            ->first();

        return response()->json(['data' => $profile]);
    }

    public function upsert(ProfileUpdateRequest $req)
    {
        $user = $req->user();
        $data = $req->validated();

        // create or update the student's profile row
        $profile = $user->profile()->updateOrCreate(['user_id' => $user->id], $data);

        // return 200 OK for idempotent updates
        return response()->json(['data' => $profile], 200);
    }
}
