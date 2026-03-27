<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

use App\Models\Visit;
use App\Models\Medication;
use App\Models\Disease;
use App\Models\Student;
use App\Models\Employee;

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
        $seed = ['SMA' => 0, 'GURU' => 0, 'KARYAWAN' => 0, 'UMUM' => 0];
        $groupedMap = [];
        $applyAccFilter = fn ($query) => $type === 'acc_pulang'
            ? $query->where('visits.is_acc_pulang', true)
            : $query;

        $diseaseRows = $applyAccFilter(
            DB::table('disease_visit')
                ->join('visits', 'visits.id', '=', 'disease_visit.visit_id')
                ->join('diseases', 'diseases.id', '=', 'disease_visit.disease_id')
                ->whereMonth('visits.visit_date', $month)
                ->whereYear('visits.visit_date', $year)
                ->select(
                    'diseases.id as disease_id',
                    'diseases.name as disease_name',
                    'visits.patient_category',
                    DB::raw('COUNT(*) as aggregate_count')
                )
                ->groupBy('diseases.id', 'diseases.name', 'visits.patient_category')
        )->get();

        $undiagnosedRows = $applyAccFilter(
            DB::table('visits')
                ->leftJoin('disease_visit', 'disease_visit.visit_id', '=', 'visits.id')
                ->whereNull('disease_visit.id')
                ->whereMonth('visits.visit_date', $month)
                ->whereYear('visits.visit_date', $year)
                ->select(
                    DB::raw('0 as disease_id'),
                    DB::raw("'Tidak Terdiagnosa' as disease_name"),
                    'visits.patient_category',
                    DB::raw('COUNT(*) as aggregate_count')
                )
                ->groupBy('visits.patient_category')
        )->get();

        foreach ($diseaseRows->concat($undiagnosedRows) as $row) {
            $key = (string) $row->disease_id;
            if (!isset($groupedMap[$key])) {
                $groupedMap[$key] = [
                    'disease_name' => $row->disease_name,
                    'SMA' => $seed['SMA'],
                    'GURU' => $seed['GURU'],
                    'KARYAWAN' => $seed['KARYAWAN'],
                    'UMUM' => $seed['UMUM'],
                    'total' => 0,
                    'notes' => '',
                ];
            }

            if (isset($groupedMap[$key][$row->patient_category])) {
                $groupedMap[$key][$row->patient_category] += (int) $row->aggregate_count;
                $groupedMap[$key]['total'] += (int) $row->aggregate_count;
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
            ->get()
            ->map(function ($row) use ($startDate, $endDate) {
                return [
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'category' => $row->category,
                    'usage_count' => (int) $row->usage_count,
                    'visit_count' => (int) $row->visit_count,
                    'drilldown' => $this->buildMedicationDrilldown((int) $row->id, $startDate, $endDate),
                ];
            });

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
            ->get()
            ->map(function ($row) use ($startDate, $endDate) {
                return [
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'category' => $row->category,
                    'case_count' => (int) $row->case_count,
                    'visit_count' => (int) $row->visit_count,
                    'drilldown' => $this->buildDiseaseDrilldown((int) $row->id, $startDate, $endDate),
                ];
            });

        $frequentVisitors = $this->mergeVisitorAggregates(
            $this->aggregateStudentVisitors($startDate, $endDate),
            $this->aggregateEmployeeVisitors($startDate, $endDate),
            'visit_count'
        );

        $restVisitors = $this->mergeVisitorAggregates(
            $this->aggregateStudentVisitors($startDate, $endDate, 'is_rest'),
            $this->aggregateEmployeeVisitors($startDate, $endDate, 'is_rest'),
            'rest_count'
        );

        $accPulangVisitors = $this->mergeVisitorAggregates(
            $this->aggregateStudentVisitors($startDate, $endDate, 'is_acc_pulang'),
            $this->aggregateEmployeeVisitors($startDate, $endDate, 'is_acc_pulang'),
            'acc_pulang_count'
        );

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'summary_drilldowns' => [
                'visits_total' => $this->buildVisitListDrilldown($startDate, $endDate),
                'rest_total' => $this->buildVisitListDrilldown($startDate, $endDate, 'is_rest'),
                'acc_pulang_total' => $this->buildVisitListDrilldown($startDate, $endDate, 'is_acc_pulang'),
                'students_total' => $this->buildStudentDrilldown($startDate, $endDate),
                'employees_total' => $this->buildEmployeeDrilldown($startDate, $endDate),
                'medication_administrations_total' => $this->buildMedicationAdministrationDrilldown($startDate, $endDate),
            ],
            'top_medications' => $topMedications,
            'top_diseases' => $topDiseases,
            'frequent_visitors' => $frequentVisitors,
            'rest_visitors' => $restVisitors,
            'acc_pulang_visitors' => $accPulangVisitors,
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

    private function aggregateStudentVisitors(Carbon $startDate, Carbon $endDate, ?string $flagColumn = null): Collection
    {
        $query = DB::table('visits')
            ->join('students', 'students.id', '=', 'visits.student_id')
            ->leftJoin('student_class_histories as active_class', function ($join) {
                $join->on('active_class.student_id', '=', 'students.id')
                    ->where('active_class.is_active', true);
            })
            ->whereBetween('visits.visit_date', [$startDate, $endDate])
            ->whereNotNull('visits.student_id');

        if ($flagColumn) {
            $query->where("visits.{$flagColumn}", true);
        }

        $countField = $this->resolveCountField($flagColumn);
        $lastDateField = $this->resolveLastDateField($flagColumn);

        return $query
            ->select(
                'students.id',
                'students.name',
                'students.nis',
                'active_class.class_name',
                DB::raw('COUNT(*) as aggregate_count'),
                DB::raw('MAX(visits.visit_date) as last_visit_date')
            )
            ->groupBy('students.id', 'students.name', 'students.nis', 'active_class.class_name')
            ->get()
            ->map(function ($row) use ($countField, $lastDateField) {
                return [
                    'type' => 'student',
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'identifier' => $row->nis,
                    'meta' => collect([
                        $row->nis ? 'NIS ' . $row->nis : null,
                        $row->class_name,
                    ])->filter()->implode(' | '),
                    $countField => (int) $row->aggregate_count,
                    $lastDateField => $row->last_visit_date,
                    'history_url' => route('visitors.students.history', $row->id),
                    'drilldown' => $this->buildPersonVisitDrilldown('student', (int) $row->id, $startDate, $endDate, $flagColumn),
                ];
            });
    }

    private function aggregateEmployeeVisitors(Carbon $startDate, Carbon $endDate, ?string $flagColumn = null): Collection
    {
        $query = DB::table('visits')
            ->join('employees', 'employees.id', '=', 'visits.employee_id')
            ->whereBetween('visits.visit_date', [$startDate, $endDate])
            ->whereNotNull('visits.employee_id');

        if ($flagColumn) {
            $query->where("visits.{$flagColumn}", true);
        }

        $countField = $this->resolveCountField($flagColumn);
        $lastDateField = $this->resolveLastDateField($flagColumn);

        return $query
            ->select(
                'employees.id',
                'employees.name',
                'employees.nip',
                'employees.role_type',
                'employees.department',
                DB::raw('COUNT(*) as aggregate_count'),
                DB::raw('MAX(visits.visit_date) as last_visit_date')
            )
            ->groupBy('employees.id', 'employees.name', 'employees.nip', 'employees.role_type', 'employees.department')
            ->get()
            ->map(function ($row) use ($countField, $lastDateField) {
                return [
                    'type' => 'employee',
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'identifier' => $row->nip,
                    'meta' => collect([
                        $row->nip ? 'NIP ' . $row->nip : null,
                        $row->role_type,
                        $row->department,
                    ])->filter()->implode(' | '),
                    $countField => (int) $row->aggregate_count,
                    $lastDateField => $row->last_visit_date,
                    'history_url' => route('visitors.employees.history', $row->id),
                    'drilldown' => $this->buildPersonVisitDrilldown('employee', (int) $row->id, $startDate, $endDate, $flagColumn),
                ];
            });
    }

    private function mergeVisitorAggregates(Collection $students, Collection $employees, string $countField): Collection
    {
        return $students
            ->concat($employees)
            ->sortByDesc($countField)
            ->values()
            ->take(10);
    }

    private function resolveCountField(?string $flagColumn): string
    {
        return match ($flagColumn) {
            'is_rest' => 'rest_count',
            'is_acc_pulang' => 'acc_pulang_count',
            default => 'visit_count',
        };
    }

    private function resolveLastDateField(?string $flagColumn): string
    {
        return match ($flagColumn) {
            'is_rest' => 'last_rest_at',
            'is_acc_pulang' => 'last_acc_pulang_at',
            default => 'last_visit_at',
        };
    }

    private function buildVisitListDrilldown(Carbon $startDate, Carbon $endDate, ?string $flagColumn = null): array
    {
        $query = Visit::query()
            ->with(['student.activeClass:id,student_id,class_name,is_active', 'employee:id,name,nip,role_type,department'])
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time');

        if ($flagColumn) {
            $query->where($flagColumn, true);
        }

        return [
            'type' => 'visits',
            'items' => $query->limit(100)->get()->map(fn (Visit $visit) => $this->formatVisitDrilldownItem($visit))->all(),
        ];
    }

    private function buildStudentDrilldown(Carbon $startDate, Carbon $endDate): array
    {
        $items = DB::table('visits')
            ->join('students', 'students.id', '=', 'visits.student_id')
            ->leftJoin('student_class_histories as active_class', function ($join) {
                $join->on('active_class.student_id', '=', 'students.id')->where('active_class.is_active', true);
            })
            ->whereBetween('visits.visit_date', [$startDate, $endDate])
            ->whereNotNull('visits.student_id')
            ->select(
                'students.id',
                'students.name',
                'students.nis',
                'active_class.class_name',
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('MAX(visits.visit_date) as last_visit_date')
            )
            ->groupBy('students.id', 'students.name', 'students.nis', 'active_class.class_name')
            ->orderByDesc('visit_count')
            ->limit(100)
            ->get()
            ->map(fn ($row) => [
                'title' => $row->name,
                'meta' => collect([
                    $row->nis ? 'NIS ' . $row->nis : null,
                    $row->class_name,
                ])->filter()->implode(' | '),
                'count' => (int) $row->visit_count,
                'date' => $row->last_visit_date,
                'link' => route('visitors.students.history', $row->id),
                'link_label' => 'Riwayat',
            ])
            ->all();

        return ['type' => 'entities', 'items' => $items];
    }

    private function buildEmployeeDrilldown(Carbon $startDate, Carbon $endDate): array
    {
        $items = DB::table('visits')
            ->join('employees', 'employees.id', '=', 'visits.employee_id')
            ->whereBetween('visits.visit_date', [$startDate, $endDate])
            ->whereNotNull('visits.employee_id')
            ->select(
                'employees.id',
                'employees.name',
                'employees.nip',
                'employees.role_type',
                'employees.department',
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('MAX(visits.visit_date) as last_visit_date')
            )
            ->groupBy('employees.id', 'employees.name', 'employees.nip', 'employees.role_type', 'employees.department')
            ->orderByDesc('visit_count')
            ->limit(100)
            ->get()
            ->map(fn ($row) => [
                'title' => $row->name,
                'meta' => collect([
                    $row->nip ? 'NIP ' . $row->nip : null,
                    $row->role_type,
                    $row->department,
                ])->filter()->implode(' | '),
                'count' => (int) $row->visit_count,
                'date' => $row->last_visit_date,
                'link' => route('visitors.employees.history', $row->id),
                'link_label' => 'Riwayat',
            ])
            ->all();

        return ['type' => 'entities', 'items' => $items];
    }

    private function buildMedicationAdministrationDrilldown(Carbon $startDate, Carbon $endDate): array
    {
        $items = DB::table('medication_visit')
            ->join('visits', 'visits.id', '=', 'medication_visit.visit_id')
            ->join('medications', 'medications.id', '=', 'medication_visit.medication_id')
            ->whereBetween('visits.visit_date', [$startDate, $endDate])
            ->select(
                'medications.name as medication_name',
                'visits.id as visit_id',
                'visits.visit_date',
                'visits.visit_time',
                'visits.patient_name',
                'visits.patient_category'
            )
            ->orderByDesc('visits.visit_date')
            ->orderByDesc('visits.visit_time')
            ->limit(150)
            ->get()
            ->map(fn ($row) => [
                'title' => $row->medication_name,
                'meta' => collect([
                    $row->patient_name,
                    $row->patient_category,
                ])->filter()->implode(' | '),
                'date' => $row->visit_date,
                'link' => route('visits.show', $row->visit_id),
                'link_label' => 'Detail',
            ])
            ->all();

        return ['type' => 'simple', 'items' => $items];
    }

    private function buildMedicationDrilldown(int $medicationId, Carbon $startDate, Carbon $endDate): array
    {
        $items = Visit::query()
            ->with(['student.activeClass:id,student_id,class_name,is_active', 'employee:id,name,nip,role_type,department'])
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->whereHas('medications', fn ($query) => $query->where('medications.id', $medicationId))
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->limit(50)
            ->get()
            ->map(fn (Visit $visit) => $this->formatVisitDrilldownItem($visit))
            ->all();

        return ['type' => 'visits', 'items' => $items];
    }

    private function buildDiseaseDrilldown(int $diseaseId, Carbon $startDate, Carbon $endDate): array
    {
        $items = Visit::query()
            ->with(['student.activeClass:id,student_id,class_name,is_active', 'employee:id,name,nip,role_type,department'])
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->whereHas('diseases', fn ($query) => $query->where('diseases.id', $diseaseId))
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->limit(50)
            ->get()
            ->map(fn (Visit $visit) => $this->formatVisitDrilldownItem($visit))
            ->all();

        return ['type' => 'visits', 'items' => $items];
    }

    private function buildPersonVisitDrilldown(string $type, int $personId, Carbon $startDate, Carbon $endDate, ?string $flagColumn = null): array
    {
        $query = Visit::query()
            ->with(['student.activeClass:id,student_id,class_name,is_active', 'employee:id,name,nip,role_type,department'])
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time');

        if ($type === 'student') {
            $query->where('student_id', $personId);
        } else {
            $query->where('employee_id', $personId);
        }

        if ($flagColumn) {
            $query->where($flagColumn, true);
        }

        return [
            'type' => 'visits',
            'items' => $query->limit(50)->get()->map(fn (Visit $visit) => $this->formatVisitDrilldownItem($visit))->all(),
        ];
    }

    private function formatVisitDrilldownItem(Visit $visit): array
    {
        $historyUrl = null;
        $identityMeta = null;

        if ($visit->student_id && $visit->student) {
            $historyUrl = route('visitors.students.history', $visit->student_id);
            $identityMeta = collect([
                'SMA',
                $visit->student->nis ? 'NIS ' . $visit->student->nis : null,
                $visit->student->activeClass?->class_name ?? $visit->class_or_department,
            ])->filter()->implode(' | ');
        } elseif ($visit->employee_id && $visit->employee) {
            $historyUrl = route('visitors.employees.history', $visit->employee_id);
            $identityMeta = collect([
                $visit->patient_category,
                $visit->employee->nip ? 'NIP ' . $visit->employee->nip : null,
                $visit->employee->department ?: $visit->class_or_department,
            ])->filter()->implode(' | ');
        } else {
            $identityMeta = collect([
                $visit->patient_category,
                $visit->class_or_department,
            ])->filter()->implode(' | ');
        }

        return [
            'title' => $visit->patient_name,
            'meta' => $identityMeta,
            'subtitle' => $visit->complaint,
            'date' => optional($visit->visit_date)?->format('Y-m-d'),
            'time' => $visit->visit_time,
            'link' => route('visits.show', $visit),
            'link_label' => 'Detail',
            'secondary_link' => $historyUrl,
            'secondary_link_label' => $historyUrl ? 'Riwayat' : null,
        ];
    }
}
