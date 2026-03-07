<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Admin',
                'description' => 'Akses penuh ke seluruh sistem',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Mengelola pengguna dan konfigurasi',
            ],
            [
                'name' => 'petugas_uks',
                'display_name' => 'Petugas UKS',
                'description' => 'Mencatat dan mengelola kunjungan UKS',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
