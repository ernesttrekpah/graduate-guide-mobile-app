<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
                                            // Ensure base roles exist (idempotent safety)
        $roles = Role::pluck('id', 'code'); // ['admin'=>1, 'counsellor'=>2, 'student'=>3]

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@graduateguide.com'],
            ['name' => 'Platform Admin', 'phone' => null, 'password' => Hash::make('12345678')]
        );
        if ($roles->has('admin')) {
            $admin->roles()->syncWithoutDetaching([$roles['admin']]);
        }

        // Counsellor (demo)
        $counsellor = User::firstOrCreate(
            ['email' => 'counsellor@example.com'],
            ['name' => 'Demo Counsellor', 'phone' => null, 'password' => Hash::make('secret12345')]
        );
        if ($roles->has('counsellor')) {
            $counsellor->roles()->syncWithoutDetaching([$roles['counsellor']]);
        }

        // Student (demo)
        $student = User::firstOrCreate(
            ['email' => 'student@example.com'],
            ['name' => 'Demo Student', 'phone' => '0550000000', 'password' => Hash::make('secret12345')]
        );
        if ($roles->has('student')) {
            $student->roles()->syncWithoutDetaching([$roles['student']]);
        }

    }
}
