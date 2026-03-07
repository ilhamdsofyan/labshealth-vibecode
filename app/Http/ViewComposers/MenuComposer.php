<?php

namespace App\Http\ViewComposers;

use App\Services\MenuService;
use Illuminate\View\View;

class MenuComposer
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        if ($user) {
            $menus = $this->menuService->getMenusForUser($user);
        } else {
            $menus = collect();
        }

        $view->with('sidebarMenus', $menus);
    }
}
