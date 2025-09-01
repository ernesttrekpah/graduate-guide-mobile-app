<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChoiceGroup;
use App\Models\ChoiceGroupSubject;
use App\Models\GradeMapping;
use App\Models\Programme;
use App\Models\ProgrammeRequirementItem;
use App\Models\ProgrammeRequirementSet;
use App\Models\RequirementConstraint;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgrammeRequirementAdminController extends Controller
{
    public function show(Request $req, Programme $programme)
    {
        // Ensure sets exist
        $core = ProgrammeRequirementSet::firstOrCreate(['programme_id' => $programme->id, 'kind' => 'core']);
        $elec = ProgrammeRequirementSet::firstOrCreate(['programme_id' => $programme->id, 'kind' => 'elective']);

        $sets = collect([$core, $elec])->map(function ($set) {
            $items = ProgrammeRequirementItem::with([
                'subject:id,name',
                'choiceGroup.subjects:id,name',
                'constraints.scale:id,name',
            ])->where('set_id', $set->id)->get();

            $items = $items->map(function ($i) {
                $constraints = $i->constraints->map(function ($c) {
                    return [
                        'id'                => $c->id,
                        'scale'             => $c->scale ? ['id' => $c->scale->id, 'name' => $c->scale->name] : null,
                        'min_numeric_value' => $c->min_numeric_value,
                        'max_numeric_value' => $c->max_numeric_value,
                        'min_label'         => $this->labelFor($c->scale_id, $c->min_numeric_value),
                        'max_label'         => $this->labelFor($c->scale_id, $c->max_numeric_value),
                        'raw_text'          => $c->raw_text,
                    ];
                })->values();

                return [
                    'id'           => $i->id,
                    'required'     => (bool) $i->required,
                    'subject'      => $i->subject ? ['id' => $i->subject->id, 'name' => $i->subject->name] : null,
                    'choice_group' => $i->choiceGroup ? [
                        'id'           => $i->choiceGroup->id,
                        'min_required' => $i->choiceGroup->min_required,
                        'subjects'     => $i->choiceGroup->subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values(),
                    ] : null,
                    'constraints'  => $constraints,
                ];
            })->values();

            return [
                'id'    => $set->id,
                'kind'  => $set->kind,
                'items' => $items,
            ];
        })->values();

        return response()->json(['data' => [
            'programme' => ['id' => $programme->id, 'name' => $programme->name],
            'sets'      => $sets,
        ]]);
    }

    public function storeItem(Request $req, Programme $programme)
    {
        $data = $req->validate([
            'set'           => ['required'], // number id OR 'core'/'elective'
            'subject_id'    => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'subject_ids'   => ['nullable', 'array'],
            'subject_ids.*' => ['integer', Rule::exists('subjects', 'id')],
            'min_required'  => ['nullable', 'integer', 'min:1'],
            'required'      => ['nullable', 'boolean'],
        ]);

        $set = $this->resolveSet($programme->id, $data['set']);

        if (! empty($data['subject_id'])) {
            // single subject item
            $item = ProgrammeRequirementItem::create([
                'set_id'     => $set->id,
                'subject_id' => $data['subject_id'],
                'required'   => $data['required'] ?? true,
            ]);
            return response()->json(['message' => 'Created', 'data' => ['item_id' => $item->id]], 201);
        }

        if (! empty($data['subject_ids']) && is_array($data['subject_ids'])) {
            // OR group
            $cg   = ChoiceGroup::create(['min_required' => $data['min_required'] ?? 1]);
            $subs = array_values(array_unique($data['subject_ids']));
            foreach ($subs as $sid) {
                ChoiceGroupSubject::firstOrCreate(['choice_group_id' => $cg->id, 'subject_id' => $sid]);
            }
            $item = ProgrammeRequirementItem::create([
                'set_id'          => $set->id,
                'choice_group_id' => $cg->id,
                'required'        => true,
            ]);
            return response()->json(['message' => 'Created', 'data' => ['item_id' => $item->id, 'choice_group_id' => $cg->id]], 201);
        }

        return response()->json(['message' => 'Invalid payload'], 422);
    }

    public function updateItem(Request $req, Programme $programme, ProgrammeRequirementItem $item)
    {
        $this->assertProgrammeItem($programme, $item);
        $data = $req->validate(['required' => ['sometimes', 'boolean']]);
        $item->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroyItem(Request $req, Programme $programme, ProgrammeRequirementItem $item)
    {
        $this->assertProgrammeItem($programme, $item);
        // if a group, delete group & pivot too
        if ($item->choice_group_id) {
            ChoiceGroupSubject::where('choice_group_id', $item->choice_group_id)->delete();
            ChoiceGroup::where('id', $item->choice_group_id)->delete();
        }
        RequirementConstraint::where('item_id', $item->id)->delete();
        $item->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function storeConstraint(Request $req, Programme $programme, ProgrammeRequirementItem $item)
    {
        $this->assertProgrammeItem($programme, $item);
        $data = $req->validate([
            'scale_id'  => ['required', 'integer', Rule::exists('grade_scales', 'id')],
            'min_label' => ['nullable', 'string', 'max:50'],
            'max_label' => ['nullable', 'string', 'max:50'],
            'raw_text'  => ['nullable', 'string', 'max:1000'],
        ]);

        [$minNum, $maxNum] = $this->labelsToNumeric($data['scale_id'], $data['min_label'] ?? null, $data['max_label'] ?? null);

        $rc = RequirementConstraint::create([
            'item_id'           => $item->id,
            'scale_id'          => $data['scale_id'],
            'min_numeric_value' => $minNum,
            'max_numeric_value' => $maxNum,
            'raw_text'          => $data['raw_text'] ?? null,
        ]);

        return response()->json(['message' => 'Created', 'data' => ['id' => $rc->id]], 201);
    }

    public function updateConstraint(Request $req, Programme $programme, RequirementConstraint $constraint)
    {
        $this->assertProgrammeItem($programme, ProgrammeRequirementItem::findOrFail($constraint->item_id));
        $data = $req->validate([
            'scale_id'  => ['nullable', 'integer', Rule::exists('grade_scales', 'id')],
            'min_label' => ['nullable', 'string', 'max:50'],
            'max_label' => ['nullable', 'string', 'max:50'],
            'raw_text'  => ['nullable', 'string', 'max:1000'],
        ]);

        $update = [];
        if (isset($data['scale_id'])) {
            $update['scale_id'] = $data['scale_id'];
        }
        if (array_key_exists('min_label', $data) || array_key_exists('max_label', $data)) {
            $sid                         = $data['scale_id'] ?? $constraint->scale_id;
            [$minNum, $maxNum]           = $this->labelsToNumeric($sid, $data['min_label'] ?? null, $data['max_label'] ?? null);
            $update['min_numeric_value'] = $minNum;
            $update['max_numeric_value'] = $maxNum;
        }
        if (array_key_exists('raw_text', $data)) {
            $update['raw_text'] = $data['raw_text'];
        }
        $constraint->update($update);

        return response()->json(['message' => 'Updated']);
    }

    public function destroyConstraint(Request $req, Programme $programme, RequirementConstraint $constraint)
    {
        $this->assertProgrammeItem($programme, ProgrammeRequirementItem::findOrFail($constraint->item_id));
        $constraint->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function updateChoiceGroup(Request $req, Programme $programme, ChoiceGroup $group)
    {
        $this->assertProgrammeGroup($programme, $group);
        $data = $req->validate(['min_required' => ['sometimes', 'integer', 'min:1']]);
        $group->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function addChoiceGroupSubject(Request $req, Programme $programme, ChoiceGroup $group)
    {
        $this->assertProgrammeGroup($programme, $group);
        $data = $req->validate(['subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')]]);
        ChoiceGroupSubject::firstOrCreate(['choice_group_id' => $group->id, 'subject_id' => $data['subject_id']]);
        return response()->json(['message' => 'Added']);
    }

    public function removeChoiceGroupSubject(Request $req, Programme $programme, ChoiceGroup $group, Subject $subject)
    {
        $this->assertProgrammeGroup($programme, $group);
        ChoiceGroupSubject::where(['choice_group_id' => $group->id, 'subject_id' => $subject->id])->delete();
        // clamp min_required if needed
        $count = ChoiceGroupSubject::where('choice_group_id', $group->id)->count();
        if ($group->min_required > max(1, $count)) {
            $group->update(['min_required' => max(1, $count)]);
        }

        return response()->json(['message' => 'Removed']);
    }

    // --- helpers ---

    protected function resolveSet(int $programmeId, $set)
    {
        if ($set === 'core' || $set === 'elective') {
            return ProgrammeRequirementSet::firstOrCreate(['programme_id' => $programmeId, 'kind' => $set]);
        }
        return ProgrammeRequirementSet::where('programme_id', $programmeId)->where('id', (int) $set)->firstOrFail();
    }

    protected function assertProgrammeItem(Programme $programme, ProgrammeRequirementItem $item): void
    {
        $set = ProgrammeRequirementSet::findOrFail($item->set_id);
        if ((int) $set->programme_id !== (int) $programme->id) {
            abort(403, 'Item does not belong to programme');
        }

    }

    protected function assertProgrammeGroup(Programme $programme, ChoiceGroup $group): void
    {
        $item = ProgrammeRequirementItem::where('choice_group_id', $group->id)->firstOrFail();
        $this->assertProgrammeItem($programme, $item);
    }

    protected function labelFor(?int $scaleId, ?int $num): ?string
    {
        if (! $scaleId || $num === null) {
            return null;
        }

        $map = GradeMapping::where('scale_id', $scaleId)->where('numeric_value', $num)->first();
        return $map?->label;
    }

    protected function labelsToNumeric(int $scaleId, ?string $minLabel, ?string $maxLabel): array
    {
        $min = null;
        $max = null;
        if ($minLabel) {
            $m = GradeMapping::where('scale_id', $scaleId)->where('label', strtoupper(trim($minLabel)))->first();
            if (! $m) {
                abort(422, "Unknown min label for scale");
            }

            $min = (int) $m->numeric_value;
        }
        if ($maxLabel) {
            $m = GradeMapping::where('scale_id', $scaleId)->where('label', strtoupper(trim($maxLabel)))->first();
            if (! $m) {
                abort(422, "Unknown max label for scale");
            }

            $max = (int) $m->numeric_value;
        }
        return [$min, $max];
    }
}
