<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visit_date' => ['required', 'date'],
            'visit_time' => ['required', 'date_format:H:i'],
            'patient_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'patient_category' => ['required', 'in:SMA,GURU,KARYAWAN,UMUM'],
            
            'student_id' => ['required_if:patient_category,SMA', 'nullable', 'exists:students,id'],
            'employee_id' => ['required_if:patient_category,GURU,patient_category,KARYAWAN', 'nullable', 'exists:employees,id'],
            'disease_id' => ['required', 'exists:diseases,id'],
            'medication_id' => ['nullable', 'exists:medications,id'],
            
            'external_patient_name' => ['required_if:patient_category,UMUM', 'nullable', 'string', 'max:255'],
            'additional_info' => ['required_if:patient_category,UMUM', 'nullable', 'string'],
            
            'class_or_department' => ['nullable', 'string', 'max:255'],
            'complaint' => ['required', 'string'],
            'therapy' => ['nullable', 'string'],
            'officer_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_acc_pulang' => ['nullable', 'boolean'],
            'acc_pulang_reason' => ['required_if:is_acc_pulang,1', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_time.required' => 'Waktu kunjungan wajib diisi.',
            'patient_name.required' => 'Nama pasien wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'patient_category.required' => 'Kategori pasien wajib dipilih.',
            'student_id.required_if' => 'Siswa wajib dipilih untuk kategori SMA.',
            'employee_id.required_if' => 'Pegawai wajib dipilih untuk kategori GURU/KARYAWAN.',
            'disease_id.required' => 'Diagnosa/Penyakit wajib dipilih.',
            'complaint.required' => 'Keluhan wajib diisi.',
            'officer_name.required' => 'Nama petugas wajib diisi.',
            'acc_pulang_reason.required_if' => 'Alasan Acc Pulang wajib diisi jika dicentang.',
        ];
    }
}
