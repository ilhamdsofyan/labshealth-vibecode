<?php

namespace App\Imports;

use App\Models\ImportLog;
use App\Models\Medication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MedicationImport implements ToCollection, WithHeadingRow
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
                'name' => ['required', 'string', 'max:255', 'unique:medications,name'],
                'category' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $this->failedRows[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                    'data' => $row->toArray(),
                ];
                continue;
            }

            Medication::create([
                'name' => $row['name'],
                'category' => $row['category'],
            ]);

            $this->successCount++;
        }

        $this->importLog->update([
            'total_rows' => count($rows),
            'success_rows' => $this->successCount,
            'failed_rows' => count($this->failedRows),
            'status' => 'completed',
        ]);
    }
}

