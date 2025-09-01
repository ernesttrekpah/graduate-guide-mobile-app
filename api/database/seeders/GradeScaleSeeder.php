<?php
namespace Database\Seeders;

use App\Models\GradeMapping;
use App\Models\GradeScale;
use Illuminate\Database\Seeder;

class GradeScaleSeeder extends Seeder
{
    public function run(): void
    {
        $scale = GradeScale::firstOrCreate(['name' => 'WASSCE']);
        $map   = ['A1' => 1, 'B2' => 2, 'B3' => 3, 'C4' => 4, 'C5' => 5, 'C6' => 6, 'D7' => 7, 'E8' => 8, 'F9' => 9];
        foreach ($map as $label => $num) {
            GradeMapping::updateOrCreate(['scale_id' => $scale->id, 'label' => $label], ['numeric_value' => $num]);
        }
    }
}
