<?php
namespace Database\Seeders;

use App\Models\Consent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ConsentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            Consent::firstOrCreate(
                ['user_id' => $admin->id, 'policy_version' => '1.0.0'],
                ['granted_at' => Carbon::now()]
            );
        }
    }
}
