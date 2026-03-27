<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Student;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get monthly report data grouped by complaint.
     *
     * @param int    $month  1-12
     * @param int    $year   e.g. 2026
     * @param string $type   'kunjungan' or 'acc_pulang'
     * @return array{data: Collection, totals: array}
     */
    public function getMonthlyReport(int $month, int $year, string $type = 'kunjungan'): array
    {
        $query = Visit::whereMonth('visit_date', $month)
            ->whereYear('visit_date', $year)
            ->with('diseases');

        if ($type === 'acc_pulang') {
            $query->where('is_acc_pulang', true);
        }

        $visits = $query->get();

        $seed = ['SMA' => 0, 'GURU' => 0, 'KARYAWAN' => 0, 'UMUM' => 0];
        $groupedMap = [];

        foreach ($visits as $visit) {
            $assignedDiseases = $visit->diseases;
            if ($assignedDiseases->isEmpty()) {
                $assignedDiseases = collect([(object) ['id' => 0, 'name' => 'Tidak Terdiagnosa']]);
            }

            foreach ($assignedDiseases as $disease) {
                $key = (string) $disease->id;
                if (!isset($groupedMap[$key])) {
                    $groupedMap[$key] = [
                        'disease_name' => $disease->name,
                        'SMA' => $seed['SMA'],
                        'GURU' => $seed['GURU'],
                        'KARYAWAN' => $seed['KARYAWAN'],
                        'UMUM' => $seed['UMUM'],
                        'total' => 0,
                        'notes' => '',
                    ];
                }

                if (isset($groupedMap[$key][$visit->patient_category])) {
                    $groupedMap[$key][$visit->patient_category]++;
                    $groupedMap[$key]['total']++;
                }
            }
        }

        $grouped = collect(array_values($groupedMap));

        // Sort by total desc
        $grouped = $grouped->sortByDesc('total')->values();

        // Calculate column totals
        $totals = [
            'SMA' => $grouped->sum('SMA'),
            'GURU' => $grouped->sum('GURU'),
            'KARYAWAN' => $grouped->sum('KARYAWAN'),
            'UMUM' => $grouped->sum('UMUM'),
            'total' => $grouped->sum('total'),
        ];

        return [
            'data' => $grouped,
            'totals' => $totals,
        ];
    }

    public function getAnalyticsReport(?string $dateFrom = null, ?string $dateTo = null): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($dateFrom, $dateTo);

        $visitQuery = Visit::query()
            ->whereBetween('visit_date', [$startDate, $endDate]);

        $visits = (clone $visitQuery)
            ->with(['student.activeClass', 'employee'])
            ->orderByDesc('visit_date')
            ->get();

        $summary = [
            'visits_total' => $visits->count(),
            'rest_total' => $visits->where('is_rest', true)->count(),
            'acc_pulang_total' => $visits->where('is_acc_pulang', true)->count(),
            'students_total' => $visits->whereNotNull('student_id')->pluck('student_id')->unique()->count(),
            'employees_total' => $visits->whereNotNull('employee_id')->pluck('employee_id')->unique()->count(),
            'medication_administrations_total' => (int) DB::table('medication_visit')
                ->join('visits', 'visits.id', '=', 'medication_visit.visit_id')
                ->whereBetween('visits.visit_date', [$startDate, $endDate])
                ->count(),
        ];

        $topMedications = DB::table('medication_visit')
            ->join('visits', 'visits.id', '=', 'medication_visit.visit_id')
            ->join('medications', 'medications.id', '=', 'medication_visit.medication_id')
            ->whereBetween('visits.visit_date', [$startDate, $endDate])
            ->select(
                'medications.id',
                'medications.name',
                'medications.category',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('COUNT(DISTINCT medication_visit.visit_id) as visit_count')
            )
            ->groupBy('medications.id', 'medications.name', 'medications.category')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        $topDiseases = DB::table('disease_visit')
            ->join('visits', 'visits.id', '=', 'disease_visit.visit_id')
            ->join('diseases', 'diseases.id', '=', 'disease_visit.disease_id')
            ->whereBetween('visits.visit_date', [$startDate, $endDate])
            ->select(
                'diseases.id',
                'diseases.name',
                'diseases.category',
                DB::raw('COUNT(*) as case_count'),
                DB::raw('COUNT(DISTINCT disease_visit.visit_id) as visit_count')
            )
            ->groupBy('diseases.id', 'diseases.name', 'diseases.category')
            ->orderByDesc('case_count')
            ->limit(10)
            ->get();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'top_medications' => $topMedications,
            'top_diseases' => $topDiseases,
            'frequent_visitors' => $this->aggregateVisitors($visits)->take(10),
            'rest_visitors' => $this->aggregateVisitors($visits->where('is_rest', true), 'rest_count', 'last_rest_at')->take(10),
            'acc_pulang_visitors' => $this->aggregateVisitors($visits->where('is_acc_pulang', true), 'acc_pulang_count', 'last_acc_pulang_at')->take(10),
        ];
    }

    private function resolveDateRange(?string $dateFrom, ?string $dateTo): array
    {
        $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfMonth()->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate];
    }

    private function aggregateVisitors(Collection $visits, string $countField = 'visit_count', string $lastDateField = 'last_visit_at'): Collection
    {
        return $visits
            ->filter(function (Visit $visit) {
                return $visit->student_id || $visit->employee_id;
            })
            ->groupBy(function (Visit $visit) {
                if ($visit->student_id) {
                    return 'student:' . $visit->student_id;
                }

                return 'employee:' . $visit->employee_id;
            })
            ->map(function (Collection $group, string $key) use ($countField, $lastDateField) {
                /** @var Visit $first */
                $first = $group->first();
                $latestVisit = $group->sortByDesc(fn (Visit $visit) => ($visit->visit_date?->format('Y-m-d') ?? '') . ' ' . ($visit->visit_time ?? ''))->first();

                if (str_starts_with($key, 'student:')) {
                    /** @var Student|null $student */
                    $student = $first->student;
                    $meta = collect([
                        $student?->nis ? 'NIS ' . $student->nis : null,
                        $student?->activeClass?->class_name,
                    ])->filter()->implode(' | ');

                    return [
                        'type' => 'student',
                        'name' => $student?->name ?? $first->patient_name,
                        'identifier' => $student?->nis,
                        'meta' => $meta,
                        $countField => $group->count(),
                        $lastDateField => $latestVisit?->visit_date,
                        'history_url' => $student ? route('visitors.students.history', $student) : null,
                    ];
                }

                /** @var Employee|null $employee */
                $employee = $first->employee;
                $meta = collect([
                    $employee?->nip ? 'NIP ' . $employee->nip : null,
                    $employee?->role_type,
                    $employee?->department,
                ])->filter()->implode(' | ');

                return [
                    'type' => 'employee',
                    'name' => $employee?->name ?? $first->patient_name,
                    'identifier' => $employee?->nip,
                    'meta' => $meta,
                    $countField => $group->count(),
                    $lastDateField => $latestVisit?->visit_date,
                    'history_url' => $employee ? route('visitors.employees.history', $employee) : null,
                ];
            })
            ->sortByDesc($countField)
            ->values();
    }
}
