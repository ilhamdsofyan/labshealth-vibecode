<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SettingsFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'settings.index',
            'settings.update',
            'settings.maintenance',
        ])->map(function (string $name) {
            return Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => ucwords(str_replace(['.', '_'], ' ', $name)),
                    'group_name' => 'settings',
                ]
            );
        });

        $adminRole = Role::query()->where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($permissions->pluck('id')->all());
        }

        $settingsMenu = Menu::query()->where('route_name', 'settings.index')->first();
        if ($settingsMenu && $adminRole) {
            $settingsMenu->roles()->syncWithoutDetaching([$adminRole->id]);
        }
    }
}
