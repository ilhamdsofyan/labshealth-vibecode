<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $students = Student::where('name', 'like', "%{$q}%")
            ->orWhere('nis', 'like', "%{$q}%")
            ->orderBy('created_at', 'desc')
            ->with(['activeClass'])
            ->limit(10)
            ->get();

        return response()->json($students->map(function ($s) {
            return [
                'id' => $s->id,
                'text' => "{$s->nis} - {$s->name} ({$s->activeClass?->class_name})",
                'class' => $s->activeClass?->class_name,
                'gender' => $s->gender,
                'avatar' => $s->avatar_path ? asset('storage/' . $s->avatar_path) : null,
            ];
        }));
    }

    public function index(Request $request): View
    {
        $query = Student::with(['activeClass' => function($q) {
            $q->orderBy('class_name');
        }]);

        $classSuggestions = StudentClassHistory::query()
            ->whereNotNull('class_name')
            ->where('class_name', '!=', '')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_name')) {
            $className = $request->class_name;
            $query->whereHas('activeClass', function ($q) use ($className) {
                $q->where('class_name', $className);
            });
        }

        $students = $query
                        ->paginate(15)->withQueryString();

        return view('admin.master.students.index', compact('students', 'classSuggestions'));
    }

    public function show(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson() && ! $request->ajax()) {
            return redirect()->route('admin.master.students.index');
        }

        $student->load([
            'activeClass',
            'health',
            'medicalHistory',
            'previousSchool',
            'learningProfile',
            'homeAssets',
            'familyMembers',
        ]);

        return response()->json($this->formatStudentDetail($student));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.master.students.index');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'unique:students,nis'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'avatar_cropped_data' => ['nullable', 'string'],
        ]);

        $student = Student::create([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
        ]);

        $avatarPath = $this->storeStudentAvatar($request);
        if ($avatarPath) {
            $student->update(['avatar_path' => $avatarPath]);
        }

        $student->classHistories()->create([
            'class_name' => $validated['class_name'],
            'academic_year' => $validated['academic_year'],
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data siswa berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): RedirectResponse
    {
        return redirect()->route('admin.master.students.index');
    }

    public function update(Request $request, Student $student): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'unique:students,nis,' . $student->id],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'avatar_cropped_data' => ['nullable', 'string'],
        ]);

        $student->update([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
        ]);

        $avatarPath = $this->storeStudentAvatar($request, $student->avatar_path);
        if ($avatarPath) {
            $student->update(['avatar_path' => $avatarPath]);
        }

        // If class or academic year changes, create new history and deactivate old ones
        $activeClass = $student->activeClass;
        if (!$activeClass || $activeClass->class_name !== $validated['class_name'] || $activeClass->academic_year !== $validated['academic_year']) {
            $student->classHistories()->update(['is_active' => false]);
            $student->classHistories()->create([
                'class_name' => $validated['class_name'],
                'academic_year' => $validated['academic_year'],
                'is_active' => true,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data siswa berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse|JsonResponse
    {
        if ($student->avatar_path) {
            Storage::disk('public')->delete($student->avatar_path);
        }

        $student->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Data siswa berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    public function removeAvatar(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        if ($student->avatar_path) {
            Storage::disk('public')->delete($student->avatar_path);
            $student->update(['avatar_path' => null]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Foto siswa berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Foto siswa berhasil dihapus.');
    }

    private function storeStudentAvatar(Request $request, ?string $oldPath = null): ?string
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

                $path = 'students/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($path, $binary);

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }

                return $path;
            }
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('students', 'public');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            return $path;
        }

        return null;
    }

    private function formatStudentDetail(Student $student): array
    {
        $student->loadMissing([
            'activeClass',
            'health',
            'medicalHistory',
            'previousSchool',
            'learningProfile',
            'homeAssets',
            'familyMembers',
        ]);

        $familyMembers = $student->familyMembers
            ->sortBy(fn ($member) => sprintf(
                '%d-%s-%s',
                $member->is_guardian ? 0 : 1,
                $member->relation_type ?? '',
                $member->full_name ?? ''
            ))
            ->values()
            ->map(fn ($member) => [
                'relation_type' => $member->relation_type,
                'full_name' => $member->full_name,
                'nik' => $member->nik,
                'birth_year' => $member->birth_year,
                'relationship_detail' => $member->relationship_detail,
                'whatsapp_number' => $member->whatsapp_number,
                'email' => $member->email,
                'religion' => $member->religion,
                'occupation' => $member->occupation,
                'rank_group' => $member->rank_group,
                'position_title' => $member->position_title,
                'education' => $member->education,
                'monthly_income' => $member->monthly_income,
                'special_needs' => $member->special_needs,
                'is_guardian' => $member->is_guardian,
                'is_emergency_contact' => $member->is_emergency_contact,
                'is_primary_contact' => $member->is_primary_contact,
                'lives_with_student' => $member->lives_with_student,
                'marital_status' => $member->marital_status,
                'address_text' => $member->address_text,
                'notes' => $member->notes,
            ]);

        return [
            'id' => $student->id,
            'nis' => $student->nis,
            'name' => $student->name,
            'nickname' => $student->nickname,
            'gender' => $student->gender,
            'class_name' => $student->activeClass?->class_name ?? $student->class_name,
            'academic_year' => $student->activeClass?->academic_year,
            'nisn' => $student->nisn,
            'nik_kitas' => $student->nik_kitas,
            'family_card_number' => $student->family_card_number,
            'birth_place' => $student->birth_place,
            'birth_date' => $student->birth_date?->format('Y-m-d'),
            'birth_certificate_number' => $student->birth_certificate_number,
            'religion' => $student->religion,
            'citizenship' => $student->citizenship,
            'daily_language' => $student->daily_language,
            'whatsapp_number' => $student->whatsapp_number,
            'email' => $student->email,
            'address_text' => $student->address_text,
            'notes' => $student->notes,
            'avatar_url' => $student->avatar_path ? asset('storage/' . $student->avatar_path) : null,
            'health' => $student->health,
            'medical_history' => $student->medicalHistory,
            'previous_school' => $student->previousSchool,
            'learning_profile' => $student->learningProfile,
            'home_assets' => $student->homeAssets,
            'family_members' => $familyMembers,
        ];
    }
}
