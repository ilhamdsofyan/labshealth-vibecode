<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Permission::withCount('roles');

        if ($group = $request->input('group')) {
            $query->where('group_name', $group);
        }

        $permissions = $query->orderBy('group_name')->orderBy('display_name')->paginate(30)->withQueryString();
        $groups = Permission::distinct()->pluck('group_name')->filter()->sort()->values();

        return view('admin.permissions.index', compact('permissions', 'groups'));
    }

    public function create(): View
    {
        $groups = Permission::distinct()->pluck('group_name')->filter()->sort()->values();
        return view('admin.permissions.create', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions'],
            'display_name' => ['required', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
        ]);

        Permission::create($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dibuat.');
    }

    public function edit(Permission $permission): View
    {
        $groups = Permission::distinct()->pluck('group_name')->filter()->sort()->values();
        return view('admin.permissions.edit', compact('permission', 'groups'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($permission->id)],
            'display_name' => ['required', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
}
