<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;

class MenuService
{
    /**
     * Get menus visible to the given user, filtered by permissions and roles.
     */
    public function getMenusForUser(User $user): Collection
    {
        $userPermissions = $user->getAllPermissions();
        $userRoleIds = $user->roles->pluck('id')->toArray();
        $isSuperAdmin = $user->isSuperAdmin();

        $menus = Menu::active()
            ->roots()
            ->ordered()
            ->with(['roles', 'activeChildren' => function ($query) {
                $query->ordered()->with('roles');
            }])
            ->get();

        return $menus->filter(function ($menu) use ($userPermissions, $userRoleIds, $isSuperAdmin) {
            return $this->isMenuVisible($menu, $userPermissions, $userRoleIds, $isSuperAdmin);
        })->values();
    }

    /**
     * Check if a menu item should be visible to the user.
     */
    protected function isMenuVisible(Menu $menu, Collection $userPermissions, array $userRoleIds, bool $isSuperAdmin): bool
    {
        // SuperAdmin sees everything
        if ($isSuperAdmin) {
            if ($menu->relationLoaded('activeChildren')) {
                $menu->setRelation(
                    'activeChildren',
                    $menu->activeChildren->filter(fn () => true)->values()
                );
            }
            return true;
        }

        // 1. Role Check (Pivot)
        if ($menu->relationLoaded('roles') && $menu->roles->isNotEmpty()) {
            $menuRoleIds = $menu->roles->pluck('id')->toArray();
            if (empty(array_intersect($userRoleIds, $menuRoleIds))) {
                return false;
            }
        }

        // 2. Permission Check
        if ($menu->permission_name && !$userPermissions->contains($menu->permission_name)) {
            return false;
        }

        // 3. Children Filtering
        if ($menu->relationLoaded('activeChildren') && $menu->activeChildren->isNotEmpty()) {
            $visibleChildren = $menu->activeChildren->filter(function ($child) use ($userPermissions, $userRoleIds, $isSuperAdmin) {
                return $this->isMenuVisible($child, $userPermissions, $userRoleIds, $isSuperAdmin);
            })->values();

            $menu->setRelation('activeChildren', $visibleChildren);

            if ($visibleChildren->isEmpty() && !$menu->route_name) {
                return false;
            }
        }

        return true;
    }
}
