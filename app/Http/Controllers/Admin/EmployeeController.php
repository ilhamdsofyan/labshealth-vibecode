<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $employees = Employee::where('name', 'like', "%{$q}%")
            ->orWhere('nip', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        return response()->json($employees->map(function ($e) {
            return [
                'id' => $e->id,
                'text' => "{$e->nip} - {$e->name} ({$e->role_type})",
            ];
        }));
    }

    public function index(Request $request): View
    {
        $query = Employee::query();
        $departmentSuggestions = Employee::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('created_at', 'desc')
                        ->paginate(15)->withQueryString();

        return view('admin.master.employees.index', compact('employees', 'departmentSuggestions'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.master.employees.index');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'unique:employees,nip'],
            'name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'in:GURU,KARYAWAN'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        Employee::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data pegawai berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.master.employees.index')
            ->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit(Employee $employee): RedirectResponse
    {
        return redirect()->route('admin.master.employees.index');
    }

    public function update(Request $request, Employee $employee): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'unique:employees,nip,' . $employee->id],
            'name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'in:GURU,KARYAWAN'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $employee->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data pegawai berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.master.employees.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Employee $employee): RedirectResponse|JsonResponse
    {
        $employee->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Data pegawai berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.employees.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }
}
