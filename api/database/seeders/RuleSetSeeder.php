<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\{RuleSet, RuleSetVersion, User};

class RuleSetSeeder extends Seeder
{
    public function run(): void
    {
        $rs = RuleSet::firstOrCreate(['name'=>'MVP Scoring']);

        $definition = [
            // How to compute each component deterministically
            'aggregate' => ['method' => 'best6'], // sum 6 lowest numeric grades
            'core'      => ['require_all' => true], // all core items must satisfy constraints
            'elective'  => ['min_electives_matched' => 2], // min elective subjects from set
            'interest'  => ['use_latest_assessment' => true, 'match_if_area_equals_top_domain' => true],
        ];
        $weights = [
            'core_match'       => 0.40,
            'elective_strength'=> 0.25,
            'aggregate_fit'    => 0.20,
            'interest_fit'     => 0.15,
        ];

        $by = User::whereHas('roles', fn($q)=>$q->where('code','admin'))->first();
        $v = RuleSetVersion::updateOrCreate(
            ['rule_set_id'=>$rs->id,'version'=>1],
            [
                'definition_json'=>$definition,
                'weights_json'=>$weights,
                'published_at'=>Carbon::now(),
                'published_by'=>$by?->id,
                'change_note'=>'Initial MVP scoring',
            ]
        );

        // point ruleset to active version
        if ($rs->active_version_id !== $v->id) {
            $rs->active_version_id = $v->id;
            $rs->save();
        }
    }
}
