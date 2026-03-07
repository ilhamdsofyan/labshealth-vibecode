<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Dashboard ──────────────────────────────────────
        Menu::firstOrCreate(['route_name' => 'dashboard'], [
            'name' => 'Dashboard',
            'route_name' => 'dashboard',
            'icon' => 'bi-speedometer2',
            'permission_name' => 'dashboard',
            'order' => 0,
        ]);

        // ─── Kunjungan (parent) ──────────────────────────────
        $kunjungan = Menu::firstOrCreate(['name' => 'Kunjungan', 'parent_id' => null], [
            'name' => 'Kunjungan',
            'icon' => 'bi-clipboard2-pulse',
            'order' => 1,
        ]);

        Menu::firstOrCreate(['route_name' => 'visits.index'], [
            'parent_id' => $kunjungan->id,
            'name' => 'Data Kunjungan',
            'route_name' => 'visits.index',
            'icon' => 'bi-list-ul',
            'permission_name' => 'visits.index',
            'order' => 0,
        ]);

        Menu::firstOrCreate(['route_name' => 'visits.create'], [
            'parent_id' => $kunjungan->id,
            'name' => 'Tambah Kunjungan',
            'route_name' => 'visits.create',
            'icon' => 'bi-plus-circle',
            'permission_name' => 'visits.create',
            'order' => 1,
        ]);

        // ─── Laporan (parent) ────────────────────────────────
        $laporan = Menu::firstOrCreate(['name' => 'Laporan', 'parent_id' => null], [
            'name' => 'Laporan',
            'icon' => 'bi-bar-chart-line',
            'order' => 2,
        ]);

        Menu::firstOrCreate(['route_name' => 'reports.monthly'], [
            'parent_id' => $laporan->id,
            'name' => 'Kunjungan Bulanan',
            'route_name' => 'reports.monthly',
            'icon' => 'bi-calendar-month',
            'permission_name' => 'reports.monthly',
            'order' => 0,
        ]);

        Menu::firstOrCreate(['route_name' => 'reports.acc-pulang'], [
            'parent_id' => $laporan->id,
            'name' => 'Acc Pulang',
            'route_name' => 'reports.acc-pulang',
            'icon' => 'bi-box-arrow-right',
            'permission_name' => 'reports.acc-pulang',
            'order' => 1,
        ]);

        // ─── Admin (parent) ─────────────────────────────────
        $admin = Menu::firstOrCreate(['name' => 'Admin', 'parent_id' => null], [
            'name' => 'Admin',
            'icon' => 'bi-gear',
            'order' => 3,
        ]);

        Menu::firstOrCreate(['route_name' => 'admin.users.index'], [
            'parent_id' => $admin->id,
            'name' => 'Users',
            'route_name' => 'admin.users.index',
            'icon' => 'bi-people',
            'permission_name' => 'admin.users.index',
            'order' => 0,
        ]);

        Menu::firstOrCreate(['route_name' => 'admin.roles.index'], [
            'parent_id' => $admin->id,
            'name' => 'Roles',
            'route_name' => 'admin.roles.index',
            'icon' => 'bi-shield-lock',
            'permission_name' => 'admin.roles.index',
            'order' => 1,
        ]);

        Menu::firstOrCreate(['route_name' => 'admin.permissions.index'], [
            'parent_id' => $admin->id,
            'name' => 'Permissions',
            'route_name' => 'admin.permissions.index',
            'icon' => 'bi-key',
            'permission_name' => 'admin.permissions.index',
            'order' => 2,
        ]);

        Menu::firstOrCreate(['route_name' => 'admin.menus.index'], [
            'parent_id' => $admin->id,
            'name' => 'Menu',
            'route_name' => 'admin.menus.index',
            'icon' => 'bi-menu-button-wide',
            'permission_name' => 'admin.menus.index',
            'order' => 3,
        ]);
    }
}
