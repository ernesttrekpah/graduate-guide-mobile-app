<?php
// database/seeders/SubjectAliasPatchesSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Subject, SubjectAlias};

class SubjectAliasPatchesSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            // txt value                  => canonical subject name
            'Statistics'                => 'Elective Mathematics',
            'Mechanics'                 => 'Physics',
            'Operations Research'       => 'Elective Mathematics',
            'Child Psychology'          => 'Social Studies',
            'Psychology'                => 'Social Studies',
            'Safety Management'         => 'Integrated Science',
            'Mathematics Education'     => 'Elective Mathematics',
            // 'Environmental Science'     => 'Integrated Science',
            'Geology'                   => 'Geography',
            // redundancies (safe)
            'Accounting Principles'     => 'Financial Accounting',
            'Business Studies'          => 'Business Management',
            'Political Science'         => 'Government',
            'French Language'           => 'French',
            'Information Technology'    => 'Information and Communication Technology',
            'IT Fundamentals'           => 'Information and Communication Technology',
            'Programming'               => 'Information and Communication Technology',
            'Art'                       => 'General Knowledge in Art',
            'Media Studies'             => 'General Knowledge in Art',
            'Agricultural Science'      => 'Agriculture',
            'Health Science'            => 'Biology',
        ];

        foreach ($map as $alias => $canonical) {
            $subject = Subject::where('name', $canonical)->first();
            if (!$subject) continue; // ensure canonical exists (from SubjectsSeeder)
            SubjectAlias::firstOrCreate([
                'subject_id' => $subject->id,
                'alias'      => $alias,
            ]);
        }
    }
}
