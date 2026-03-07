<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::with(['activeChildren', 'roles'])
            ->roots()
            ->ordered()
            ->get();

        return view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        $parentMenus = Menu::roots()->ordered()->get();
        $roles = \App\Models\Role::all();
        return view('admin.menus.create', compact('parentMenus', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'permission_name' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $menu = Menu::create($validated);
        
        if ($request->has('role_ids')) {
            $menu->roles()->sync($request->role_ids);
        }

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu berhasil dibuat.');
    }

    public function edit(Menu $menu): View
    {
        $parentMenus = Menu::roots()
            ->where('id', '!=', $menu->id)
            ->ordered()
            ->get();
        $roles = \App\Models\Role::all();
        $menu->load('roles');

        return view('admin.menus.edit', compact('menu', 'parentMenus', 'roles'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'permission_name' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (isset($validated['parent_id']) && $validated['parent_id'] == $menu->id) {
            return back()->with('error', 'Menu tidak dapat menjadi parent dari dirinya sendiri.');
        }

        $menu->update($validated);
        $menu->roles()->sync($request->role_ids ?? []);

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        // Move children to root level before deleting
        $menu->children()->update(['parent_id' => null]);
        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->input('data');
        if (!$data) {
            return back()->with('error', 'Data urutan tidak valid.');
        }

        $this->updateMenuOrder(json_decode($data, true));

        return back()->with('success', 'Urutan menu berhasil diperbarui.');
    }

    private function updateMenuOrder($items, $parentId = null)
    {
        foreach ($items as $index => $item) {
            $menu = Menu::find($item['id']);
            if ($menu) {
                $menu->update([
                    'parent_id' => $parentId,
                    'order' => $index + 1
                ]);

                if (isset($item['children']) && !empty($item['children'])) {
                    $this->updateMenuOrder($item['children'], $menu->id);
                }
            }
        }
    }
}
