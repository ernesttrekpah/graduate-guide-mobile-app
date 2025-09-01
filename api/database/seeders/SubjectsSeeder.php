<?php
namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SubjectAlias;
use Illuminate\Database\Seeder;

class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Core
            ['code' => 'ENG',        'name' => 'English',                    'group' => 'Core',     'aliases' => ['English Language']],
            ['code' => 'CORE_MATH',  'name' => 'Core Mathematics',           'group' => 'Core',     'aliases' => ['Mathematics', 'Maths (Core)']],
            ['code' => 'INT_SCI',    'name' => 'Integrated Science',         'group' => 'Core',     'aliases' => ['Integrated Sci']],
            ['code' => 'SOC_STUD',   'name' => 'Social Studies',             'group' => 'Core',     'aliases' => ['Social Studies (Core)']],

            // Science electives
            ['code' => 'ELT_MATH',   'name' => 'Elective Mathematics',       'group' => 'Elective', 'aliases' => ['Further Mathematics', 'Maths (Elective)', 'Statistics', 'Operations Research']],
            ['code' => 'PHYS',       'name' => 'Physics',                    'group' => 'Elective', 'aliases' => ['Mechanics']],
            ['code' => 'CHEM',       'name' => 'Chemistry',                  'group' => 'Elective', 'aliases' => []],
            ['code' => 'BIO',        'name' => 'Biology',                    'group' => 'Elective', 'aliases' => ['Health Science']],
            ['code' => 'ENV_SCI',    'name' => 'Environmental Science',      'group' => 'Elective', 'aliases' => []],
            ['code' => 'AGRIC',      'name' => 'Agriculture',                'group' => 'Elective', 'aliases' => ['Agricultural Science']],
            ['code' => 'GEO',        'name' => 'Geography',                  'group' => 'Elective', 'aliases' => []],
            ['code' => 'PE',         'name' => 'Physical Education',         'group' => 'Elective', 'aliases' => []],
            ['code' => 'GEOLOGY',    'name' => 'Geology',                    'group' => 'Elective', 'aliases' => []], // if your data truly lists Geology separately

            // Business / Social Science electives
            ['code' => 'ECON',       'name' => 'Economics',                  'group' => 'Elective', 'aliases' => []],
            ['code' => 'GOVT',       'name' => 'Government',                 'group' => 'Elective', 'aliases' => ['Political Science']],
            ['code' => 'HIST',       'name' => 'History',                    'group' => 'Elective', 'aliases' => []],
            ['code' => 'ACCT',       'name' => 'Financial Accounting',       'group' => 'Elective', 'aliases' => ['Accounting', 'Accounting Principles']],
            ['code' => 'COST_ACCT',  'name' => 'Principles of Cost Accounting','group'=>'Elective','aliases' => []],
            ['code' => 'BUS_MGMT',   'name' => 'Business Management',        'group' => 'Elective', 'aliases' => ['Business Studies', 'Management']],
            ['code' => 'PSY',        'name' => 'Psychology',                 'group' => 'Elective', 'aliases' => ['Child Psychology']], // maps psych requirements

            // Languages / Arts / ICT electives
            ['code' => 'LIT_ENG',    'name' => 'Literature in English',      'group' => 'Elective', 'aliases' => ['Literature']],
            ['code' => 'FRENCH',     'name' => 'French',                     'group' => 'Elective', 'aliases' => ['French Language']],
            ['code' => 'ICT',        'name' => 'Information and Communication Technology', 'group' => 'Elective', 'aliases' => ['Information Technology', 'IT Fundamentals', 'Programming', 'IT']],
            ['code' => 'GKA',        'name' => 'General Knowledge in Art',   'group' => 'Elective', 'aliases' => ['Art', 'Media Studies']],
        ];

        foreach ($subjects as $s) {
            $sub = Subject::firstOrCreate(
                ['code' => $s['code']],
                ['name' => $s['name'], 'group' => $s['group'] ?? null]
            );

            // If the subject exists but the name/group changed, keep DB consistent (optional tidy-up)
            if ($sub->wasRecentlyCreated === false) {
                $needsUpdate = false;
                $updates = [];
                if ($sub->name !== $s['name']) { $updates['name'] = $s['name']; $needsUpdate = true; }
                if (($sub->group ?? null) !== ($s['group'] ?? null)) { $updates['group'] = $s['group'] ?? null; $needsUpdate = true; }
                if ($needsUpdate) $sub->update($updates);
            }

            foreach ($s['aliases'] as $alias) {
                SubjectAlias::firstOrCreate(['subject_id' => $sub->id, 'alias' => $alias]);
            }
        }
    }
}
