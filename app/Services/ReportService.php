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
            ->with('disease');

        if ($type === 'acc_pulang') {
            $query->where('is_acc_pulang', true);
        }

        $visits = $query->get();

        // Group by disease_id and count by patient_category
        $grouped = $visits->groupBy('disease_id')->map(function ($group, $diseaseId) {
            $categories = ['SMA' => 0, 'GURU' => 0, 'KARYAWAN' => 0, 'UMUM' => 0];
            $diseaseName = $group->first()->disease?->name ?? 'Tidak Terdiagnosa';

            foreach ($group as $visit) {
                if (isset($categories[$visit->patient_category])) {
                    $categories[$visit->patient_category]++;
                }
            }

            $total = array_sum($categories);

            return [
                'disease_name' => $diseaseName,
                'SMA' => $categories['SMA'],
                'GURU' => $categories['GURU'],
                'KARYAWAN' => $categories['KARYAWAN'],
                'UMUM' => $categories['UMUM'],
                'total' => $total,
                'notes' => '',
            ];
        })->values();

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
