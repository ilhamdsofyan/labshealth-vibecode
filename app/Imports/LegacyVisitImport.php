<?php

namespace App\Imports;

use App\Models\Visit;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Disease;
use App\Models\ImportLog;
use App\Models\Medication;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LegacyVisitImport implements ToCollection, WithHeadingRow
{
    protected $importLog;
    public $successCount = 0;
    public $failedRows = [];

    public function __construct(ImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $data = $this->processRow($row);
                if ($data) {
                    Visit::create($data);
                    $this->successCount++;
                } else {
                    $this->failedRows[] = [
                        'row' => $index + 2,
                        'reason' => 'Data tidak valid atau identitas pasien tidak ditemukan',
                        'data' => $row->toArray()
                    ];
                }
            } catch (\Exception $e) {
                $this->failedRows[] = [
                    'row' => $index + 2,
                    'reason' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
            }
        }

        $this->importLog->update([
            'success_rows' => $this->successCount,
            'failed_rows' => count($this->failedRows),
        ]);
    }

    protected function processRow($row)
    {
        $patientName = $this->normalizeWhitespace((string) $this->value($row, ['patient_name']));
        if ($patientName === '') {
            return null;
        }
        $position = trim((string) $this->value($row, ['position']));
        $gender = $this->normalizeGender((string) $this->value($row, ['gender', 'l_p', 'lp']));

        $patient = $this->resolvePatientIdentity($patientName, $position);

        // Disease normalization
        $diseaseName = trim((string) $this->value($row, ['disease_name']));
        $diseaseId = null;
        if ($diseaseName) {
            $disease = Disease::firstOrCreate(['name' => $diseaseName]);
            $diseaseId = $disease->id;
        }

        $complaint = trim((string) $this->value($row, ['complaint']));
        if (!$complaint) {
            $complaint = $diseaseName;
        }

        $therapy = trim((string) $this->value($row, ['therapy']));
        $medication = trim((string) $this->value($row, ['medication']));
        $medicationId = null;
        if ($medication !== '') {
            $medicationModel = Medication::firstOrCreate(['name' => $medication]);
            $medicationId = $medicationModel->id;
        }

        return [
            'visit_date' => $this->parseDate($this->value($row, ['visit_date'])),
            'visit_time' => $this->parseTime($this->value($row, ['visit_time'])),
            'patient_name' => $patient['patient_name'],
            'gender' => $patient['gender'] ?? $gender,
            'patient_category' => $patient['patient_category'],
            'student_id' => $patient['student_id'],
            'employee_id' => $patient['employee_id'],
            'disease_id' => $diseaseId,
            'medication_id' => $medicationId,
            'class_or_department' => $patient['class_or_department'],
            'class_at_visit' => $patient['class_at_visit'],
            'complaint' => $complaint,
            'therapy' => $therapy ?: null,
            'officer_name' => $this->value($row, ['officer_name']) ?: 'Imported Data',
            'notes' => $this->value($row, ['notes']),
            'is_acc_pulang' => $this->toBool($this->value($row, ['acc_pulang'])),
            'created_by' => Auth::id() ?: 1,
            'visit_type' => 'kunjungan' // mapping for legacy compat if needed
        ];
    }

    protected function parseDate($date)
    {
        try {
            if (is_numeric($date)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
            }
            $normalized = $this->normalizeDateString((string) $date);
            return Carbon::parse($normalized);
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }

    protected function parseTime($time): string
    {
        if (empty($time)) {
            return '00:00:00';
        }

        if (is_numeric($time)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($time))->format('H:i:s');
        }

        try {
            return Carbon::parse((string) $time)->format('H:i:s');
        } catch (\Exception $e) {
            return '00:00:00';
        }
    }

    protected function resolvePatientIdentity(string $patientName, string $position): array
    {
        $normName = $this->normalizeComparable($patientName);
        $normPosition = $this->normalizeComparable($position);

        $students = Student::with('activeClass')
            ->whereRaw('LOWER(name) = ?', [strtolower($patientName)])
            ->get();
        if ($students->isEmpty()) {
            $students = Student::with('activeClass')
                ->where('name', 'like', '%' . $patientName . '%')
                ->get()
                ->filter(function ($item) use ($normName) {
                    return $this->normalizeComparable((string) $item->name) === $normName;
                })
                ->values();
        }
        $student = $students->first(function ($item) use ($normPosition) {
            if ($normPosition === '') {
                return true;
            }
            $studentClass = $this->normalizeComparable((string) optional($item->activeClass)->class_name);
            return $studentClass !== '' && (str_contains($studentClass, $normPosition) || str_contains($normPosition, $studentClass));
        });
        if ($student) {
            return [
                'patient_category' => 'SMA',
                'patient_name' => $student->name,
                'gender' => $student->gender,
                'student_id' => $student->id,
                'employee_id' => null,
                'class_or_department' => $position ?: $student->activeClass?->class_name,
                'class_at_visit' => $student->activeClass?->class_name ?? $position,
            ];
        }

        $employees = Employee::whereRaw('LOWER(name) = ?', [strtolower($patientName)])->get();
        if ($employees->isEmpty()) {
            $employees = Employee::where('name', 'like', '%' . $patientName . '%')
                ->get()
                ->filter(function ($item) use ($normName) {
                    return $this->normalizeComparable((string) $item->name) === $normName;
                })
                ->values();
        }
        $employee = $employees->first(function ($item) use ($normPosition) {
            if ($normPosition === '') {
                return true;
            }

            $dept = $this->normalizeComparable((string) $item->department);
            $role = $this->normalizeComparable((string) $item->role_type);
            return ($dept !== '' && (str_contains($normPosition, $dept) || str_contains($dept, $normPosition)))
                || ($role !== '' && (str_contains($normPosition, $role) || str_contains($role, $normPosition)));
        });
        if ($employee) {
            return [
                'patient_category' => $this->normalizeStaffCategory($employee->role_type, $position),
                'patient_name' => $employee->name,
                'gender' => $employee->gender ?? null,
                'student_id' => null,
                'employee_id' => $employee->id,
                'class_or_department' => $position ?: $employee->department,
                'class_at_visit' => null,
            ];
        }

        return [
            'patient_category' => 'UMUM',
            'patient_name' => $patientName,
            'gender' => null,
            'student_id' => null,
            'employee_id' => null,
            'class_or_department' => $position ?: null,
            'class_at_visit' => null,
        ];
    }

    protected function normalizeStaffCategory(?string $roleType, string $fallbackText = ''): string
    {
        $value = strtoupper($this->normalizeWhitespace((string) $roleType));
        $fallback = strtoupper($this->normalizeWhitespace((string) $fallbackText));
        if (str_contains($value, 'GURU')) {
            return 'GURU';
        }
        if (str_contains($value, 'KARYAWAN') || str_contains($value, 'STAFF')) {
            return 'KARYAWAN';
        }
        if (str_contains($fallback, 'GURU')) {
            return 'GURU';
        }
        if (str_contains($fallback, 'KARYAWAN') || str_contains($fallback, 'STAFF')) {
            return 'KARYAWAN';
        }

        return 'KARYAWAN';
    }

    protected function normalizeGender(string $value): string
    {
        $v = strtoupper($this->normalizeWhitespace($value));
        if (in_array($v, ['L', 'LAKI-LAKI', 'LAKI LAKI'], true)) {
            return 'L';
        }
        return 'P';
    }

    protected function normalizeWhitespace(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    protected function normalizeComparable(string $value): string
    {
        $value = strtolower($this->normalizeWhitespace($value));
        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }

    protected function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $v = strtolower(trim((string) $value));
        return in_array($v, ['1', 'true', 'yes', 'y', 'ya'], true);
    }

    protected function value($row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }

    protected function normalizeDateString(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/^[A-Za-z]+(?:day)?\s*[,\/-]\s*/', '', $value) ?? $value;
        return $this->normalizeWhitespace($value);
    }
}
