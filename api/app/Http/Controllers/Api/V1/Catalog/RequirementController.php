<?php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Programme;

class RequirementController extends Controller
{
    public function show(Programme $programme)
    {
        $programme->load([
            'requirementSets.items.subject:id,code,name,group',
            'requirementSets.items.choiceGroup.subjects:id,code,name,group',
            'requirementSets.items.constraints.scale:id,name',
            'jobProspects:id,programme_id,title,description',
            'flags:id,code,label',
        ]);

        // Transform to mobile-friendly structure
        $sets = $programme->requirementSets->map(function ($set) {
            return [
                'kind'  => $set->kind,
                'items' => $set->items->map(function ($it) {
                    return [
                        'type'        => $it->subject_id ? 'subject' : 'choice',
                        'subject'     => $it->subject_id ? [
                            'id' => $it->subject->id, 'code' => $it->subject->code, 'name' => $it->subject->name, 'group' => $it->subject->group,
                        ] : null,
                        'choice'      => $it->choice_group_id ? [
                            'min_required' => $it->choiceGroup->min_required,
                            'subjects'     => $it->choiceGroup->subjects->map(fn($s) => [
                                'id' => $s->id, 'code' => $s->code, 'name' => $s->name, 'group' => $s->group,
                            ]),
                        ] : null,
                        'constraints' => $it->constraints->map(fn($c) => [
                            'scale'       => $c->scale->name,
                            'min_numeric' => $c->min_numeric_value,
                            'max_numeric' => $c->max_numeric_value,
                            'raw'         => $c->raw_text,
                        ]),
                        'required'    => (bool) $it->required,
                        'weight'      => $it->weight,
                    ];
                }),
            ];
        });

        return response()->json([
            'programme'     => [
                'id'               => $programme->id,
                'name'             => $programme->name,
                'course_type'      => $programme->course_type,
                'aggregate_cutoff' => $programme->aggregate_cutoff,
                'flags'            => $programme->flags->map->only(['code', 'label']),
            ],
            'requirements'  => $sets,
            'job_prospects' => $programme->jobProspects->map->only(['title', 'description']),
        ]);
    }
}
