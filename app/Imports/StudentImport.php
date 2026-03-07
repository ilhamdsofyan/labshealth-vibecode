<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\ImportLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Validator;

class StudentImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $failedRows = [];
    protected $importLog;

    public function __construct(ImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $row['nis'] = (string) $row['nis'];

            $validator = Validator::make($row->toArray(), [
                'nis' => ['required', 'string', 'unique:students,nis'],
                'name' => ['required', 'string', 'max:255'],
                'gender' => ['required', 'in:L,P'],
                'class_name' => ['required', 'string', 'max:100'],
                'academic_year' => ['required', 'string', 'max:20'],
            ]);

            if ($validator->fails()) {
                $this->failedRows[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                    'data' => $row->toArray()
                ];
                continue;
            }

            $student = Student::create([
                'nis' => $row['nis'],
                'name' => $row['name'],
                'gender' => $row['gender'],
            ]);

            $student->classHistories()->create([
                'class_name' => $row['class_name'],
                'academic_year' => $row['academic_year'],
                'is_active' => true,
            ]);

            $this->successCount++;
        }

        $this->importLog->update([
            'total_rows' => count($rows),
            'success_rows' => $this->successCount,
            'failed_rows' => count($this->failedRows),
            'status' => 'completed'
        ]);
    }
}
