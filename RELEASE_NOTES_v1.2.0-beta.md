# LabsHealth UKS `v1.2.0-beta`

Beta release pertama untuk workflow operasional inti UKS.

## Summary

Rilis ini menstabilkan fitur-fitur utama yang sebelumnya ada di alpha: agenda klinik berbasis visibilitas role, okupansi bed yang terhubung DB, toggle rest/pulang berbasis AJAX di daftar kunjungan, serta penyelarasan branding logo resmi.

## Highlights

### 1. Master Modal Reliability

- Tombol edit modal di master data kini tetap berfungsi setelah pagination/search async.
- Event handler dipindah ke delegated listener agar tidak hilang saat DOM tabel di-refresh.

### 2. Agenda Visibility + Modal Workflow

- Agenda mendukung visibilitas `publik/pribadi` berdasarkan role:
  - `admin/superadmin` bisa memilih publik/pribadi
  - role lain otomatis pribadi
- Halaman agenda diubah ke alur index + modal + async.
- Dashboard menampilkan agenda sesuai policy visibilitas user login.

### 3. Bed Occupancy via Database + Rest/Pulang Toggles

- Tambah entitas `beds` dan relasi `visits.bed_id`.
- Tambah status `is_rest` pada kunjungan.
- Daftar kunjungan punya dua toggle terpisah via AJAX:
  - `Rest`
  - `Pulang`
- Rule operasional:
  - Toggle rest hanya aktif di hari kunjungan (hari H).
  - Jika bed penuh dan visitor baru mengaktifkan rest, occupant lama otomatis dilepas.
  - Saat pulang aktif, rest dimatikan dan bed dilepas.
- Dashboard okupansi bed sekarang real-time dari assignment bed aktif di DB.

### 4. Official Logo Branding

- Integrasi logo panjang dan logo kotak ke app shell/login.
- Favicon dan apple-touch-icon menggunakan logo kotak.
- Palet warna global diselaraskan ke warna logo.

## Commits Included

- `04156a9` fix(master): keep edit modal working after async table refresh
- `a346232` feat(core): add agenda visibility and DB-driven rest/bed workflow
- `7cd83c0` feat(ui): apply official square/long logos across app shell

## Tag

- `v1.2.0-beta`
