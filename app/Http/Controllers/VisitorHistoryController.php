<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Student;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorHistoryController extends Controller
{
    public function student(Student $student): View
    {
        $student->load([
            'activeClass:id,student_id,class_name,academic_year',
            'health:id,student_id,height_cm,weight_kg,head_circumference_cm,blood_type,rhesus,eye_condition,has_eye_disorder,assistive_device,ear_condition,uses_hearing_aid',
            'medicalHistory:id,student_id,past_diseases,ever_hospitalized,has_recurring_disease,surgery_history,relapse_treatment,drug_food_allergies',
        ]);

        $visits = Visit::query()
            ->select([
                'id',
                'visit_date',
                'visit_time',
                'patient_name',
                'complaint',
                'officer_name',
                'is_acc_pulang',
                'is_rest',
                'created_by',
                'height_cm',
                'weight_kg',
                'blood_pressure',
                'heart_rate',
                'respiratory_rate',
                'temperature_c',
                'student_id',
            ])
            ->with(['diseases:id,name', 'creator:id,name'])
            ->where('student_id', $student->id)
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->paginate(15)
            ->withQueryString();

        $profile = [
            'type' => 'student',
            'title' => $student->name,
            'subtitle' => trim(collect([
                'NIS ' . $student->nis,
                $student->activeClass?->class_name,
                $student->activeClass?->academic_year,
            ])->filter()->implode(' | ')),
            'avatar_url' => $student->avatar_path ? asset('storage/' . $student->avatar_path) : null,
            'quick_facts' => array_filter([
                $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : null),
                $student->whatsapp_number ? 'WA: ' . $student->whatsapp_number : null,
                $student->email ? 'Email: ' . $student->email : null,
            ]),
            'sections' => [
                'Biodata' => [
                    'Nama Panggilan' => $student->nickname,
                    'NISN' => $student->nisn,
                    'NIK/KITAS' => $student->nik_kitas,
                    'No. KK' => $student->family_card_number,
                    'Tempat Lahir' => $student->birth_place,
                    'Tanggal Lahir' => $student->birth_date?->format('d/m/Y'),
                    'Agama' => $student->religion,
                    'Kewarganegaraan' => $student->citizenship,
                    'Bahasa Harian' => $student->daily_language,
                    'Alamat' => $student->address_text,
                ],
                'Medical Record' => [
                    'Tinggi Badan' => $student->health?->height_cm ? $student->health->height_cm . ' cm' : null,
                    'Berat Badan' => $student->health?->weight_kg ? $student->health->weight_kg . ' kg' : null,
                    'Lingkar Kepala' => $student->health?->head_circumference_cm ? $student->health->head_circumference_cm . ' cm' : null,
                    'Golongan Darah' => $student->health?->blood_type,
                    'Rhesus' => $student->health?->rhesus,
                    'Keadaan Mata' => $student->health?->eye_condition,
                    'Kelainan Mata' => $this->boolText($student->health?->has_eye_disorder),
                    'Alat Bantu' => $student->health?->assistive_device,
                    'Keadaan Telinga' => $student->health?->ear_condition,
                    'Alat Bantu Dengar' => $this->boolText($student->health?->uses_hearing_aid),
                    'Riwayat Penyakit' => $student->medicalHistory?->past_diseases,
                    'Pernah Rawat Inap' => $this->boolText($student->medicalHistory?->ever_hospitalized),
                    'Penyakit Kambuhan' => $this->boolText($student->medicalHistory?->has_recurring_disease),
                    'Riwayat Operasi' => $student->medicalHistory?->surgery_history,
                    'Penanganan Saat Kambuh' => $student->medicalHistory?->relapse_treatment,
                    'Alergi Obat/Makanan' => $student->medicalHistory?->drug_food_allergies,
                ],
            ],
            'history_route' => route('visitors.students.history', $student),
        ];

        return view('visitors.history', [
            'profile' => $profile,
            'visits' => $visits,
        ]);
    }

    public function employee(Employee $employee): View
    {
        $employee->load('medicalRecord:id,employee_id,height_cm,weight_kg,blood_type,rhesus,allergies,chronic_diseases,past_surgeries,regular_medications,last_checkup_date,medical_notes');

        $visits = Visit::query()
            ->select([
                'id',
                'visit_date',
                'visit_time',
                'patient_name',
                'complaint',
                'officer_name',
                'is_acc_pulang',
                'is_rest',
                'created_by',
                'height_cm',
                'weight_kg',
                'blood_pressure',
                'heart_rate',
                'respiratory_rate',
                'temperature_c',
                'employee_id',
            ])
            ->with(['diseases:id,name', 'creator:id,name'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->paginate(15)
            ->withQueryString();

        $profile = [
            'type' => 'employee',
            'title' => $employee->name,
            'subtitle' => trim(collect([
                'NIP ' . $employee->nip,
                $employee->role_type,
                $employee->department,
            ])->filter()->implode(' | ')),
            'avatar_url' => $employee->avatar_path ? asset('storage/' . $employee->avatar_path) : null,
            'quick_facts' => array_filter([
                $employee->role_type,
                $employee->department ? 'Unit: ' . $employee->department : null,
            ]),
            'sections' => [
                'Identitas' => [
                    'NIP' => $employee->nip,
                    'Nama' => $employee->name,
                    'Tipe Pegawai' => $employee->role_type,
                    'Bagian/Unit' => $employee->department,
                ],
                'Medical Record' => [
                    'Tinggi Badan' => $employee->medicalRecord?->height_cm ? $employee->medicalRecord->height_cm . ' cm' : null,
                    'Berat Badan' => $employee->medicalRecord?->weight_kg ? $employee->medicalRecord->weight_kg . ' kg' : null,
                    'Golongan Darah' => $employee->medicalRecord?->blood_type,
                    'Rhesus' => $employee->medicalRecord?->rhesus,
                    'Alergi' => $employee->medicalRecord?->allergies,
                    'Penyakit Kronis' => $employee->medicalRecord?->chronic_diseases,
                    'Riwayat Operasi' => $employee->medicalRecord?->past_surgeries,
                    'Obat Rutin' => $employee->medicalRecord?->regular_medications,
                    'Terakhir Checkup' => $employee->medicalRecord?->last_checkup_date?->format('d/m/Y'),
                    'Catatan Medis' => $employee->medicalRecord?->medical_notes,
                ],
            ],
            'history_route' => route('visitors.employees.history', $employee),
        ];

        return view('visitors.history', [
            'profile' => $profile,
            'visits' => $visits,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->get('q', ''));
        if ($query === '' || mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $students = Student::query()
            ->select(['id', 'nis', 'name'])
            ->with('activeClass:id,student_id,class_name')
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('nis', 'like', '%' . $query . '%');
            })
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(function (Student $student) {
                return [
                    'type' => 'student',
                    'label' => $student->name,
                    'meta' => trim(collect([
                        'NIS ' . $student->nis,
                        $student->activeClass?->class_name,
                    ])->filter()->implode(' | ')),
                    'url' => route('visitors.students.history', $student),
                ];
            });

        $employees = Employee::query()
            ->select(['id', 'nip', 'name', 'role_type', 'department'])
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('nip', 'like', '%' . $query . '%');
            })
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(function (Employee $employee) {
                return [
                    'type' => 'employee',
                    'label' => $employee->name,
                    'meta' => trim(collect([
                        'NIP ' . $employee->nip,
                        $employee->role_type,
                        $employee->department,
                    ])->filter()->implode(' | ')),
                    'url' => route('visitors.employees.history', $employee),
                ];
            });

        return response()->json($students->concat($employees)->values());
    }

    private function boolText(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? 'Ya' : 'Tidak';
    }
}
