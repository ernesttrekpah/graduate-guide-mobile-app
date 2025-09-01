<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\{Institution,Faculty,Programme,InterestArea,RequirementFlag,JobProspect,
    Subject,SubjectAlias,ProgrammeRequirementSet,ProgrammeRequirementItem,ChoiceGroup,
    RequirementConstraint,GradeScale,GradeMapping};

class CatalogueImport extends Command
{
    protected $signature = 'catalogue:import {path : Path to .txt or .json file}';
    protected $description = 'Import universities, faculties, programmes, requirements, flags, and job prospects';

    protected GradeScale $wassce;
    protected array $gradeLookup = []; // 'A1'=>1, etc.

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!File::exists($path)) {
            $this->error("File not found: $path");
            return self::FAILURE;
        }

        $this->bootGradeLookup();

        $raw = trim(File::get($path));
        $records = $this->parseRecords($raw);
        $this->info("Found ".count($records)." records");

        $ok = $skip = 0;

        foreach ($records as $i => $rec) {
            try {
                $this->importRecord($rec);
                $ok++;
            } catch (\Throwable $e) {
                $skip++;
                $this->warn("Row ".($i+1)." skipped: ".$e->getMessage());
            }
        }

        $this->info("Done. Imported: $ok, Skipped: $skip");
        return self::SUCCESS;
    }

    protected function bootGradeLookup(): void
    {
        $this->wassce = GradeScale::where('name','WASSCE')->firstOrFail();
        $this->gradeLookup = GradeMapping::where('scale_id',$this->wassce->id)
            ->pluck('numeric_value','label')->map(fn($v)=> (int)$v)->toArray();
    }

    /** @return array<int, array> */
    protected function parseRecords(string $raw): array
    {
        // Try JSON array first
        if (Str::startsWith($raw, '[')) {
            $arr = json_decode($raw, true);
            if (is_array($arr)) return $arr;
        }
        // Try line-delimited JSON objects
        $rows = preg_split('/\r?\n/', $raw);
        $out = [];
        foreach ($rows as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $obj = json_decode($line, true);
            if (is_array($obj)) $out[] = $obj;
        }
        return $out;
    }

  protected function importRecord(array $rec): void
{
    // Expected keys (case-insensitive fallback)
    $institutionName = $rec['university'] ?? $rec['institution'] ?? null;
    $facultyName     = $rec['faculty'] ?? null;
    $programmeName   = $rec['programme'] ?? null;
    $courseType      = $rec['course_type'] ?? 'Undergraduate';
    $interestArea    = $rec['interest_area'] ?? null;
    $aggregateCutoff = $rec['aggregate_cut_off'] ?? $rec['aggregate_cutoff'] ?? null;

    // additional_requirements may be string OR array
    $additionalReqRaw = $rec['additional_requirements'] ?? '';
    $additionalReqText = is_array($additionalReqRaw)
        ? implode('; ', array_map(fn($x)=> is_scalar($x)? (string)$x : json_encode($x), $additionalReqRaw))
        : (string)$additionalReqRaw;

    $jobProspects    = $rec['job_prospects'] ?? [];

    if (!$institutionName || !$facultyName || !$programmeName) {
        throw new \RuntimeException('Missing university/faculty/programme');
    }

    // Upsert institution, faculty, interest area, programme
    $institution = Institution::firstOrCreate(['name' => trim((string)$institutionName)]);
    $faculty = Faculty::firstOrCreate(['institution_id' => $institution->id, 'name' => trim((string)$facultyName)]);
    $ia = $interestArea ? InterestArea::firstOrCreate(['name'=>trim((string)$interestArea)]) : null;

    // Sanitize aggregate cutoff (accept numeric strings, else null)
    $agg = null;
    if (is_numeric($aggregateCutoff)) {
        $agg = (int)$aggregateCutoff;
    } elseif (is_string($aggregateCutoff)) {
        $digits = preg_replace('/\D+/', '', $aggregateCutoff);
        $agg = ($digits !== '') ? (int)$digits : null;
    }

    $programme = Programme::updateOrCreate(
        ['faculty_id'=>$faculty->id, 'name'=>trim((string)$programmeName)],
        [
            'interest_area_id'=> $ia?->id,
            'course_type' => (string)$courseType,
            'aggregate_cutoff' => $agg,
            'additional_requirements_text' => $additionalReqText ?: null,
        ]
    );

    // Flags from additional requirements (string only)
    $this->attachFlags($programme, $additionalReqText);

    // Requirement sets (create or fetch)
    $coreSet = ProgrammeRequirementSet::firstOrCreate(['programme_id'=>$programme->id,'kind'=>'core']);
    $elecSet = ProgrammeRequirementSet::firstOrCreate(['programme_id'=>$programme->id,'kind'=>'elective']);

    // Parse subjects (can be string, array of strings, or structured arrays)
    $coreVal = $rec['core_subjects'] ?? $rec['core'] ?? ($rec['core_requirements'] ?? []);
    $elecVal = $rec['elective_subjects'] ?? $rec['electives'] ?? ($rec['elective_requirements'] ?? []);

    $this->parseRequirements($coreSet, $coreVal);
    $this->parseRequirements($elecSet, $elecVal);

    // Special pattern: "Minimum B3 in Elective Mathematics" (text may be array -> handled above)
    $this->applyMinimumInSubject($elecSet, $additionalReqText);

    // Job prospects (wipe & replace to keep idempotent)
    $programme->jobProspects()->delete();
    foreach ((array)$jobProspects as $jp) {
        if (is_string($jp)) {
            $programme->jobProspects()->create(['title'=>trim($jp)]);
        } elseif (is_array($jp) && !empty($jp['title'])) {
            $programme->jobProspects()->create([
                'title'=>trim((string)$jp['title']),
                'description'=> isset($jp['description']) ? (string)$jp['description'] : null
            ]);
        }
    }
}

    protected function attachFlags(Programme $programme, string $text): void
    {
        $map = [
            'aptitude' => 'APTITUDE_TEST',
            'interview'=> 'INTERVIEW',
            'portfolio'=> 'PORTFOLIO',
            'physical' => 'PHYSICAL_FITNESS',
            'language' => 'LANGUAGE_TEST',
            'technical aptitude' => 'TECH_APTITUDE',
        ];
        $attach = [];
        $lower = Str::lower($text);
        foreach ($map as $needle => $code) {
            if (Str::contains($lower, $needle)) {
                if ($flag = RequirementFlag::where('code',$code)->first()) $attach[] = $flag->id;
            }
        }
        if ($attach) $programme->flags()->syncWithoutDetaching($attach);
    }

/**
 * Accepts:
 * - string: "English (A1-C6), Core Mathematics (A1-C6), Integrated Science/Social Studies (A1-C6)"
 * - array of strings: ["English (A1-C6)", "Core Mathematics (A1-C6)", "Integrated Science/Social Studies (A1-C6)"]
 * - array of objects:
 *    { "subject":"English", "grade":"A1-C6" }
 *    { "or":["Integrated Science","Social Studies"], "constraint":"A1-C6" }
 *    { "subjects":["Physics","Chemistry"], "constraint":"A1-C6", "min_required":1 }
 */
protected function parseRequirements(ProgrammeRequirementSet $set, $value): void
{
    // Clear existing items to keep import idempotent
    $set->items()->delete();

    // If it's an array, handle each entry
    if (is_array($value)) {
        foreach ($value as $entry) {
            if (is_string($entry)) {
                $this->parseRequirementToken($set, $entry);
            } elseif (is_array($entry)) {
                // Choice groups via 'or' or 'subjects'
                if (isset($entry['or']) && is_array($entry['or'])) {
                    $subjects = array_map('strval', $entry['or']);
                    $constraint = (string)($entry['constraint'] ?? $entry['grade'] ?? '');
                    $cg = ChoiceGroup::create(['min_required'=>1]);
                    foreach ($subjects as $subName) {
                        $subject = $this->resolveSubject($subName);
                        $cg->subjects()->syncWithoutDetaching([$subject->id]);
                    }
                    $item = ProgrammeRequirementItem::create([
                        'set_id'=>$set->id,'choice_group_id'=>$cg->id,'required'=>true
                    ]);
                    if ($constraint) $this->applyConstraint($item, $constraint);
                } elseif (isset($entry['subjects']) && is_array($entry['subjects'])) {
                    $subjects = array_map('strval', $entry['subjects']);
                    $min = (int)($entry['min_required'] ?? $entry['minimum'] ?? 1);
                    $constraint = (string)($entry['constraint'] ?? $entry['grade'] ?? '');
                    $cg = ChoiceGroup::create(['min_required'=> max(1,$min) ]);
                    foreach ($subjects as $subName) {
                        $subject = $this->resolveSubject($subName);
                        $cg->subjects()->syncWithoutDetaching([$subject->id]);
                    }
                    $item = ProgrammeRequirementItem::create([
                        'set_id'=>$set->id,'choice_group_id'=>$cg->id,'required'=>true
                    ]);
                    if ($constraint) $this->applyConstraint($item, $constraint);
                } else {
                    // Single subject object {subject/name, grade/constraint}
                    $label = (string)($entry['subject'] ?? $entry['name'] ?? '');
                    $constraint = (string)($entry['grade'] ?? $entry['constraint'] ?? '');
                    if ($label === '') continue;
                    $subject = $this->resolveSubject($label);
                    $item = ProgrammeRequirementItem::create([
                        'set_id'=>$set->id,'subject_id'=>$subject->id,'required'=>true
                    ]);
                    if ($constraint) $this->applyConstraint($item, $constraint);
                }
            }
        }
        return;
    }

    // Else treat as string
    $str = trim((string)$value);
    if ($str === '') return;
    $str = str_replace(['–','—'], '-', $str);
    $parts = array_filter(array_map('trim', preg_split('/\s*,\s*/', $str)));
    foreach ($parts as $p) $this->parseRequirementToken($set, $p);
}

protected function parseRequirementToken(ProgrammeRequirementSet $set, string $p): void
{
    if ($p === '') return;

    // Extract "label (constraint)" if present
    if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/u', $p, $m)) {
        $label = trim($m[1]);
        $constraint = trim($m[2]);
    } else {
        $label = trim($p);
        $constraint = null;
    }

    // OR-choice "X/Y/Z"
    if (str_contains($label, '/')) {
        $subs = array_map('trim', explode('/', $label));
        $cg = ChoiceGroup::create(['min_required'=>1]);
        foreach ($subs as $subName) {
            $subject = $this->resolveSubject($subName);
            $cg->subjects()->syncWithoutDetaching([$subject->id]);
        }
        $item = ProgrammeRequirementItem::create([
            'set_id'=>$set->id,'choice_group_id'=>$cg->id,'required'=>true
        ]);
        if ($constraint) $this->applyConstraint($item, $constraint);
    } else {
        // Single subject
        $subject = $this->resolveSubject($label);
        $item = ProgrammeRequirementItem::create([
            'set_id'=>$set->id,'subject_id'=>$subject->id,'required'=>true
        ]);
        if ($constraint) $this->applyConstraint($item, $constraint);
    }
}


    protected function applyMinimumInSubject(ProgrammeRequirementSet $set, string $text): void
    {
        // e.g., "Minimum B3 in Elective Mathematics"
        if (preg_match('/Minimum\s+([A-F][1-9])\s+in\s+(.+?)$/i', $text, $m)) {
            $grade = strtoupper($m[1]);
            $subjectName = trim($m[2]);
            $subject = $this->resolveSubject($subjectName);

            // Find or create an item for this subject within the set
            $item = $set->items()->where('subject_id', $subject->id)->first();
            if (!$item) {
                $item = ProgrammeRequirementItem::create([
                    'set_id'=>$set->id,'subject_id'=>$subject->id,'required'=>true
                ]);
            }
            $this->applyConstraint($item, "Minimum $grade");
        }
    }

    protected function applyConstraint(ProgrammeRequirementItem $item, string $raw): void
    {
        $raw = trim(str_replace(['–','—'],'-',$raw));

        $min = null; $max = null;

        // Range: "A1-C6"
        if (preg_match('/^([A-F][1-9])\s*-\s*([A-F][1-9])$/i', $raw, $m)) {
            $min = $this->num($m[1]);  // A1 -> 1
            $max = $this->num($m[2]);  // C6 -> 6
        }
        // "Minimum B3"
        elseif (preg_match('/^Minimum\s+([A-F][1-9])$/i', $raw, $m)) {
            $min = 1;
            $max = $this->num($m[1]);
        }

        RequirementConstraint::create([
            'item_id' => $item->id,
            'scale_id'=> $this->wassce->id,
            'min_numeric_value' => $min,
            'max_numeric_value' => $max,
            'raw_text' => $raw,
        ]);
    }

 protected function resolveSubject(string $name): Subject
{
    $name = trim($name);

    // 1) Exact name
    if ($s = Subject::where('name',$name)->first()) return $s;

    // 2) Exact alias
    if ($a = SubjectAlias::where('alias',$name)->first()) return $a->subject;

    // 3) Case-insensitive alias
    if ($a = SubjectAlias::whereRaw('LOWER(alias) = ?', [strtolower($name)])->first()) return $a->subject;

    // 4) Light normalizations: drop trailing " Language" / " Education"
    $mutations = [
        preg_replace('/\s+Language$/i', '', $name),
        preg_replace('/\s+Education$/i', '', $name),
    ];
    foreach ($mutations as $m) {
        if ($m && ($s = Subject::where('name',$m)->first())) return $s;
        if ($m && ($a = SubjectAlias::whereRaw('LOWER(alias) = ?', [strtolower($m)])->first())) return $a->subject;
    }

    throw new \RuntimeException("Unknown subject: $name");
}


    protected function num(string $label): int
    {
        $label = strtoupper(trim($label));
        if (!isset($this->gradeLookup[$label])) {
            throw new \RuntimeException("Unknown grade label: $label");
        }
        return (int)$this->gradeLookup[$label];
    }
}
