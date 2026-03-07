<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $students = Student::where('name', 'like', "%{$q}%")
            ->orWhere('nis', 'like', "%{$q}%")
            ->with(['activeClass'])
            ->limit(10)
            ->get();

        return response()->json($students->map(function ($s) {
            return [
                'id' => $s->id,
                'text' => "{$s->nis} - {$s->name} ({$s->activeClass?->class_name})",
                'class' => $s->activeClass?->class_name,
                'gender' => $s->gender,
            ];
        }));
    }

    public function index(Request $request): View
    {
        $query = Student::with(['activeClass']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(15)->withQueryString();

        return view('admin.master.students.index', compact('students'));
    }

    public function create(): View
    {
        return view('admin.master.students.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'unique:students,nis'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);

        $student = Student::create([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
        ]);

        $student->classHistories()->create([
            'class_name' => $validated['class_name'],
            'academic_year' => $validated['academic_year'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        $student->load('activeClass');
        return view('admin.master.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'unique:students,nis,' . $student->id],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);

        $student->update([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
        ]);

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

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();
        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }
}
