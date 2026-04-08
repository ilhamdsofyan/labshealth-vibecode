<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitRequest;
use App\Models\Bed;
use App\Models\Disease;
use App\Models\Medication;
use App\Models\Student;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $query = Visit::query()
            ->select([
                'id',
                'visit_date',
                'visit_time',
                'patient_name',
                'patient_category',
                'class_or_department',
                'class_at_visit',
                'complaint',
                'student_id',
                'employee_id',
                'officer_name',
                'is_acc_pulang',
                'is_rest',
                'rest_started_at',
                'rest_ended_at',
                'rest_status',
            ])
            ->with([
                'diseases:id,name',
                'student:id,nis',
                'employee:id,nip',
            ]);

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
        $visit = $this->persistVisit($request->validated());

        return redirect()->route('visits.index')
            ->with('success', 'Data kunjungan berhasil disimpan.');
    }

    public function show(Visit $visit): View
    {
        $visit->load([
            'creator:id,name',
            'diseases:id,name',
            'medications:id,name',
            'student:id,nis',
            'employee:id,nip',
        ]);
        return view('visits.show', compact('visit'));
    }

    public function edit(Visit $visit): View
    {
        $visit->load([
            'diseases:id,name',
            'medications:id,name',
            'student:id,nis,name',
            'employee:id,nip,name',
        ]);
        return view('visits.edit', compact('visit'));
    }

    public function update(VisitRequest $request, Visit $visit): RedirectResponse
    {
        $data = $request->validated();
        $diseaseIds = $this->resolveDiseaseIds($data['disease_names'] ?? []);
        $medicationIds = $this->resolveMedicationIds($data['medication_names'] ?? []);
        unset($data['disease_names'], $data['medication_names']);

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

    public function syncOffline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.client_uuid' => ['required', 'string', 'max:100'],
            'items.*.payload' => ['required', 'array'],
        ]);

        $synced = [];
        $failed = [];
        $visitRequest = new VisitRequest();

        foreach ($validated['items'] as $item) {
            $clientUuid = trim((string) $item['client_uuid']);
            $payload = is_array($item['payload']) ? $item['payload'] : [];

            if (! Str::isUuid($clientUuid)) {
                $failed[] = [
                    'client_uuid' => $clientUuid,
                    'message' => 'Format identitas sinkronisasi tidak valid.',
                ];
                continue;
            }

            $existingVisit = Visit::query()
                ->select(['id', 'offline_client_uuid'])
                ->where('offline_client_uuid', $clientUuid)
                ->first();

            if ($existingVisit) {
                $synced[] = [
                    'client_uuid' => $clientUuid,
                    'visit_id' => $existingVisit->id,
                    'status' => 'duplicate',
                ];
                continue;
            }

            $validator = Validator::make($payload, $visitRequest->rules(), $visitRequest->messages());

            if ($validator->fails()) {
                $failed[] = [
                    'client_uuid' => $clientUuid,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray(),
                ];
                continue;
            }

            try {
                $visit = $this->persistVisit($validator->validated(), $clientUuid);
                $synced[] = [
                    'client_uuid' => $clientUuid,
                    'visit_id' => $visit->id,
                    'status' => 'created',
                ];
            } catch (\Throwable $exception) {
                report($exception);

                $failed[] = [
                    'client_uuid' => $clientUuid,
                    'message' => 'Sinkronisasi kunjungan gagal diproses di server.',
                ];
            }
        }

        return response()->json([
            'message' => count($synced) . ' kunjungan berhasil disinkronkan.',
            'synced' => $synced,
            'failed' => $failed,
        ], count($failed) > 0 ? 207 : 200);
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
                        'rest_started_at' => $victimVisit->rest_started_at ?: now(),
                        'rest_ended_at' => now(),
                        'rest_status' => 'completed',
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
                'rest_started_at' => now(),
                'rest_ended_at' => null,
                'rest_status' => 'active',
            ]);

            $message = 'Status rest diaktifkan dan pasien ditempatkan di ' . ($availableBed->name ?: $availableBed->code) . '.';
        } else {
            $restAction = $request->input('rest_action');
            if (! in_array($restAction, ['completed', 'cancelled'], true)) {
                $message = 'Pilih apakah rest selesai atau ga jadi rest terlebih dahulu.';
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }

                return redirect()->route('visits.index')->with('error', $message);
            }

            if ($restAction === 'completed') {
                $visit->update([
                    'is_rest' => false,
                    'bed_id' => null,
                    'rest_started_at' => $visit->rest_started_at ?: now(),
                    'rest_ended_at' => now(),
                    'rest_status' => 'completed',
                ]);

                $message = 'Rest diselesaikan dan histori rest tetap tersimpan.';
            } else {
                $visit->update([
                    'is_rest' => false,
                    'bed_id' => null,
                    'rest_started_at' => null,
                    'rest_ended_at' => null,
                    'rest_status' => 'cancelled',
                ]);

                $message = 'Rest dibatalkan dan tidak dihitung sebagai histori rest.';
            }
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
            $restPayload = [];
            if ($visit->is_rest) {
                $restPayload = [
                    'is_rest' => false,
                    'bed_id' => null,
                    'rest_started_at' => $visit->rest_started_at ?: now(),
                    'rest_ended_at' => now(),
                    'rest_status' => 'completed',
                ];
            }

            $visit->update([
                'is_acc_pulang' => true,
            ] + $restPayload);
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

    private function resolveDiseaseIds(array $names): array
    {
        return $this->resolveMasterIds($names, Disease::class);
    }

    private function resolveMedicationIds(array $names): array
    {
        return $this->resolveMasterIds($names, Medication::class);
    }

    private function resolveMasterIds(array $names, string $modelClass): array
    {
        return $this->normalizeTagValues($names)
            ->map(function (string $name) use ($modelClass) {
                $record = $modelClass::query()
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                    ->first();

                if (! $record) {
                    $record = $modelClass::create(['name' => $name]);
                }

                return (int) $record->id;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeTagValues(array $values): Collection
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values();
    }

    private function persistVisit(array $data, ?string $offlineClientUuid = null): Visit
    {
        $diseaseIds = $this->resolveDiseaseIds($data['disease_names'] ?? []);
        $medicationIds = $this->resolveMedicationIds($data['medication_names'] ?? []);
        unset($data['disease_names'], $data['medication_names']);

        $data['disease_id'] = $diseaseIds[0] ?? null;
        $data['medication_id'] = $medicationIds[0] ?? null;
        $data['created_by'] = auth()->id();
        $data['visit_type'] = 'kunjungan';
        $data['offline_client_uuid'] = $offlineClientUuid;

        if (($data['patient_category'] ?? null) === 'SMA' && ! empty($data['student_id'])) {
            $student = Student::query()->with('activeClass:id,student_id,class_name')->find($data['student_id']);
            $data['class_at_visit'] = $student?->activeClass?->class_name ?? ($data['class_or_department'] ?? null);
            $data['external_patient_name'] = null;
            $data['additional_info'] = null;
            $data['employee_id'] = null;
        } elseif (in_array($data['patient_category'] ?? null, ['GURU', 'KARYAWAN'], true)) {
            $data['class_or_department'] = null;
            $data['student_id'] = null;
            $data['external_patient_name'] = null;
            $data['additional_info'] = null;
        } elseif (($data['patient_category'] ?? null) === 'UMUM') {
            $data['student_id'] = null;
            $data['employee_id'] = null;
            $data['class_at_visit'] = null;
            $data['patient_name'] = $data['external_patient_name'] ?? $data['patient_name'];
        }

        $visit = Visit::create($data);
        $visit->diseases()->sync($diseaseIds);
        $visit->medications()->sync($medicationIds);

        return $visit;
    }
}
