# LabsHealth UKS `v0.1.0-alpha`

Initial alpha release untuk baseline aplikasi operasional UKS berbasis Laravel 12, dengan modul inti sudah cukup lengkap untuk dipakai internal.

## Highlights

- Sistem autentikasi dan otorisasi berbasis role-permission (route-name based).
- Manajemen kunjungan UKS end-to-end (CRUD) untuk kategori pasien:
  - `SMA`
  - `GURU`
  - `KARYAWAN`
  - `UMUM`
- Master data utama:
  - Siswa
  - Pegawai
  - Penyakit
  - Obat (`Medication`) - baru di alpha ini
- Import data massal (Excel/CSV) dengan logging hasil dan detail baris gagal.
- Laporan bulanan + laporan `Acc Pulang` dengan export Excel dan PDF.
- Sidebar/menu dinamis berbasis database + filter role/permission.

## What’s Included

### 1. Auth & Access Control

- Login email/password.
- Optional Google OAuth login.
- User nonaktif otomatis ditolak akses.
- Middleware `permission` melakukan pengecekan permission berdasarkan nama route.
- `superadmin` bypass permission check sesuai desain.

### 2. Visit Management

- Resource `visits` dengan form terstruktur:
  - tanggal, jam
  - kategori pasien
  - identitas pasien
  - kelas/bagian
  - diagnosa
  - terapi/tindakan
  - status `Acc Pulang`
  - petugas dan catatan
- Rule kategori pasien:
  - `SMA` -> student relation + snapshot kelas aktif
  - `GURU/KARYAWAN` -> employee relation
  - `UMUM` -> external patient fields

### 3. Medication Master (New)

- Penambahan entitas `medications` sebagai master table obat.
- CRUD admin untuk data obat.
- Endpoint search `Select2` untuk input kunjungan.
- Relasi `visits.medication_id` (nullable FK).
- Tampilan kunjungan (list/detail/form) sudah mendukung obat.

### 4. Import Pipelines

- Import types:
  - `students`
  - `employees`
  - `diseases`
  - `medications`
  - `visits`
- Header import `visits` yang dipakai:
  - `visit_date, visit_time, patient_name, position, complaint, disease_name, therapy, medication, acc_pulang, officer_name, notes`
- Identity resolution saat import visit:
  - Berdasarkan `patient_name + position`
  - Match ke siswa/pegawai jika tersedia
  - Fallback ke kategori `UMUM` jika tidak match
- Normalisasi master otomatis:
  - `disease_name` -> `diseases` via `firstOrCreate`
  - `medication` -> `medications` via `firstOrCreate`
- Semua proses import tersimpan ke `import_logs` plus detail baris gagal.

### 5. Reporting & Export

- Rekap bulanan kunjungan per penyakit.
- Rekap bulanan `Acc Pulang`.
- Export Excel (styled summary).
- Export PDF (print-friendly layout).

### 6. Admin Governance

- Role, permission, user management.
- Permission sync command untuk generate dari route names.
- Menu management (hierarki + reorder + visibility constraints).

## Database Changes (Included in alpha)

- `medications` table ditambahkan.
- `visits.medication_id` foreign key ditambahkan.
- Existing schema untuk:
  - role/permission/menu
  - visit refinements (`disease_id`, `student_id`, `employee_id`, `class_at_visit`, `is_acc_pulang`, soft delete)
  - class history siswa
  - import logs
  - audit logs

## Notes

- Ini release alpha: fitur utama sudah berjalan, tapi masih ada ruang hardening dan test coverage.
- Default test bawaan framework masih ada (belum mewakili full business flow).
- Disarankan jalankan:
  - `php artisan migrate`
  - `php artisan permission:sync`
  - `php artisan test`

## Tag Reference

- Tag: `v0.1.0-alpha`
- Commit: `eb17e8d`
