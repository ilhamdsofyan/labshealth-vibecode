# LabsHealth UKS `v0.3.0-alpha`

Minor alpha release untuk sinkronisasi tabel operasional agar lebih responsif tanpa full page reload.

## Summary

Rilis ini merapikan interaksi data di modul master dan kunjungan dengan pola asynchronous yang konsisten: pencarian, filter, pagination, refresh, dan delete. UX juga ditingkatkan dengan loading overlay di area tabel dan state loading pada tombol submit/delete.

## What’s New

### 1. Async Master Data Interaction Finalization

Master berikut sekarang konsisten memakai async flow untuk operasi tabel:

- `Diseases`
- `Students`
- `Employees`
- `Medications`

Cakupan:

- Search asynchronous
- Refresh asynchronous
- Pagination asynchronous
- Delete asynchronous
- Inline validation tetap tampil untuk error `422`

### 2. Loading UX Improvements

- Table overlay loading saat fetch/update data
- Tombol submit/delete otomatis `disabled` saat request berjalan
- Spinner + loading text tampil saat proses
- State tombol kembali normal setelah sukses maupun error

### 3. Async Visit Table Operations

Halaman `Data Kunjungan` sekarang mendukung asynchronous untuk:

- Filter
- Search
- Pagination
- Refresh
- Delete

`VisitController::destroy()` juga sudah mendukung JSON response (`expectsJson`) agar kompatibel dengan fetch async, tetap mempertahankan redirect untuk alur non-async.

## Commits Included

- `2c73b28` feat(master): finalize async table interactions and loading states
- `879479e` feat(visits): enable async table operations on index

## Tag

- `v0.3.0-alpha`
