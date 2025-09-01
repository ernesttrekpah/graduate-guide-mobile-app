<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['code' => 'admin', 'name' => 'Administrator'],
            ['code' => 'counsellor', 'name' => 'Counsellor'],
            ['code' => 'student', 'name' => 'Student'],
        ] as $r) {
            Role::firstOrCreate(['code' => $r['code']], ['name' => $r['name']]);
        }

    }
}

