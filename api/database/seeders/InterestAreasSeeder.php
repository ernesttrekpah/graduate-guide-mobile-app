<?php
namespace Database\Seeders;

use App\Models\InterestArea;
use Illuminate\Database\Seeder;

class InterestAreasSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Engineering/Technology', 'Sciences', 'Health Sciences', 'Business', 'Education', 'Arts & Humanities', 'Social Sciences',
        ] as $name) {
            InterestArea::firstOrCreate(['name' => $name]);
        }
    }
}
