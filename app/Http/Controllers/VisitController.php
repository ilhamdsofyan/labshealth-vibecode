<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitRequest;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $query = Visit::with(['creator', 'disease', 'medication', 'student', 'employee']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('complaint', 'like', "%{$search}%")
                  ->orWhereHas('disease', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('medication', function($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        $query->filterByDateRange($request->input('date_from'), $request->input('date_to'));
        $query->filterByCategory($request->input('patient_category'));
        
        if ($diseaseId = $request->input('disease_id')) {
            $query->where('disease_id', $diseaseId);
        }

        if ($request->input('is_acc_pulang')) {
            $query->where('is_acc_pulang', true);
        }

        $visits = $query->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->paginate(20)
            ->withQueryString();

        return view('visits.index', compact('visits'));
    }

    public function create(): View
    {
        return view('visits.create');
    }

    public function store(VisitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['visit_type'] = 'kunjungan'; // compatibility
        
        // Snapshot class for students and category-specific cleanups
        if ($data['patient_category'] === 'SMA' && $request->filled('student_id')) {
            $student = \App\Models\Student::find($request->student_id);
            $data['class_at_visit'] = $student->activeClass?->class_name ?? $data['class_or_department'];
            $data['external_patient_name'] = null;
            $data['additional_info'] = null;
            $data['employee_id'] = null;
        } elseif (in_array($data['patient_category'], ['GURU', 'KARYAWAN'])) {
            $data['class_or_department'] = null; // staff doesn't need class_or_department in this context or handled by employee data
            $data['student_id'] = null;
            $data['external_patient_name'] = null;
            $data['additional_info'] = null;
        } elseif ($data['patient_category'] === 'UMUM') {
            $data['student_id'] = null;
            $data['employee_id'] = null;
            $data['patient_name'] = $data['external_patient_name'];
        }

        Visit::create($data);

        return redirect()->route('visits.index')
            ->with('success', 'Data kunjungan berhasil disimpan.');
    }

    public function show(Visit $visit): View
    {
        $visit->load(['creator', 'disease', 'medication', 'student', 'employee']);
        return view('visits.show', compact('visit'));
    }

    public function edit(Visit $visit): View
    {
        $visit->load(['disease', 'medication', 'student', 'employee']);
        return view('visits.edit', compact('visit'));
    }

    public function update(VisitRequest $request, Visit $visit): RedirectResponse
    {
        $data = $request->validated();
        
        // Update logic for category changes
        if ($data['patient_category'] === 'SMA' && $request->filled('student_id')) {
            $student = \App\Models\Student::find($request->student_id);
            $data['class_at_visit'] = $student->activeClass?->class_name ?? $data['class_or_department'];
            $data['external_patient_name'] = null;
            $data['additional_info'] = null;
            $data['employee_id'] = null;
        } elseif (in_array($data['patient_category'], ['GURU', 'KARYAWAN'])) {
            $data['class_or_department'] = null;
            $data['student_id'] = null;
            $data['external_patient_name'] = null;
            $data['additional_info'] = null;
        } elseif ($data['patient_category'] === 'UMUM') {
            $data['student_id'] = null;
            $data['employee_id'] = null;
            $data['class_at_visit'] = null;
            $data['patient_name'] = $data['external_patient_name'];
        }

        $visit->update($data);

        return redirect()->route('visits.index')
            ->with('success', 'Data kunjungan berhasil diperbarui.');
    }

    public function destroy(Visit $visit): RedirectResponse|JsonResponse
    {
        $visit->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Data kunjungan berhasil dihapus.',
            ]);
        }

        return redirect()->route('visits.index')
            ->with('success', 'Data kunjungan berhasil dihapus.');
    }
}
