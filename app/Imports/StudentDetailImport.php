<?php

namespace App\Imports;

use App\Models\ImportLog;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class StudentDetailImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    public array $failedRows = [];

    protected ImportLog $importLog;

    public function __construct(ImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowArray = $this->normalizeRow($row->toArray());

            $validator = Validator::make($rowArray, $this->rules());

            if ($validator->fails()) {
                $this->failedRows[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                    'data' => $rowArray,
                ];
                continue;
            }

            $student = Student::query()->where('nis', $rowArray['nis'])->first();

            if (! $student) {
                $this->failedRows[] = [
                    'row' => $index + 2,
                    'errors' => ['Siswa dengan NIS tersebut tidak ditemukan.'],
                    'data' => $rowArray,
                ];
                continue;
            }

            try {
                DB::transaction(function () use ($student, $rowArray) {
                    $this->updateStudent($student, $rowArray);
                    $this->syncOneToOneRelations($student, $rowArray);
                    $this->syncFamilyMember($student, $rowArray, 'father', false);
                    $this->syncFamilyMember($student, $rowArray, 'mother', false);
                    $this->syncFamilyMember($student, $rowArray, 'guardian', true);
                });
            } catch (\Throwable $exception) {
                $this->failedRows[] = [
                    'row' => $index + 2,
                    'errors' => [$exception->getMessage()],
                    'data' => $rowArray,
                ];
                continue;
            }

            $this->successCount++;
        }

        $this->importLog->update([
            'total_rows' => count($rows),
            'success_rows' => $this->successCount,
            'failed_rows' => count($this->failedRows),
            'status' => 'completed',
        ]);
    }

    protected function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'exists:students,nis'],
            'birth_date' => ['nullable', 'date'],
        ];
    }

    protected function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalizedKey = trim((string) $key);
            $normalized[$normalizedKey] = $this->normalizeValue($value);
        }

        $normalized['nis'] = isset($normalized['nis']) ? (string) $normalized['nis'] : null;
        $normalized = $this->applyStringLengthLimits($normalized);

        foreach ($this->booleanColumns() as $column) {
            $normalized[$column] = $this->parseBoolean($normalized[$column] ?? null);
        }

        foreach ($this->integerColumns() as $column) {
            $normalized[$column] = $this->parseInteger($normalized[$column] ?? null);
        }

        foreach ($this->decimalColumns() as $column) {
            $normalized[$column] = $this->parseDecimal($normalized[$column] ?? null);
        }

        if (isset($normalized['blood_type']) && is_string($normalized['blood_type'])) {
            $normalized['blood_type'] = strtoupper($normalized['blood_type']);
            if (! in_array($normalized['blood_type'], ['A', 'B', 'AB', 'O'], true)) {
                $normalized['blood_type'] = null;
            }
        }

        if (isset($normalized['rhesus']) && is_string($normalized['rhesus'])) {
            $normalized['rhesus'] = trim($normalized['rhesus']);
            if (! in_array($normalized['rhesus'], ['+', '-'], true)) {
                $normalized['rhesus'] = null;
            }
        }

        if (isset($normalized['birth_date'])) {
            $normalized['birth_date'] = $this->parseDate($normalized['birth_date']);
        }

        return $normalized;
    }

    protected function applyStringLengthLimits(array $normalized): array
    {
        foreach ($this->stringLengthLimits() as $column => $maxLength) {
            if (! array_key_exists($column, $normalized) || $normalized[$column] === null) {
                continue;
            }

            if (! is_string($normalized[$column])) {
                $normalized[$column] = (string) $normalized[$column];
            }

            $value = trim($normalized[$column]);
            if ($value === '') {
                $normalized[$column] = null;
                continue;
            }

            $normalized[$column] = $this->truncateString($value, $maxLength);
        }

        return $normalized;
    }

    protected function stringLengthLimits(): array
    {
        return [
            'nickname' => 80,
            'nisn' => 10,
            'nik_kitas' => 24,
            'family_card_number' => 24,
            'birth_place' => 80,
            'birth_certificate_number' => 50,
            'religion' => 20,
            'citizenship' => 30,
            'daily_language' => 40,
            'whatsapp_number' => 20,
            'email' => 120,
            'blood_type' => 2,
            'rhesus' => 1,
            'eye_condition' => 60,
            'assistive_device' => 80,
            'ear_condition' => 60,
            'face_shape' => 40,
            'hair_type' => 40,
            'skin_tone' => 40,
            'smp_school_name' => 150,
            'smp_npsn' => 8,
            'sports_hobby' => 80,
            'arts_hobby' => 80,
            'other_hobby' => 80,
            'talent_field' => 80,
            'likes_play_with' => 80,
            'likes_game_type' => 80,
            'preferred_activity' => 80,
            'concentration_level' => 40,
            'task_completion_style' => 40,
            'imagination_role' => 100,
            'tutoring_institution' => 120,
            'common_study_time' => 40,
            'transport_mode' => 40,
            'household_vehicle' => 120,
            'living_environment' => 80,
            'home_lighting_condition' => 60,
            'bedroom_condition' => 60,
            'study_room_condition' => 60,
            'learning_tools' => 160,
            'musical_instrument_1' => 60,
            'musical_instrument_2' => 60,
            'sports_equipment_1' => 60,
            'sports_equipment_2' => 60,
            'father_full_name' => 120,
            'father_nik' => 24,
            'father_relationship_detail' => 60,
            'father_whatsapp_number' => 20,
            'father_email' => 120,
            'father_religion' => 20,
            'father_occupation' => 80,
            'father_rank_group' => 40,
            'father_position_title' => 80,
            'father_education' => 40,
            'father_special_needs' => 80,
            'father_marital_status' => 30,
            'mother_full_name' => 120,
            'mother_nik' => 24,
            'mother_relationship_detail' => 60,
            'mother_whatsapp_number' => 20,
            'mother_email' => 120,
            'mother_religion' => 20,
            'mother_occupation' => 80,
            'mother_rank_group' => 40,
            'mother_position_title' => 80,
            'mother_education' => 40,
            'mother_special_needs' => 80,
            'mother_marital_status' => 30,
            'guardian_full_name' => 120,
            'guardian_nik' => 24,
            'guardian_relationship_detail' => 60,
            'guardian_whatsapp_number' => 20,
            'guardian_email' => 120,
            'guardian_religion' => 20,
            'guardian_occupation' => 80,
            'guardian_rank_group' => 40,
            'guardian_position_title' => 80,
            'guardian_education' => 40,
            'guardian_special_needs' => 80,
            'guardian_marital_status' => 30,
        ];
    }

    protected function truncateString(string $value, int $maxLength): string
    {
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($value) <= $maxLength) {
                return $value;
            }

            return mb_substr($value, 0, $maxLength);
        }

        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return substr($value, 0, $maxLength);
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? null : $value;
        }

        return $value;
    }

    protected function booleanColumns(): array
    {
        return [
            'has_eye_disorder',
            'uses_hearing_aid',
            'ever_hospitalized',
            'has_recurring_disease',
            'ever_repeated_grade',
            'receives_scholarship',
            'has_leisure_time',
            'likes_school',
            'has_home_study_group',
            'study_group_beneficial',
            'attends_tutoring',
            'has_home_study_schedule',
            'asks_curiosity_questions',
            'has_musical_instruments',
            'has_sports_equipment',
            'father_is_guardian',
            'father_is_emergency_contact',
            'father_is_primary_contact',
            'father_lives_with_student',
            'mother_is_guardian',
            'mother_is_emergency_contact',
            'mother_is_primary_contact',
            'mother_lives_with_student',
            'guardian_is_guardian',
            'guardian_is_emergency_contact',
            'guardian_is_primary_contact',
            'guardian_lives_with_student',
        ];
    }

    protected function integerColumns(): array
    {
        return [
            'height_cm',
            'smp_study_duration_months',
            'reading_start_age_months',
            'writing_start_age_months',
            'counting_start_age_months',
            'speaking_start_age_months',
            'start_kb_tk_age_months',
            'start_sd_age_months',
            'start_smp_age_months',
            'home_to_school_travel_minutes',
            'father_birth_year',
            'mother_birth_year',
            'guardian_birth_year',
            'father_monthly_income',
            'mother_monthly_income',
            'guardian_monthly_income',
        ];
    }

    protected function decimalColumns(): array
    {
        return [
            'weight_kg',
            'head_circumference_cm',
            'self_study_hours_per_day',
            'home_to_school_distance_km',
        ];
    }

    protected function parseBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['1', 'true', 'yes', 'ya', 'y'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'no', 'tidak', 't'], true)) {
            return false;
        }

        return null;
    }

    protected function parseInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        return null;
    }

    protected function parseDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    protected function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function updateStudent(Student $student, array $row): void
    {
        $attributes = [
            'nickname' => $row['nickname'] ?? null,
            'nik_kitas' => $row['nik_kitas'] ?? null,
            'family_card_number' => $row['family_card_number'] ?? null,
            'birth_place' => $row['birth_place'] ?? null,
            'birth_date' => $row['birth_date'] ?? null,
            'birth_certificate_number' => $row['birth_certificate_number'] ?? null,
            'religion' => $row['religion'] ?? null,
            'citizenship' => $row['citizenship'] ?? null,
            'daily_language' => $row['daily_language'] ?? null,
            'whatsapp_number' => $row['whatsapp_number'] ?? null,
            'email' => $row['email'] ?? null,
            'address_text' => $row['address_text'] ?? null,
            'notes' => $row['notes'] ?? null,
        ];

        $this->updateModelIfChanged($student, $attributes);
    }

    protected function syncOneToOneRelations(Student $student, array $row): void
    {
        $health = $this->prepareAttributes(
            $this->only($row, [
                'height_cm',
                'weight_kg',
                'head_circumference_cm',
                'blood_type',
                'rhesus',
                'eye_condition',
                'has_eye_disorder',
                'assistive_device',
                'ear_condition',
                'uses_hearing_aid',
                'face_shape',
                'hair_type',
                'skin_tone',
            ]),
            ['has_eye_disorder', 'uses_hearing_aid']
        );

        if ($this->hasMeaningfulData($health)) {
            $this->syncHasOne($student->health(), $health);
        }

        $medical = $this->prepareAttributes(
            $this->only($row, [
                'past_diseases',
                'ever_hospitalized',
                'has_recurring_disease',
                'surgery_history',
                'relapse_treatment',
                'drug_food_allergies',
            ]),
            ['ever_hospitalized', 'has_recurring_disease']
        );

        if ($this->hasMeaningfulData($medical)) {
            $this->syncHasOne($student->medicalHistory(), $medical);
        }

        $previousSchool = $this->prepareAttributes(
            $this->only($row, [
                'smp_school_name',
                'smp_npsn',
                'smp_study_duration_months',
                'ever_repeated_grade',
                'achievements',
                'receives_scholarship',
                'extracurricular_smp',
            ]),
            ['ever_repeated_grade', 'receives_scholarship']
        );

        if ($this->hasMeaningfulData($previousSchool)) {
            $this->syncHasOne($student->previousSchool(), $previousSchool);
        }

        $learningProfile = $this->prepareAttributes(
            $this->only($row, [
                'sports_hobby',
                'arts_hobby',
                'other_hobby',
                'talent_field',
                'has_leisure_time',
                'reading_start_age_months',
                'writing_start_age_months',
                'counting_start_age_months',
                'speaking_start_age_months',
                'start_kb_tk_age_months',
                'start_sd_age_months',
                'start_smp_age_months',
                'likes_school',
                'likes_play_with',
                'likes_game_type',
                'preferred_activity',
                'concentration_level',
                'task_completion_style',
                'imagination_role',
                'has_home_study_group',
                'study_group_beneficial',
                'attends_tutoring',
                'tutoring_institution',
                'self_study_hours_per_day',
                'has_home_study_schedule',
                'common_study_time',
                'asks_curiosity_questions',
                'curiosity_topics',
            ]),
            [
                'has_leisure_time',
                'likes_school',
                'has_home_study_group',
                'study_group_beneficial',
                'attends_tutoring',
                'has_home_study_schedule',
                'asks_curiosity_questions',
            ]
        );

        if ($this->hasMeaningfulData($learningProfile)) {
            $this->syncHasOne($student->learningProfile(), $learningProfile);
        }

        $homeAssets = $this->prepareAttributes(
            $this->only($row, [
                'home_to_school_distance_km',
                'home_to_school_travel_minutes',
                'transport_mode',
                'household_vehicle',
                'living_environment',
                'home_lighting_condition',
                'bedroom_condition',
                'study_room_condition',
                'learning_tools',
                'has_musical_instruments',
                'musical_instrument_1',
                'musical_instrument_2',
                'has_sports_equipment',
                'sports_equipment_1',
                'sports_equipment_2',
            ]),
            ['has_musical_instruments', 'has_sports_equipment']
        );

        if ($this->hasMeaningfulData($homeAssets)) {
            $this->syncHasOne($student->homeAssets(), $homeAssets);
        }
    }

    protected function syncFamilyMember(Student $student, array $row, string $prefix, bool $isGuardianRelation): void
    {
        $attributes = $this->prepareAttributes([
            'full_name' => $row[$prefix . '_full_name'] ?? null,
            'nik' => $row[$prefix . '_nik'] ?? null,
            'birth_year' => $row[$prefix . '_birth_year'] ?? null,
            'relationship_detail' => $row[$prefix . '_relationship_detail'] ?? null,
            'whatsapp_number' => $row[$prefix . '_whatsapp_number'] ?? null,
            'email' => $row[$prefix . '_email'] ?? null,
            'religion' => $row[$prefix . '_religion'] ?? null,
            'occupation' => $row[$prefix . '_occupation'] ?? null,
            'rank_group' => $row[$prefix . '_rank_group'] ?? null,
            'position_title' => $row[$prefix . '_position_title'] ?? null,
            'education' => $row[$prefix . '_education'] ?? null,
            'monthly_income' => $row[$prefix . '_monthly_income'] ?? null,
            'special_needs' => $row[$prefix . '_special_needs'] ?? null,
            'is_guardian' => $row[$prefix . '_is_guardian'] ?? $isGuardianRelation,
            'is_emergency_contact' => $row[$prefix . '_is_emergency_contact'] ?? null,
            'is_primary_contact' => $row[$prefix . '_is_primary_contact'] ?? null,
            'lives_with_student' => $row[$prefix . '_lives_with_student'] ?? null,
            'marital_status' => $row[$prefix . '_marital_status'] ?? null,
            'address_text' => $row[$prefix . '_address_text'] ?? null,
            'notes' => $row[$prefix . '_notes'] ?? null,
        ], ['is_guardian', 'is_emergency_contact', 'is_primary_contact', 'lives_with_student']);

        $fullName = $attributes['full_name'] ?? null;
        if (is_string($fullName)) {
            $fullName = trim($fullName);
        }
        if ($fullName === null || $fullName === '') {
            unset($attributes['full_name']);
        } else {
            $attributes['full_name'] = $fullName;
        }

        $relationType = $isGuardianRelation ? 'guardian' : $prefix;

        $member = $student->familyMembers()
            ->where('relation_type', $relationType)
            ->orderBy('id')
            ->first();

        if ($member) {
            if (! $this->hasMeaningfulData($attributes)) {
                return;
            }
            $this->updateModelIfChanged($member, $attributes);
            return;
        }

        if (! array_key_exists('full_name', $attributes)) {
            return;
        }

        if (! $this->hasMeaningfulData($attributes)) {
            return;
        }

        $student->familyMembers()->create(array_merge($attributes, [
            'relation_type' => $relationType,
        ]));
    }

    protected function syncHasOne(mixed $relation, array $attributes): void
    {
        if (! $this->hasMeaningfulData($attributes)) {
            return;
        }

        $existing = $relation->first();
        if ($existing) {
            $this->updateModelIfChanged($existing, $attributes);
            return;
        }

        $relation->create($attributes);
    }

    protected function hasMeaningfulData(array $attributes): bool
    {
        foreach ($attributes as $key => $value) {
            if ($key === 'is_guardian' && $value === false) {
                continue;
            }

            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    protected function only(array $row, array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $row[$key] ?? null;
        }

        return $values;
    }

    protected function prepareAttributes(array $attributes, array $booleanKeys = []): array
    {
        foreach ($booleanKeys as $key) {
            if (array_key_exists($key, $attributes) && $attributes[$key] === null) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    protected function updateModelIfChanged(Model $model, array $attributes): void
    {
        $model->fill($attributes);

        if (! $model->isDirty()) {
            return;
        }

        $model->save();
    }
}
