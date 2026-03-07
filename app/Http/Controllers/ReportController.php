<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function monthly(Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $report = $this->reportService->getMonthlyReport($month, $year, 'kunjungan');

        $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');

        return view('reports.monthly', [
            'report' => $report,
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName,
            'reportType' => 'kunjungan',
            'reportTitle' => "Laporan Kunjungan {$monthName}",
        ]);
    }

    public function accPulang(Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $report = $this->reportService->getMonthlyReport($month, $year, 'acc_pulang');

        $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');

        return view('reports.monthly', [
            'report' => $report,
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName,
            'reportType' => 'acc_pulang',
            'reportTitle' => "Laporan Acc Pulang {$monthName}",
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $type = $request->input('type', 'kunjungan');

        $report = $this->reportService->getMonthlyReport($month, $year, $type);
        $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');

        $title = $type === 'acc_pulang' ? "Acc Pulang" : "Kunjungan";
        $filename = "Laporan_{$title}_{$monthName}.xlsx";

        return Excel::download(
            new MonthlyReportExport($report, "Laporan {$title} {$monthName}"),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $type = $request->input('type', 'kunjungan');

        $report = $this->reportService->getMonthlyReport($month, $year, $type);
        $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');

        $title = $type === 'acc_pulang' ? "Acc Pulang" : "Kunjungan";

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'reportTitle' => "Laporan {$title} {$monthName}",
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download("Laporan_{$title}_{$monthName}.pdf");
    }
}
