<?php
namespace App\Services;

use App\Models\RuleSet;
use App\Models\Subject;;
use App\Models\Programme;
use App\Models\RuleSetVersion;
use App\Models\StudentProfile;
use App\Models\RecommendationRun;
use App\Models\RecommendationItem;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function runForUser(int $userId, int $topN = 10): RecommendationRun
    {
        $version = RuleSet::whereNotNull('active_version_id')->with('activeVersion')->firstOrFail()->activeVersion;

        // Snapshot inputs
        $profile = StudentProfile::with([
            'currentExamResult.subjects.subject:id,code,name,group',
            'interestAssessments.answers.question',
        ])->where('user_id', $userId)->first();

        $snapshot  = $this->buildSnapshot($profile);
        $gradesMap = $this->gradesMap($profile);

        // Compute scores for all programmes
        $rows = [];
        Programme::with([
            'faculty.institution:id,name',
            'interestArea:id,name',
            'flags:id,code,label',
            'requirementSets.items.subject:id,code,name,group',
            'requirementSets.items.choiceGroup.subjects:id,code,name,group',
            'requirementSets.items.constraints.scale:id,name',
            'jobProspects:id,programme_id,title',
        ])->chunk(250, function ($programmes) use (&$rows, $version, $gradesMap, $snapshot) {
            foreach ($programmes as $p) {
                $calc = $this->scoreProgramme($p, $gradesMap, $snapshot, $version);
                if ($calc['eligible']) {
                    $rows[] = ['programme' => $p, 'calc' => $calc];
                }

            }
        });

        // Sort & take top N
        usort($rows, fn($a, $b) => $b['calc']['total_score'] <=> $a['calc']['total_score']);
        $top = array_slice($rows, 0, $topN);

        // Persist run + items atomically
        return DB::transaction(function () use ($userId, $version, $topN, $snapshot, $top) {
            $run = RecommendationRun::create([
                'user_id'               => $userId,
                'rule_set_version_id'   => $version->id,
                'top_n'                 => $topN,
                'profile_snapshot_json' => $snapshot,
                'generated_at'          => now(),
            ]);
            foreach ($top as $row) {
                RecommendationItem::create([
                    'run_id'                => $run->id,
                    'programme_id'          => $row['programme']->id,
                    'total_score'           => $row['calc']['total_score'],
                    'component_scores_json' => $row['calc']['components'],
                    'explanation_json'      => $row['calc']['explanations'],
                    'action_plan_text'      => $row['calc']['action_plan'],
                ]);
            }
            return $run->load('items.programme.faculty.institution', 'items.programme.interestArea');
        });
    }

    protected function buildSnapshot(?StudentProfile $profile): array
    {
        if (! $profile) {
            return ['has_profile' => false];
        }

        $exam   = $profile->currentExamResult;
        $grades = [];
        if ($exam) {
            foreach ($exam->subjects as $es) {
                $grades[$es->subject->name] = [
                    'label' => $es->grade_label, 'numeric' => $es->grade_numeric,
                ];
            }
        }
        // Simple interest summary: average by domain
        $domains = [];
        $latest  = optional($profile->interestAssessments()->latest()->first());
        if ($latest) {
            $answers = $latest->answers()->with('question')->get();
            foreach ($answers as $a) {
                $d                  = $a->question->domain ?: 'General';
                $domains[$d]['sum'] = ($domains[$d]['sum'] ?? 0) + $a->value * ($a->question->weight ?? 1);
                $domains[$d]['w']   = ($domains[$d]['w'] ?? 0) + ($a->question->weight ?? 1);
            }
            foreach ($domains as $d => $v) {
                $domains[$d] = $v['w'] ? $v['sum'] / $v['w'] : 0;
            }
        }
        arsort($domains);
        $topDomain = array_key_first($domains);

        return [
            'has_profile'      => true,
            'school'           => $profile->school,
            'region'           => $profile->region,
            'graduation_year'  => $profile->graduation_year,
            'grades'           => $grades,
            'interest_domains' => $domains,
            'top_domain'       => $topDomain,
        ];
    }

    protected function gradesMap(?StudentProfile $profile): array
    {
        $map = []; // subject_name => numeric grade
        if (! $profile || ! $profile->currentExamResult) {
            return $map;
        }

        foreach ($profile->currentExamResult->subjects as $es) {
            $map[strtolower($es->subject->name)] = (int) $es->grade_numeric;
        }
        return $map;
    }

    protected function scoreProgramme($programme, array $gradesMap, array $snap, RuleSetVersion $version): array
    {
        $w   = $version->weights_json ?? [];
        $def = $version->definition_json ?? [];

        // 1) Core match (must meet constraints if require_all=true)
        [$coreOk, $coreScore, $coreWhy] = $this->scoreSet($programme, 'core', $gradesMap, $def);

        if (($def['core']['require_all'] ?? true) && ! $coreOk) {
            return ['eligible' => false]; // disqualify early
        }

        // 2) Elective strength
        [$elecOk, $elecScore, $elecWhy] = $this->scoreSet($programme, 'elective', $gradesMap, $def);

        // 3) Aggregate fit
        [$aggOk, $aggScore, $aggWhy] = $this->scoreAggregate($programme, $gradesMap, $def);

        // 4) Interest fit
        [$intOk, $intScore, $intWhy] = $this->scoreInterest($programme, $snap, $def);

        // Weighted sum (0..100)
        $total =
            100 * (
            ($w['core_match'] ?? 0.4) * $coreScore +
            ($w['elective_strength'] ?? 0.25) * $elecScore +
            ($w['aggregate_fit'] ?? 0.20) * $aggScore +
            ($w['interest_fit'] ?? 0.15) * $intScore
        );

        // Build explanations & action plan
        $explanations = array_merge($coreWhy, $elecWhy, $aggWhy, $intWhy);
        $plan         = $this->actionPlan($programme, $gradesMap, $coreOk, $elecOk, $aggOk, $intOk);

        return [
            'eligible'     => true,
            'total_score'  => round($total, 3),
            'components'   => [
                'core_match'        => $coreScore,
                'elective_strength' => $elecScore,
                'aggregate_fit'     => $aggScore,
                'interest_fit'      => $intScore,
            ],
            'explanations' => $explanations,
            'action_plan'  => $plan,
        ];
    }

    protected function scoreSet($programme, string $kind, array $gradesMap, array $def): array
    {
        $set = $programme->requirementSets->firstWhere('kind', $kind);
        if (! $set) {
            return [true, 1.0, ["No {$kind} set defined (treated as satisfied)."]];
        }

        $items = $set->items;
        if ($items->isEmpty()) {
            return [true, 1.0, ["No {$kind} items (treated as satisfied)."]];
        }

        $met     = 0;
        $explain = [];
        foreach ($items as $it) {
            $bestNumeric = null;
            $label       = '';

            if ($it->subject) {
                $key         = strtolower($it->subject->name);
                $bestNumeric = $gradesMap[$key] ?? null;
                $label       = $it->subject->name;
            } elseif ($it->choiceGroup) {
                foreach ($it->choiceGroup->subjects as $s) {
                    $key = strtolower($s->name);
                    $g   = $gradesMap[$key] ?? null;
                    if ($g !== null && ($bestNumeric === null || $g < $bestNumeric)) {
                        $bestNumeric = $g;
                    }

                }
                $label = 'Choice: ' . $it->choiceGroup->subjects->pluck('name')->implode(' / ');
            }

            $ok = $this->satisfiesConstraints($bestNumeric, $it->constraints);
            if ($ok) {
                $met++;
            }

            $explain[] = $ok
            ? "✅ {$kind}: {$label} meets required grade"
            : "⚠️ {$kind}: {$label} does not meet required grade";
        }

        $ratio = $met / max(1, $items->count());
        // If require_all=true for core, gate passes handled in caller. For score we still return ratio.
        return [$ratio == 1.0 || $kind !== 'core', round($ratio, 3), $explain];
    }

    protected function satisfiesConstraints(?int $numeric, $constraints): bool
    {
        if ($numeric === null) {
            return false;
        }

        foreach ($constraints as $c) {
            $min = $c->min_numeric_value ?? 1; // A1 best
            $max = $c->max_numeric_value ?? 9; // F9 worst
            if (! ($numeric >= $min && $numeric <= $max)) {
                return false;
            }

        }
        return true;
    }

    protected function scoreAggregate($programme, array $gradesMap, array $def): array
    {
        $grades = array_values($gradesMap);
        if (count($grades) < 6) {
            return [false, 0.0, ["Aggregate: fewer than 6 graded subjects available"]];
        }
        sort($grades); // lowest numbers = better
        $best6 = array_slice($grades, 0, 6);
        $sum   = array_sum($best6);

        $cut = $programme->aggregate_cutoff ?? null;
        if ($cut === null) {
            return [true, 1.0, ["Aggregate: programme has no cutoff; treated as fit"]];
        }

        $ok = $sum <= $cut;
        // Map to score: perfect (sum==1*6=6) → 1.0, at cutoff → 0.7, +5 over cutoff → 0.2 floor
        $score = $ok ? 1.0 : max(0.2, 1.0 - (($sum - $cut) / 20.0));
        return [$ok, round($score, 3), ["Aggregate: your best six sum {$sum} vs cutoff {$cut}"]];
    }

    protected function scoreInterest($programme, array $snap, array $def): array
    {
        if (! ($snap['top_domain'] ?? null)) {
            return [false, 0.5, ["Interest: no assessment on record"]];
        }

        $pArea = $programme->interestArea?->name;
        if (! $pArea) {
            return [true, 0.8, ["Interest: programme has no area; neutral fit"]];
        }

        $top     = $snap['top_domain'];
        $domains = $snap['interest_domains'] ?? [];

        $match = (strcasecmp($pArea, $top) === 0);
        if ($match) {
            $score = min(1.0, ($domains[$top] ?? 5) / 5.0); // normalize 1..5 -> 0.2..1.0
            return [true, round($score, 3), ["Interest: your top domain '{$top}' matches '{$pArea}'"]];
        }
        // Partial credit if within 80% of top score
        $pScore   = $domains[$pArea] ?? 0;
        $topScore = $domains[$top] ?? 0.0001;
        $ratio    = $pScore / $topScore;
        $score    = max(0.3, min(0.9, $ratio)); // clamp
        return [false, round($score, 3), ["Interest: closest match '{$pArea}' vs your top '{$top}'"]];
    }

    protected function actionPlan($programme, array $gradesMap, bool $coreOk, bool $elecOk, bool $aggOk, bool $intOk): string
    {
        $tips = [];
        if (! $coreOk) {
            $tips[] = "Strengthen core subjects to meet the minimum grades.";
        }

        if (! $elecOk) {
            $tips[] = "Improve at least two elective subjects required by this programme.";
        }

        if (! $aggOk) {
            $tips[] = "Aim to reduce your aggregate by improving weaker grades.";
        }

        if (! $intOk) {
            $tips[] = "Review the programme’s focus to ensure it aligns with your interests.";
        }

        if (! $tips) {
            $tips[] = "Great fit — prepare your documents and apply early.";
        }

        return implode(' ', $tips);
    }
}
