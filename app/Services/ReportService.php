<?php

namespace App\Services;

use App\Models\Visit;
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
}
