<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterestQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $qs = [
            ['text' => 'I enjoy fixing or building devices.', 'domain' => 'Engineering/Technology', 'weight' => 1],
            ['text' => 'I like experimenting in science labs.', 'domain' => 'Sciences', 'weight' => 1],
            ['text' => 'I’m interested in healthcare and helping patients.', 'domain' => 'Health Sciences', 'weight' => 1],
            ['text' => 'I like analyzing business problems and markets.', 'domain' => 'Business', 'weight' => 1],
            ['text' => 'I enjoy teaching or mentoring others.', 'domain' => 'Education', 'weight' => 1],
            ['text' => 'I love drawing, writing, or performing arts.', 'domain' => 'Arts & Humanities', 'weight' => 1],
            ['text' => 'I’m curious about societies and human behavior.', 'domain' => 'Social Sciences', 'weight' => 1],
        ];
        foreach ($qs as $q) {
            \App\Models\InterestQuestion::firstOrCreate(
                ['text' => $q['text']],
                ['domain' => $q['domain'], 'weight' => $q['weight'], 'active' => true]
            );
        }

    }
}
