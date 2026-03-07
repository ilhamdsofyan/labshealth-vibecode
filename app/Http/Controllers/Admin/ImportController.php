<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportLog;
use App\Imports\LegacyVisitImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ImportController extends Controller
{
    public function index()
    {
        $logs = ImportLog::with('uploader')->latest()->paginate(10);
        return view('admin.import.index', compact('logs'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'type' => 'required|in:visits,students,employees,diseases,medications'
        ]);

        $file = $request->file('file');
        $type = $request->input('type');

        $log = ImportLog::create([
            'file_name' => "[{$type}] " . $file->getClientOriginalName(),
            'total_rows' => 0, 
            'success_rows' => 0,
            'failed_rows' => 0,
            'uploaded_by' => Auth::id(),
        ]);

        switch ($type) {
            case 'students':
                $importer = new \App\Imports\StudentImport($log);
                break;
            case 'employees':
                $importer = new \App\Imports\EmployeeImport($log);
                break;
            case 'diseases':
                $importer = new \App\Imports\DiseaseImport($log);
                break;
            case 'medications':
                $importer = new \App\Imports\MedicationImport($log);
                break;
            default:
                $importer = new LegacyVisitImport($log);
                break;
        }

        Excel::import($importer, $file);

        $message = "Import " . ucfirst($type) . " selesai. Berhasil: {$importer->successCount}, Gagal: " . count($importer->failedRows);
        
        if (count($importer->failedRows) > 0) {
            return redirect()->back()->with('success', $message)->with('failedRows', $importer->failedRows);
        }

        return redirect()->back()->with('success', $message);
    }

    public function downloadTemplate(Request $request)
    {
        $type = $request->get('type', 'visits');
        
        $templates = [
            'visits' => ['visit_date', 'visit_time', 'patient_name', 'position', 'complaint', 'disease_name', 'therapy', 'medication', 'acc_pulang', 'officer_name', 'notes'],
            'students' => ['nis', 'name', 'gender', 'class_name', 'academic_year'],
            'employees' => ['nip', 'name', 'gender', 'role_type', 'department'],
            'diseases' => ['name', 'category'],
            'medications' => ['name', 'category']
        ];

        $headers = $templates[$type] ?? $templates['visits'];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_{$type}.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
