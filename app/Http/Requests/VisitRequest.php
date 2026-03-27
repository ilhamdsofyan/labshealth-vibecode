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
            'disease_names' => ['required', 'array', 'min:1'],
            'disease_names.*' => ['required', 'string', 'max:255'],
            'medication_names' => ['nullable', 'array'],
            'medication_names.*' => ['required', 'string', 'max:255'],
            
            'external_patient_name' => ['required_if:patient_category,UMUM', 'nullable', 'string', 'max:255'],
            'additional_info' => ['required_if:patient_category,UMUM', 'nullable', 'string'],
            
            'class_or_department' => ['nullable', 'string', 'max:120'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'heart_rate' => ['nullable', 'integer', 'min:0', 'max:300'],
            'respiratory_rate' => ['nullable', 'integer', 'min:0', 'max:120'],
            'temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'complaint' => ['required', 'string'],
            'therapy' => ['nullable', 'string'],
            'officer_name' => ['required', 'string', 'max:120'],
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
            'disease_names.required' => 'Diagnosa/Penyakit wajib diisi minimal 1.',
            'disease_names.min' => 'Diagnosa/Penyakit wajib diisi minimal 1.',
            'complaint.required' => 'Keluhan wajib diisi.',
            'officer_name.required' => 'Nama petugas wajib diisi.',
            'acc_pulang_reason.required_if' => 'Alasan Acc Pulang wajib diisi jika dicentang.',
            'height_cm.numeric' => 'Tinggi badan harus berupa angka.',
            'weight_kg.numeric' => 'Berat badan harus berupa angka.',
            'blood_pressure.max' => 'Tekanan darah maksimal 20 karakter.',
            'heart_rate.integer' => 'Heart rate harus berupa angka bulat.',
            'respiratory_rate.integer' => 'RR harus berupa angka bulat.',
            'temperature_c.numeric' => 'Temperature harus berupa angka.',
        ];
    }
}
