<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class SyncPermissions extends Command
{
    protected $signature = 'permission:sync';

    protected $description = 'Sync permissions from registered named routes';

    public function handle(): int
    {
        $routes = Route::getRoutes();
        $created = 0;
        $existing = 0;

        foreach ($routes as $route) {
            $name = $route->getName();

            // Skip routes without names
            if (!$name) {
                continue;
            }

            // Skip vendor/internal routes
            $skipPrefixes = ['sanctum.', 'ignition.', 'debugbar.', 'horizon.', 'telescope.'];
            $shouldSkip = false;
            foreach ($skipPrefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $shouldSkip = true;
                    break;
                }
            }
            if ($shouldSkip) {
                continue;
            }

            // Skip auth-related routes that don't need permissions
            $skipExact = ['login', 'login.post', 'logout', 'auth.google', 'auth.google.callback'];
            if (in_array($name, $skipExact)) {
                continue;
            }

            // Derive group name from route name prefix (e.g., "visits.index" → "visits")
            $parts = explode('.', $name);
            $groupName = count($parts) > 1 ? $parts[0] : $name;

            // Make display name human readable
            $displayName = str_replace(['.', '_'], ' ', $name);
            $displayName = ucwords($displayName);

            $permission = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $displayName,
                    'group_name' => $groupName,
                ]
            );

            if ($permission->wasRecentlyCreated) {
                $created++;
                $this->line("  <info>Created:</info> {$name}");
            } else {
                $existing++;
            }
        }

        $this->newLine();
        $this->info("Permission sync complete!");
        $this->info("  Created: {$created}");
        $this->info("  Existing: {$existing}");
        $this->info("  Total: " . ($created + $existing));

        return Command::SUCCESS;
    }
}
