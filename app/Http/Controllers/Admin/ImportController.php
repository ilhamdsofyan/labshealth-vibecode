<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportLog;
use App\Imports\LegacyVisitImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\HeadingRowImport;

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
            'type' => 'required|in:visits,students,student_details,employees,diseases,medications'
        ]);

        $file = $request->file('file');
        $type = $request->input('type');
        $mismatchMessage = $this->detectImportTypeMismatch($type, $file->getRealPath());

        if ($mismatchMessage) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['type' => $mismatchMessage]);
        }

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
            case 'student_details':
                $importer = new \App\Imports\StudentDetailImport($log);
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

    private function detectImportTypeMismatch(string $type, string $filePath): ?string
    {
        try {
            $headingsPerSheet = (new HeadingRowImport())->toArray($filePath);
            $firstSheet = $headingsPerSheet[0] ?? [];
            $headings = array_values(array_filter(array_map(static function ($heading) {
                return is_string($heading) ? trim($heading) : '';
            }, $firstSheet)));
        } catch (\Throwable) {
            return null;
        }

        if ($type === 'students') {
            $hasStudentsRequired = $this->hasAllHeaders($headings, ['nis', 'name', 'gender', 'class_name', 'academic_year']);
            $looksLikeDetail = $this->hasAnyHeader($headings, ['nickname', 'nik_kitas', 'father_full_name', 'past_diseases']);

            if (! $hasStudentsRequired && $looksLikeDetail) {
                return 'File yang diunggah terlihat seperti template Detail Siswa. Pilih tipe import "Detail Siswa".';
            }
        }

        if ($type === 'student_details') {
            $looksLikeStudents = $this->hasAllHeaders($headings, ['nis', 'name', 'gender', 'class_name', 'academic_year']);
            $hasDetailMarkers = $this->hasAnyHeader($headings, ['nickname', 'nik_kitas', 'father_full_name', 'past_diseases']);

            if ($looksLikeStudents && ! $hasDetailMarkers) {
                return 'File yang diunggah terlihat seperti template Data Siswa. Pilih tipe import "Data Siswa".';
            }
        }

        return null;
    }

    private function hasAllHeaders(array $headings, array $required): bool
    {
        foreach ($required as $header) {
            if (! in_array($header, $headings, true)) {
                return false;
            }
        }

        return true;
    }

    private function hasAnyHeader(array $headings, array $candidates): bool
    {
        foreach ($candidates as $header) {
            if (in_array($header, $headings, true)) {
                return true;
            }
        }

        return false;
    }

    public function downloadTemplate(Request $request)
    {
        $type = $request->get('type', 'visits');
        
        $templates = [
            'visits' => ['visit_date', 'visit_time', 'patient_name', 'position', 'complaint', 'disease_name', 'therapy', 'medication', 'acc_pulang', 'officer_name', 'notes'],
            'students' => ['nis', 'name', 'gender', 'class_name', 'academic_year'],
            'student_details' => [
                'nis', 'nickname', 'nik_kitas', 'family_card_number', 'birth_place', 'birth_date', 'birth_certificate_number', 'religion',
                'citizenship', 'daily_language', 'whatsapp_number', 'email', 'address_text', 'notes',
                'height_cm', 'weight_kg', 'head_circumference_cm', 'blood_type', 'rhesus', 'eye_condition',
                'has_eye_disorder', 'assistive_device', 'ear_condition', 'uses_hearing_aid', 'face_shape',
                'hair_type', 'skin_tone',
                'past_diseases', 'ever_hospitalized', 'has_recurring_disease', 'surgery_history',
                'relapse_treatment', 'drug_food_allergies',
                'smp_school_name', 'smp_npsn', 'smp_study_duration_months', 'ever_repeated_grade',
                'achievements', 'receives_scholarship', 'extracurricular_smp',
                'sports_hobby', 'arts_hobby', 'other_hobby', 'talent_field', 'has_leisure_time',
                'reading_start_age_months', 'writing_start_age_months', 'counting_start_age_months',
                'speaking_start_age_months', 'start_kb_tk_age_months', 'start_sd_age_months',
                'start_smp_age_months', 'likes_school', 'likes_play_with', 'likes_game_type',
                'preferred_activity', 'concentration_level', 'task_completion_style', 'imagination_role',
                'has_home_study_group', 'study_group_beneficial', 'attends_tutoring', 'tutoring_institution',
                'self_study_hours_per_day', 'has_home_study_schedule', 'common_study_time',
                'asks_curiosity_questions', 'curiosity_topics',
                'home_to_school_distance_km', 'home_to_school_travel_minutes', 'transport_mode',
                'household_vehicle', 'living_environment', 'home_lighting_condition', 'bedroom_condition',
                'study_room_condition', 'learning_tools', 'has_musical_instruments', 'musical_instrument_1',
                'musical_instrument_2', 'has_sports_equipment', 'sports_equipment_1', 'sports_equipment_2',
                'father_full_name', 'father_nik', 'father_birth_year', 'father_relationship_detail',
                'father_whatsapp_number', 'father_email', 'father_religion', 'father_occupation',
                'father_rank_group', 'father_position_title', 'father_education', 'father_monthly_income',
                'father_special_needs', 'father_is_guardian', 'father_is_emergency_contact',
                'father_is_primary_contact', 'father_lives_with_student', 'father_marital_status',
                'father_address_text', 'father_notes',
                'mother_full_name', 'mother_nik', 'mother_birth_year', 'mother_relationship_detail',
                'mother_whatsapp_number', 'mother_email', 'mother_religion', 'mother_occupation',
                'mother_rank_group', 'mother_position_title', 'mother_education', 'mother_monthly_income',
                'mother_special_needs', 'mother_is_guardian', 'mother_is_emergency_contact',
                'mother_is_primary_contact', 'mother_lives_with_student', 'mother_marital_status',
                'mother_address_text', 'mother_notes',
                'guardian_full_name', 'guardian_nik', 'guardian_birth_year', 'guardian_relationship_detail',
                'guardian_whatsapp_number', 'guardian_email', 'guardian_religion', 'guardian_occupation',
                'guardian_rank_group', 'guardian_position_title', 'guardian_education',
                'guardian_monthly_income', 'guardian_special_needs', 'guardian_is_guardian',
                'guardian_is_emergency_contact', 'guardian_is_primary_contact',
                'guardian_lives_with_student', 'guardian_marital_status', 'guardian_address_text',
                'guardian_notes',
            ],
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
