<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');
        $students = Student::where('name', 'like', "%{$q}%")
            ->orWhere('nis', 'like', "%{$q}%")
            ->orderBy('created_at', 'desc')
            ->with(['activeClass'])
            ->limit(10)
            ->get();

        return response()->json($students->map(function ($s) {
            return [
                'id' => $s->id,
                'text' => "{$s->nis} - {$s->name} ({$s->activeClass?->class_name})",
                'class' => $s->activeClass?->class_name,
                'gender' => $s->gender,
                'avatar' => $s->avatar_path ? asset('storage/' . $s->avatar_path) : null,
            ];
        }));
    }

    public function index(Request $request): View
    {
        $query = Student::with(['activeClass' => function($q) {
            $q->orderBy('class_name');
        }]);

        $classSuggestions = StudentClassHistory::query()
            ->whereNotNull('class_name')
            ->where('class_name', '!=', '')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_name')) {
            $className = $request->class_name;
            $query->whereHas('activeClass', function ($q) use ($className) {
                $q->where('class_name', $className);
            });
        }

        $students = $query
                        ->paginate(15)->withQueryString();

        return view('admin.master.students.index', compact('students', 'classSuggestions'));
    }

    public function show(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson() && ! $request->ajax()) {
            return redirect()->route('admin.master.students.index');
        }

        $student->load([
            'activeClass',
            'health',
            'medicalHistory',
            'previousSchool',
            'learningProfile',
            'homeAssets',
            'familyMembers',
        ]);

        return response()->json($this->formatStudentDetail($student));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.master.students.index');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'unique:students,nis'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'avatar_cropped_data' => ['nullable', 'string'],
        ]);

        $student = Student::create([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
        ]);

        $avatarPath = $this->storeStudentAvatar($request);
        if ($avatarPath) {
            $student->update(['avatar_path' => $avatarPath]);
        }

        $student->classHistories()->create([
            'class_name' => $validated['class_name'],
            'academic_year' => $validated['academic_year'],
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data siswa berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): RedirectResponse
    {
        return redirect()->route('admin.master.students.index');
    }

    public function update(Request $request, Student $student): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'unique:students,nis,' . $student->id],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'avatar_cropped_data' => ['nullable', 'string'],
        ]);

        $student->update([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
        ]);

        $avatarPath = $this->storeStudentAvatar($request, $student->avatar_path);
        if ($avatarPath) {
            $student->update(['avatar_path' => $avatarPath]);
        }

        // If class or academic year changes, create new history and deactivate old ones
        $activeClass = $student->activeClass;
        if (!$activeClass || $activeClass->class_name !== $validated['class_name'] || $activeClass->academic_year !== $validated['academic_year']) {
            $student->classHistories()->update(['is_active' => false]);
            $student->classHistories()->create([
                'class_name' => $validated['class_name'],
                'academic_year' => $validated['academic_year'],
                'is_active' => true,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data siswa berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function updateDetail(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nickname' => ['nullable', 'string', 'max:80'],
            'nik_kitas' => ['nullable', 'string', 'max:24'],
            'family_card_number' => ['nullable', 'string', 'max:24'],
            'birth_place' => ['nullable', 'string', 'max:80'],
            'birth_date' => ['nullable', 'date'],
            'birth_certificate_number' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:20'],
            'citizenship' => ['nullable', 'string', 'max:30'],
            'daily_language' => ['nullable', 'string', 'max:40'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:120'],
            'address_text' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            'height_cm' => ['nullable', 'integer', 'min:0', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'head_circumference_cm' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'blood_type' => ['nullable', 'in:A,B,AB,O'],
            'rhesus' => ['nullable', 'in:+,-'],
            'eye_condition' => ['nullable', 'string', 'max:60'],
            'has_eye_disorder' => ['nullable', 'boolean'],
            'assistive_device' => ['nullable', 'string', 'max:80'],
            'ear_condition' => ['nullable', 'string', 'max:60'],
            'uses_hearing_aid' => ['nullable', 'boolean'],
            'face_shape' => ['nullable', 'string', 'max:40'],
            'hair_type' => ['nullable', 'string', 'max:40'],
            'skin_tone' => ['nullable', 'string', 'max:40'],

            'past_diseases' => ['nullable', 'string'],
            'ever_hospitalized' => ['nullable', 'boolean'],
            'has_recurring_disease' => ['nullable', 'boolean'],
            'surgery_history' => ['nullable', 'string'],
            'relapse_treatment' => ['nullable', 'string'],
            'drug_food_allergies' => ['nullable', 'string'],

            'smp_school_name' => ['nullable', 'string', 'max:150'],
            'smp_npsn' => ['nullable', 'string', 'max:8'],
            'smp_study_duration_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'ever_repeated_grade' => ['nullable', 'boolean'],
            'achievements' => ['nullable', 'string'],
            'receives_scholarship' => ['nullable', 'boolean'],
            'extracurricular_smp' => ['nullable', 'string'],

            'sports_hobby' => ['nullable', 'string', 'max:80'],
            'arts_hobby' => ['nullable', 'string', 'max:80'],
            'other_hobby' => ['nullable', 'string', 'max:80'],
            'talent_field' => ['nullable', 'string', 'max:80'],
            'has_leisure_time' => ['nullable', 'boolean'],
            'reading_start_age_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'writing_start_age_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'counting_start_age_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'speaking_start_age_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'start_kb_tk_age_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'start_sd_age_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'start_smp_age_months' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'likes_school' => ['nullable', 'boolean'],
            'likes_play_with' => ['nullable', 'string', 'max:80'],
            'likes_game_type' => ['nullable', 'string', 'max:80'],
            'preferred_activity' => ['nullable', 'string', 'max:80'],
            'concentration_level' => ['nullable', 'string', 'max:40'],
            'task_completion_style' => ['nullable', 'string', 'max:40'],
            'imagination_role' => ['nullable', 'string', 'max:100'],
            'has_home_study_group' => ['nullable', 'boolean'],
            'study_group_beneficial' => ['nullable', 'boolean'],
            'attends_tutoring' => ['nullable', 'boolean'],
            'tutoring_institution' => ['nullable', 'string', 'max:120'],
            'self_study_hours_per_day' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'has_home_study_schedule' => ['nullable', 'boolean'],
            'common_study_time' => ['nullable', 'string', 'max:40'],
            'asks_curiosity_questions' => ['nullable', 'boolean'],
            'curiosity_topics' => ['nullable', 'string'],

            'home_to_school_distance_km' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'home_to_school_travel_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'transport_mode' => ['nullable', 'string', 'max:40'],
            'household_vehicle' => ['nullable', 'string', 'max:120'],
            'living_environment' => ['nullable', 'string', 'max:80'],
            'home_lighting_condition' => ['nullable', 'string', 'max:60'],
            'bedroom_condition' => ['nullable', 'string', 'max:60'],
            'study_room_condition' => ['nullable', 'string', 'max:60'],
            'learning_tools' => ['nullable', 'string', 'max:160'],
            'has_musical_instruments' => ['nullable', 'boolean'],
            'musical_instrument_1' => ['nullable', 'string', 'max:60'],
            'musical_instrument_2' => ['nullable', 'string', 'max:60'],
            'has_sports_equipment' => ['nullable', 'boolean'],
            'sports_equipment_1' => ['nullable', 'string', 'max:60'],
            'sports_equipment_2' => ['nullable', 'string', 'max:60'],

            'father_full_name' => ['nullable', 'string', 'max:120'],
            'father_nik' => ['nullable', 'string', 'max:24'],
            'father_birth_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'father_relationship_detail' => ['nullable', 'string', 'max:60'],
            'father_whatsapp_number' => ['nullable', 'string', 'max:20'],
            'father_email' => ['nullable', 'email', 'max:120'],
            'father_religion' => ['nullable', 'string', 'max:20'],
            'father_occupation' => ['nullable', 'string', 'max:80'],
            'father_rank_group' => ['nullable', 'string', 'max:40'],
            'father_position_title' => ['nullable', 'string', 'max:80'],
            'father_education' => ['nullable', 'string', 'max:40'],
            'father_monthly_income' => ['nullable', 'integer', 'min:0'],
            'father_special_needs' => ['nullable', 'string', 'max:80'],
            'father_is_guardian' => ['nullable', 'boolean'],
            'father_is_emergency_contact' => ['nullable', 'boolean'],
            'father_is_primary_contact' => ['nullable', 'boolean'],
            'father_lives_with_student' => ['nullable', 'boolean'],
            'father_marital_status' => ['nullable', 'string', 'max:30'],
            'father_address_text' => ['nullable', 'string'],
            'father_notes' => ['nullable', 'string'],

            'mother_full_name' => ['nullable', 'string', 'max:120'],
            'mother_nik' => ['nullable', 'string', 'max:24'],
            'mother_birth_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'mother_relationship_detail' => ['nullable', 'string', 'max:60'],
            'mother_whatsapp_number' => ['nullable', 'string', 'max:20'],
            'mother_email' => ['nullable', 'email', 'max:120'],
            'mother_religion' => ['nullable', 'string', 'max:20'],
            'mother_occupation' => ['nullable', 'string', 'max:80'],
            'mother_rank_group' => ['nullable', 'string', 'max:40'],
            'mother_position_title' => ['nullable', 'string', 'max:80'],
            'mother_education' => ['nullable', 'string', 'max:40'],
            'mother_monthly_income' => ['nullable', 'integer', 'min:0'],
            'mother_special_needs' => ['nullable', 'string', 'max:80'],
            'mother_is_guardian' => ['nullable', 'boolean'],
            'mother_is_emergency_contact' => ['nullable', 'boolean'],
            'mother_is_primary_contact' => ['nullable', 'boolean'],
            'mother_lives_with_student' => ['nullable', 'boolean'],
            'mother_marital_status' => ['nullable', 'string', 'max:30'],
            'mother_address_text' => ['nullable', 'string'],
            'mother_notes' => ['nullable', 'string'],

            'guardian_full_name' => ['nullable', 'string', 'max:120'],
            'guardian_nik' => ['nullable', 'string', 'max:24'],
            'guardian_birth_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'guardian_relationship_detail' => ['nullable', 'string', 'max:60'],
            'guardian_whatsapp_number' => ['nullable', 'string', 'max:20'],
            'guardian_email' => ['nullable', 'email', 'max:120'],
            'guardian_religion' => ['nullable', 'string', 'max:20'],
            'guardian_occupation' => ['nullable', 'string', 'max:80'],
            'guardian_rank_group' => ['nullable', 'string', 'max:40'],
            'guardian_position_title' => ['nullable', 'string', 'max:80'],
            'guardian_education' => ['nullable', 'string', 'max:40'],
            'guardian_monthly_income' => ['nullable', 'integer', 'min:0'],
            'guardian_special_needs' => ['nullable', 'string', 'max:80'],
            'guardian_is_guardian' => ['nullable', 'boolean'],
            'guardian_is_emergency_contact' => ['nullable', 'boolean'],
            'guardian_is_primary_contact' => ['nullable', 'boolean'],
            'guardian_lives_with_student' => ['nullable', 'boolean'],
            'guardian_marital_status' => ['nullable', 'string', 'max:30'],
            'guardian_address_text' => ['nullable', 'string'],
            'guardian_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($student, $validated, $request) {
            $this->updateModelIfChanged($student, [
                'nickname' => $validated['nickname'] ?? null,
                'nik_kitas' => $validated['nik_kitas'] ?? null,
                'family_card_number' => $validated['family_card_number'] ?? null,
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'birth_certificate_number' => $validated['birth_certificate_number'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'citizenship' => $validated['citizenship'] ?? null,
                'daily_language' => $validated['daily_language'] ?? null,
                'whatsapp_number' => $validated['whatsapp_number'] ?? null,
                'email' => $validated['email'] ?? null,
                'address_text' => $validated['address_text'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncHasOne($student->health(), $this->filterFilled([
                'height_cm' => $validated['height_cm'] ?? null,
                'weight_kg' => $validated['weight_kg'] ?? null,
                'head_circumference_cm' => $validated['head_circumference_cm'] ?? null,
                'blood_type' => $validated['blood_type'] ?? null,
                'rhesus' => $validated['rhesus'] ?? null,
                'eye_condition' => $validated['eye_condition'] ?? null,
                'has_eye_disorder' => $request->boolean('has_eye_disorder'),
                'assistive_device' => $validated['assistive_device'] ?? null,
                'ear_condition' => $validated['ear_condition'] ?? null,
                'uses_hearing_aid' => $request->boolean('uses_hearing_aid'),
                'face_shape' => $validated['face_shape'] ?? null,
                'hair_type' => $validated['hair_type'] ?? null,
                'skin_tone' => $validated['skin_tone'] ?? null,
            ]));

            $this->syncHasOne($student->medicalHistory(), $this->filterFilled([
                'past_diseases' => $validated['past_diseases'] ?? null,
                'ever_hospitalized' => $request->boolean('ever_hospitalized'),
                'has_recurring_disease' => $request->boolean('has_recurring_disease'),
                'surgery_history' => $validated['surgery_history'] ?? null,
                'relapse_treatment' => $validated['relapse_treatment'] ?? null,
                'drug_food_allergies' => $validated['drug_food_allergies'] ?? null,
            ]));

            $this->syncHasOne($student->previousSchool(), $this->filterFilled([
                'smp_school_name' => $validated['smp_school_name'] ?? null,
                'smp_npsn' => $validated['smp_npsn'] ?? null,
                'smp_study_duration_months' => $validated['smp_study_duration_months'] ?? null,
                'ever_repeated_grade' => $request->boolean('ever_repeated_grade'),
                'achievements' => $validated['achievements'] ?? null,
                'receives_scholarship' => $request->boolean('receives_scholarship'),
                'extracurricular_smp' => $validated['extracurricular_smp'] ?? null,
            ]));

            $this->syncHasOne($student->learningProfile(), $this->filterFilled([
                'sports_hobby' => $validated['sports_hobby'] ?? null,
                'arts_hobby' => $validated['arts_hobby'] ?? null,
                'other_hobby' => $validated['other_hobby'] ?? null,
                'talent_field' => $validated['talent_field'] ?? null,
                'has_leisure_time' => $request->boolean('has_leisure_time'),
                'reading_start_age_months' => $validated['reading_start_age_months'] ?? null,
                'writing_start_age_months' => $validated['writing_start_age_months'] ?? null,
                'counting_start_age_months' => $validated['counting_start_age_months'] ?? null,
                'speaking_start_age_months' => $validated['speaking_start_age_months'] ?? null,
                'start_kb_tk_age_months' => $validated['start_kb_tk_age_months'] ?? null,
                'start_sd_age_months' => $validated['start_sd_age_months'] ?? null,
                'start_smp_age_months' => $validated['start_smp_age_months'] ?? null,
                'likes_school' => $request->boolean('likes_school'),
                'likes_play_with' => $validated['likes_play_with'] ?? null,
                'likes_game_type' => $validated['likes_game_type'] ?? null,
                'preferred_activity' => $validated['preferred_activity'] ?? null,
                'concentration_level' => $validated['concentration_level'] ?? null,
                'task_completion_style' => $validated['task_completion_style'] ?? null,
                'imagination_role' => $validated['imagination_role'] ?? null,
                'has_home_study_group' => $request->boolean('has_home_study_group'),
                'study_group_beneficial' => $request->boolean('study_group_beneficial'),
                'attends_tutoring' => $request->boolean('attends_tutoring'),
                'tutoring_institution' => $validated['tutoring_institution'] ?? null,
                'self_study_hours_per_day' => $validated['self_study_hours_per_day'] ?? null,
                'has_home_study_schedule' => $request->boolean('has_home_study_schedule'),
                'common_study_time' => $validated['common_study_time'] ?? null,
                'asks_curiosity_questions' => $request->boolean('asks_curiosity_questions'),
                'curiosity_topics' => $validated['curiosity_topics'] ?? null,
            ]));

            $this->syncHasOne($student->homeAssets(), $this->filterFilled([
                'home_to_school_distance_km' => $validated['home_to_school_distance_km'] ?? null,
                'home_to_school_travel_minutes' => $validated['home_to_school_travel_minutes'] ?? null,
                'transport_mode' => $validated['transport_mode'] ?? null,
                'household_vehicle' => $validated['household_vehicle'] ?? null,
                'living_environment' => $validated['living_environment'] ?? null,
                'home_lighting_condition' => $validated['home_lighting_condition'] ?? null,
                'bedroom_condition' => $validated['bedroom_condition'] ?? null,
                'study_room_condition' => $validated['study_room_condition'] ?? null,
                'learning_tools' => $validated['learning_tools'] ?? null,
                'has_musical_instruments' => $request->boolean('has_musical_instruments'),
                'musical_instrument_1' => $validated['musical_instrument_1'] ?? null,
                'musical_instrument_2' => $validated['musical_instrument_2'] ?? null,
                'has_sports_equipment' => $request->boolean('has_sports_equipment'),
                'sports_equipment_1' => $validated['sports_equipment_1'] ?? null,
                'sports_equipment_2' => $validated['sports_equipment_2'] ?? null,
            ]));

            $this->syncFamilyFromRequest($student, $request, 'father', false);
            $this->syncFamilyFromRequest($student, $request, 'mother', false);
            $this->syncFamilyFromRequest($student, $request, 'guardian', true);
        });

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Detail siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse|JsonResponse
    {
        if ($student->avatar_path) {
            Storage::disk('public')->delete($student->avatar_path);
        }

        $student->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Data siswa berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    public function removeAvatar(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        if ($student->avatar_path) {
            Storage::disk('public')->delete($student->avatar_path);
            $student->update(['avatar_path' => null]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Foto siswa berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.master.students.index')
            ->with('success', 'Foto siswa berhasil dihapus.');
    }

    private function storeStudentAvatar(Request $request, ?string $oldPath = null): ?string
    {
        if ($request->filled('avatar_cropped_data')) {
            $data = $request->input('avatar_cropped_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
                $extension = strtolower($matches[1]);
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                if (!in_array($extension, ['jpg', 'png', 'webp'], true)) {
                    return null;
                }

                $binary = base64_decode(substr($data, strpos($data, ',') + 1), true);
                if ($binary === false) {
                    return null;
                }

                $path = 'students/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($path, $binary);

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }

                return $path;
            }
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('students', 'public');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            return $path;
        }

        return null;
    }

    private function formatStudentDetail(Student $student): array
    {
        $student->loadMissing([
            'activeClass',
            'health',
            'medicalHistory',
            'previousSchool',
            'learningProfile',
            'homeAssets',
            'familyMembers',
        ]);

        $familyMembers = $student->familyMembers
            ->sortBy(fn ($member) => sprintf(
                '%d-%s-%s',
                $member->is_guardian ? 0 : 1,
                $member->relation_type ?? '',
                $member->full_name ?? ''
            ))
            ->values()
            ->map(fn ($member) => [
                'relation_type' => $member->relation_type,
                'full_name' => $member->full_name,
                'nik' => $member->nik,
                'birth_year' => $member->birth_year,
                'relationship_detail' => $member->relationship_detail,
                'whatsapp_number' => $member->whatsapp_number,
                'email' => $member->email,
                'religion' => $member->religion,
                'occupation' => $member->occupation,
                'rank_group' => $member->rank_group,
                'position_title' => $member->position_title,
                'education' => $member->education,
                'monthly_income' => $member->monthly_income,
                'special_needs' => $member->special_needs,
                'is_guardian' => $member->is_guardian,
                'is_emergency_contact' => $member->is_emergency_contact,
                'is_primary_contact' => $member->is_primary_contact,
                'lives_with_student' => $member->lives_with_student,
                'marital_status' => $member->marital_status,
                'address_text' => $member->address_text,
                'notes' => $member->notes,
            ]);

        return [
            'id' => $student->id,
            'nis' => $student->nis,
            'name' => $student->name,
            'nickname' => $student->nickname,
            'gender' => $student->gender,
            'class_name' => $student->activeClass?->class_name ?? $student->class_name,
            'academic_year' => $student->activeClass?->academic_year,
            'nisn' => $student->nisn,
            'nik_kitas' => $student->nik_kitas,
            'family_card_number' => $student->family_card_number,
            'birth_place' => $student->birth_place,
            'birth_date' => $student->birth_date?->format('Y-m-d'),
            'birth_certificate_number' => $student->birth_certificate_number,
            'religion' => $student->religion,
            'citizenship' => $student->citizenship,
            'daily_language' => $student->daily_language,
            'whatsapp_number' => $student->whatsapp_number,
            'email' => $student->email,
            'address_text' => $student->address_text,
            'notes' => $student->notes,
            'avatar_url' => $student->avatar_path ? asset('storage/' . $student->avatar_path) : null,
            'health' => $student->health,
            'medical_history' => $student->medicalHistory,
            'previous_school' => $student->previousSchool,
            'learning_profile' => $student->learningProfile,
            'home_assets' => $student->homeAssets,
            'family_members' => $familyMembers,
        ];
    }

    private function syncHasOne(mixed $relation, array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        $existing = $relation->first();
        if ($existing) {
            $this->updateModelIfChanged($existing, $attributes);
            return;
        }

        $relation->create($attributes);
    }

    private function filterFilled(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }

            if ($value !== null) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function syncFamilyFromRequest(Student $student, Request $request, string $prefix, bool $defaultGuardian): void
    {
        $fullName = trim((string) $request->input("{$prefix}_full_name", ''));
        $relationType = $defaultGuardian ? 'guardian' : $prefix;

        $member = $student->familyMembers()
            ->where('relation_type', $relationType)
            ->orderBy('id')
            ->first();

        if ($fullName === '' && ! $member) {
            return;
        }

        $attributes = $this->filterFilled([
            'full_name' => $fullName === '' ? null : $fullName,
            'nik' => $request->input("{$prefix}_nik"),
            'birth_year' => $request->input("{$prefix}_birth_year"),
            'relationship_detail' => $request->input("{$prefix}_relationship_detail"),
            'whatsapp_number' => $request->input("{$prefix}_whatsapp_number"),
            'email' => $request->input("{$prefix}_email"),
            'religion' => $request->input("{$prefix}_religion"),
            'occupation' => $request->input("{$prefix}_occupation"),
            'rank_group' => $request->input("{$prefix}_rank_group"),
            'position_title' => $request->input("{$prefix}_position_title"),
            'education' => $request->input("{$prefix}_education"),
            'monthly_income' => $request->input("{$prefix}_monthly_income"),
            'special_needs' => $request->input("{$prefix}_special_needs"),
            'is_guardian' => $request->has("{$prefix}_is_guardian") ? $request->boolean("{$prefix}_is_guardian") : ($defaultGuardian ? true : null),
            'is_emergency_contact' => $request->has("{$prefix}_is_emergency_contact") ? $request->boolean("{$prefix}_is_emergency_contact") : null,
            'is_primary_contact' => $request->has("{$prefix}_is_primary_contact") ? $request->boolean("{$prefix}_is_primary_contact") : null,
            'lives_with_student' => $request->has("{$prefix}_lives_with_student") ? $request->boolean("{$prefix}_lives_with_student") : null,
            'marital_status' => $request->input("{$prefix}_marital_status"),
            'address_text' => $request->input("{$prefix}_address_text"),
            'notes' => $request->input("{$prefix}_notes"),
        ]);

        if ($member) {
            if (! empty($attributes)) {
                $this->updateModelIfChanged($member, $attributes);
            }
            return;
        }

        if (! isset($attributes['full_name'])) {
            return;
        }

        $student->familyMembers()->create(array_merge($attributes, [
            'relation_type' => $relationType,
        ]));
    }

    private function updateModelIfChanged(mixed $model, array $attributes): void
    {
        $model->fill($attributes);

        if (! $model->isDirty()) {
            return;
        }

        $model->save();
    }
}
