<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\ImportLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class EmployeeImport implements ToCollection, WithHeadingRow
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
            $validator = Validator::make($row->toArray(), [
                'nip' => ['required', 'string', 'unique:employees,nip'],
                'name' => ['required', 'string', 'max:255'],
                'gender' => ['required', 'in:L,P'],
                'role_type' => ['required', 'in:GURU,KARYAWAN,TENDIK,PETUGAS'],
                'department' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $this->failedRows[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                    'data' => $row->toArray()
                ];
                continue;
            }

            Employee::create([
                'nip' => $row['nip'],
                'name' => $row['name'],
                'gender' => $row['gender'],
                'role_type' => $row['role_type'],
                'department' => $row['department'],
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
