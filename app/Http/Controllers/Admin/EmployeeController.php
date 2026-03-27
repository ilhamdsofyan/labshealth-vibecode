<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $employees = Employee::query()
            ->select(['id', 'nip', 'name', 'role_type', 'department', 'avatar_path'])
            ->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get();

        return response()->json($employees->map(function ($e) {
            return [
                'id' => $e->id,
                'text' => "{$e->nip} - {$e->name} ({$e->role_type})",
                'name' => $e->name,
                'role_type' => $e->role_type,
                'department' => $e->department,
                'avatar' => $e->avatar_path ? asset('storage/' . $e->avatar_path) : null,
            ];
        }));
    }

    public function show(Request $request, Employee $employee): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson() && ! $request->ajax()) {
            return redirect()->route('admin.master.employees.index');
        }

        $employee->loadMissing('medicalRecord');

        return response()->json([
            'id' => $employee->id,
            'nip' => $employee->nip,
            'name' => $employee->name,
            'role_type' => $employee->role_type,
            'department' => $employee->department,
            'avatar_url' => $employee->avatar_path ? asset('storage/' . $employee->avatar_path) : null,
            'medical_record' => [
                'height_cm' => $employee->medicalRecord?->height_cm,
                'weight_kg' => $employee->medicalRecord?->weight_kg,
                'blood_type' => $employee->medicalRecord?->blood_type,
                'rhesus' => $employee->medicalRecord?->rhesus,
                'allergies' => $employee->medicalRecord?->allergies,
                'chronic_diseases' => $employee->medicalRecord?->chronic_diseases,
                'past_surgeries' => $employee->medicalRecord?->past_surgeries,
                'regular_medications' => $employee->medicalRecord?->regular_medications,
                'last_checkup_date' => $employee->medicalRecord?->last_checkup_date?->format('Y-m-d'),
                'medical_notes' => $employee->medicalRecord?->medical_notes,
            ],
        ]);
    }

    public function index(Request $request): View
    {
        $query = Employee::query()
            ->select(['id', 'nip', 'name', 'role_type', 'department', 'avatar_path', 'created_at']);
        $departmentSuggestions = Employee::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
        $roleSuggestions = Employee::query()
            ->whereNotNull('role_type')
            ->where('role_type', '!=', '')
            ->distinct()
            ->orderBy('role_type')
            ->pluck('role_type');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_type')) {
            $query->where('role_type', $request->role_type);
        }

        $employees = $query->orderBy('created_at', 'desc')
                        ->paginate(15)->withQueryString();

        return view('admin.master.employees.index', compact('employees', 'departmentSuggestions', 'roleSuggestions'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.master.employees.index');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:30', 'unique:employees,nip'],
            'name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'in:GURU,KARYAWAN,TENDIK,PETUGAS'],
            'department' => ['nullable', 'string', 'max:120'],
            'height_cm' => ['nullable', 'integer', 'min:0', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'blood_type' => ['nullable', 'in:A,B,AB,O'],
            'rhesus' => ['nullable', 'in:+,-'],
            'allergies' => ['nullable', 'string'],
            'chronic_diseases' => ['nullable', 'string'],
            'past_surgeries' => ['nullable', 'string'],
            'regular_medications' => ['nullable', 'string'],
            'last_checkup_date' => ['nullable', 'date'],
            'medical_notes' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'avatar_cropped_data' => ['nullable', 'string'],
        ]);

        $employee = Employee::create([
            'nip' => $validated['nip'],
            'name' => $validated['name'],
            'role_type' => $validated['role_type'],
            'department' => $validated['department'] ?? null,
        ]);

        $avatarPath = $this->storeEmployeeAvatar($request);
        if ($avatarPath) {
            $employee->update(['avatar_path' => $avatarPath]);
        }

        $this->syncEmployeeMedicalRecord($employee, $validated);

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
            'nip' => ['required', 'string', 'max:30', 'unique:employees,nip,' . $employee->id],
            'name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'in:GURU,KARYAWAN,TENDIK,PETUGAS'],
            'department' => ['nullable', 'string', 'max:120'],
            'height_cm' => ['nullable', 'integer', 'min:0', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'blood_type' => ['nullable', 'in:A,B,AB,O'],
            'rhesus' => ['nullable', 'in:+,-'],
            'allergies' => ['nullable', 'string'],
            'chronic_diseases' => ['nullable', 'string'],
            'past_surgeries' => ['nullable', 'string'],
            'regular_medications' => ['nullable', 'string'],
            'last_checkup_date' => ['nullable', 'date'],
            'medical_notes' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'avatar_cropped_data' => ['nullable', 'string'],
        ]);

        $employee->update([
            'nip' => $validated['nip'],
            'name' => $validated['name'],
            'role_type' => $validated['role_type'],
            'department' => $validated['department'] ?? null,
        ]);

        $avatarPath = $this->storeEmployeeAvatar($request, $employee->avatar_path);
        if ($avatarPath) {
            $employee->update(['avatar_path' => $avatarPath]);
        }

        $this->syncEmployeeMedicalRecord($employee, $validated);

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
        if ($employee->avatar_path) {
            Storage::disk('public')->delete($employee->avatar_path);
        }

        $employee->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Data pegawai berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.employees.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function removeAvatar(Request $request, Employee $employee): JsonResponse|RedirectResponse
    {
        if ($employee->avatar_path) {
            Storage::disk('public')->delete($employee->avatar_path);
            $employee->update(['avatar_path' => null]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Foto pegawai berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.employees.index')
            ->with('success', 'Foto pegawai berhasil dihapus.');
    }

    private function storeEmployeeAvatar(Request $request, ?string $oldPath = null): ?string
    {
        if ($request->filled('avatar_cropped_data')) {
            $data = $request->input('avatar_cropped_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
                $extension = strtolower($matches[1]);
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                if (!in_array($extension, ['jpg', 'png', 'webp'], true)) {
                    return null;
                }

                $binary = base64_decode(substr($data, strpos($data, ',') + 1), true);
                if ($binary === false) {
                    return null;
                }

                $path = 'employees/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($path, $binary);

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }

                return $path;
            }
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('employees', 'public');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            return $path;
        }

        return null;
    }

    private function syncEmployeeMedicalRecord(Employee $employee, array $validated): void
    {
        $attributes = array_filter([
            'height_cm' => $validated['height_cm'] ?? null,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'blood_type' => $validated['blood_type'] ?? null,
            'rhesus' => $validated['rhesus'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'chronic_diseases' => $validated['chronic_diseases'] ?? null,
            'past_surgeries' => $validated['past_surgeries'] ?? null,
            'regular_medications' => $validated['regular_medications'] ?? null,
            'last_checkup_date' => $validated['last_checkup_date'] ?? null,
            'medical_notes' => $validated['medical_notes'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        if (empty($attributes)) {
            return;
        }

        $employee->medicalRecord()->updateOrCreate(
            ['employee_id' => $employee->id],
            $attributes
        );
    }
}
