# LabsHealth UKS `v1.2.0-alpha`

Minor alpha release untuk fitur Agenda Klinik.

## Summary

Rilis ini menambahkan modul agenda klinik terpisah dan mengubah panel kalender di dashboard menjadi format agenda/list.

## What’s New

### 1. Modul Agenda Klinik

- Tambah tabel database `clinic_agendas`.
- Tambah model `ClinicAgenda`.
- Tambah controller `ClinicAgendaController`.
- Tambah route:
  - `agendas.index`
  - `agendas.create`
  - `agendas.store`

### 2. Halaman Agenda

- Halaman daftar agenda dengan tabel + search.
- Halaman tambah agenda (form input tanggal, jam, judul, lokasi, deskripsi).

### 3. Dashboard Update

- Panel `Kalender Klinik` di dashboard diubah menjadi `Agenda Klinik` (list agenda).
- Data agenda dashboard diambil dari tabel agenda berdasarkan filter bulan/tahun aktif.

## Commit Included

- `0feed01` feat(agenda): add clinic agenda module and switch dashboard calendar to agenda

## Tag

- `v1.2.0-alpha`
