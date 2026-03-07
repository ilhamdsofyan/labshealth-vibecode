<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@uks.local'], [
            'name' => 'Administrator',
            'email' => 'admin@uks.local',
            'password' => 'password',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $superadminRole = Role::where('name', 'superadmin')->first();
        if ($superadminRole && !$admin->roles->contains($superadminRole->id)) {
            $admin->roles()->attach($superadminRole->id);
        }
    }
}
