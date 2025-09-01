<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



use App\Http\Requests\Api\V1\ExamResultsUpsertRequest;

use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\Subject;
use App\Models\SubjectAlias;
use App\Services\Grades;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    protected function resolveSubject(string $label): Subject
    {
        // Try by code
        if ($s = Subject::where('code', $label)->first()) {
            return $s;
        }

        // Try by exact name
        if ($s = Subject::where('name', $label)->first()) {
            return $s;
        }

        // Try alias
        if ($a = SubjectAlias::where('alias', $label)->first()) {
            return $a->subject;
        }

        // Try case-insensitive on name
        if ($s = Subject::whereRaw('LOWER(name) = ?', [strtolower($label)])->first()) {
            return $s;
        }

        abort(422, "Unknown subject: {$label}");
    }

    public function show(Request $req)
    {
        $profile = $req->user()->profile;
        if (! $profile) {
            return response()->json(['data' => null]);
        }

        $exam = $profile->currentExamResult()->with('subjects.subject:id,code,name,group')->first();
        return response()->json(['data' => $exam]);
    }

    public function upsert(ExamResultsUpsertRequest $req, Grades $grades)
    {
        $user    = $req->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        return DB::transaction(function () use ($req, $profile, $grades) {
            // Mark other results non-current
            $profile->examResults()->update(['is_current' => false]);

            $exam = ExamResult::create([
                'profile_id'   => $profile->id,
                'exam_type'    => $req->exam_type,
                'sitting_year' => $req->sitting_year,
                'is_current'   => true,
            ]);

            $rows = [];
            foreach ($req->subjects as $item) {
                $s      = $this->resolveSubject($item['subject']);
                $rows[] = [
                    'exam_result_id' => $exam->id,
                    'subject_id'     => $s->id,
                    'grade_label'    => strtoupper($item['grade_label']),
                    'grade_numeric'  => $grades->toNumeric($item['grade_label']),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            ExamSubject::insert($rows);

            $exam->load('subjects.subject:id,code,name,group');
            return response()->json(['data' => $exam], 201);
        });
    }
}

