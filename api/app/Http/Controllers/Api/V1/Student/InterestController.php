<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;




use App\Http\Requests\Api\V1\InterestAssessmentStoreRequest;

use App\Models\InterestAnswer;
use App\Models\InterestAssessment;
use App\Models\InterestQuestion;

class InterestController extends Controller
{
    public function questions()
    {
        $qs = InterestQuestion::where('active', true)->orderBy('id')->get(['id', 'text', 'domain', 'weight']);
        return response()->json(['data' => $qs]);
    }

    public function submit(InterestAssessmentStoreRequest $req)
    {
        $profile = $req->user()->profile()->firstOrCreate(['user_id' => $req->user()->id]);

        $assessment = InterestAssessment::create([
            'profile_id'         => $profile->id,
            'instrument_version' => $req->instrument_version,
        ]);

        $rows = [];
        foreach ($req->answers as $ans) {
            $rows[] = [
                'assessment_id' => $assessment->id,
                'question_id'   => $ans['question_id'],
                'value'         => $ans['value'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        InterestAnswer::insert($rows);

        $assessment->load('answers.question:id,text,domain,weight');
        return response()->json(['data' => $assessment], 201);
    }

    public function latest(Request $req)
    {
        $profile = $req->user()->profile;
        if (! $profile) {
            return response()->json(['data' => null]);
        }

        $last = $profile->interestAssessments()->with('answers.question:id,text,domain,weight')->latest()->first();
        return response()->json(['data' => $last]);
    }
}
