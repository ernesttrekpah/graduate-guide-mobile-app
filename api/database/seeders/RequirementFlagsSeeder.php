<?php
namespace Database\Seeders;

use App\Models\RequirementFlag;
use Illuminate\Database\Seeder;

class RequirementFlagsSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            ['code' => 'APTITUDE_TEST', 'label' => 'Aptitude test may be required'],
            ['code' => 'INTERVIEW', 'label' => 'Interview may be required'],
            ['code' => 'PORTFOLIO', 'label' => 'Portfolio required'],
            ['code' => 'PHYSICAL_FITNESS', 'label' => 'Physical fitness requirement'],
            ['code' => 'LANGUAGE_TEST', 'label' => 'Language proficiency test'],
            ['code' => 'TECH_APTITUDE', 'label' => 'Technical aptitude test'],
        ];
        foreach ($flags as $f) {
            RequirementFlag::firstOrCreate(['code' => $f['code']], ['label' => $f['label']]);
        }
    }
}
