<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,             // roles first
            UsersSeeder::class,            // then users (so pivot can attach roles)
            GradeScaleSeeder::class,       // independent of users
            SubjectsSeeder::class,         // independent of users
            SubjectAliasPatchesSeeder::class, // <-- add this

            InterestAreasSeeder::class,    // independent of users
            RequirementFlagsSeeder::class, // independent of users
                                           // Optional (only if you want initial consent rows):
                                           // ConsentSeeder::class,     // must be AFTER users

            InterestQuestionsSeeder::class,
            RuleSetSeeder::class,

        ]);
    }
}
