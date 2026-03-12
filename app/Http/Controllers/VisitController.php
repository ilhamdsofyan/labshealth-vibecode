<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitRequest;
use App\Models\Bed;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $query = Visit::with(['creator', 'disease', 'medication', 'diseases', 'medications', 'student', 'employee', 'bed']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('complaint', 'like', "%{$search}%")
                  ->orWhereHas('diseases', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('medications', function($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        $query->filterByDateRange($request->input('date_from'), $request->input('date_to'));
        $query->filterByCategory($request->input('patient_category'));
        
        if ($diseaseId = $request->input('disease_id')) {
            $query->whereHas('diseases', function ($dq) use ($diseaseId) {
                $dq->where('diseases.id', $diseaseId);
            });
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
        $diseaseIds = array_values(array_unique(array_map('intval', $data['disease_ids'] ?? [])));
        $medicationIds = array_values(array_unique(array_map('intval', $data['medication_ids'] ?? [])));
        unset($data['disease_ids'], $data['medication_ids']);

        $data['disease_id'] = $diseaseIds[0] ?? null;
        $data['medication_id'] = $medicationIds[0] ?? null;
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

        $visit = Visit::create($data);
        $visit->diseases()->sync($diseaseIds);
        $visit->medications()->sync($medicationIds);

        return redirect()->route('visits.index')
            ->with('success', 'Data kunjungan berhasil disimpan.');
    }

    public function show(Visit $visit): View
    {
        $visit->load(['creator', 'disease', 'medication', 'diseases', 'medications', 'student', 'employee']);
        return view('visits.show', compact('visit'));
    }

    public function edit(Visit $visit): View
    {
        $visit->load(['disease', 'medication', 'diseases', 'medications', 'student', 'employee']);
        return view('visits.edit', compact('visit'));
    }

    public function update(VisitRequest $request, Visit $visit): RedirectResponse
    {
        $data = $request->validated();
        $diseaseIds = array_values(array_unique(array_map('intval', $data['disease_ids'] ?? [])));
        $medicationIds = array_values(array_unique(array_map('intval', $data['medication_ids'] ?? [])));
        unset($data['disease_ids'], $data['medication_ids']);

        $data['disease_id'] = $diseaseIds[0] ?? null;
        $data['medication_id'] = $medicationIds[0] ?? null;
        
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
        $visit->diseases()->sync($diseaseIds);
        $visit->medications()->sync($medicationIds);

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

    public function toggleRest(Request $request, Visit $visit): RedirectResponse|JsonResponse
    {
        if (!$visit->visit_date || !$visit->visit_date->isToday()) {
            $message = 'Status rest hanya bisa diubah pada hari kunjungan (hari ini).';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->route('visits.index')->with('error', $message);
        }

        $isRest = $request->boolean('is_rest');

        if ($isRest) {
            $availableBed = null;
            if ($visit->bed_id) {
                $availableBed = Bed::find($visit->bed_id);
            }

            if (!$availableBed) {
                $occupiedBedIds = Visit::query()
                    ->where('is_rest', true)
                    ->where('is_acc_pulang', false)
                    ->whereNotNull('bed_id')
                    ->where('id', '!=', $visit->id)
                    ->pluck('bed_id');

                $availableBed = Bed::query()
                    ->where('is_active', true)
                    ->whereNotIn('id', $occupiedBedIds)
                    ->orderBy('id')
                    ->first();
            }

            if (!$availableBed) {
                $victimVisit = Visit::query()
                    ->where('is_rest', true)
                    ->where('is_acc_pulang', false)
                    ->whereNotNull('bed_id')
                    ->where('id', '!=', $visit->id)
                    ->orderBy('updated_at')
                    ->first();

                if ($victimVisit) {
                    $availableBed = Bed::find($victimVisit->bed_id);
                    $victimVisit->update([
                        'is_rest' => false,
                        'bed_id' => null,
                    ]);
                }
            }

            if (!$availableBed) {
                $message = 'Tidak ada bed aktif yang tersedia.';
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }
                return redirect()->route('visits.index')->with('error', $message);
            }

            $visit->update([
                'is_rest' => true,
                'is_acc_pulang' => false,
                'bed_id' => $availableBed->id,
            ]);

            $message = 'Status rest diaktifkan dan pasien ditempatkan di ' . ($availableBed->name ?: $availableBed->code) . '.';
        } else {
            $visit->update([
                'is_rest' => false,
                'bed_id' => null,
            ]);

            $message = 'Status rest dinonaktifkan dan bed dilepas.';
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('visits.index')->with('success', $message);
    }

    public function togglePulang(Request $request, Visit $visit): RedirectResponse|JsonResponse
    {
        $isPulang = $request->boolean('is_acc_pulang');

        if ($isPulang) {
            $visit->update([
                'is_acc_pulang' => true,
                'is_rest' => false,
                'bed_id' => null,
            ]);
            $message = 'Status pulang diaktifkan.';
        } else {
            $visit->update([
                'is_acc_pulang' => false,
            ]);
            $message = 'Status pulang dinonaktifkan.';
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('visits.index')->with('success', $message);
    }
}
