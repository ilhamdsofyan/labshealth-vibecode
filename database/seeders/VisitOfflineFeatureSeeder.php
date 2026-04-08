<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class VisitOfflineFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'visits.offline-sync'],
            [
                'display_name' => 'Visits Offline Sync',
                'group_name' => 'visits',
            ]
        );

        Role::query()
            ->whereIn('name', ['admin', 'petugas_uks'])
            ->get()
            ->each(function (Role $role) use ($permission): void {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            });
    }
}
