# LabsHealth UKS `v1.1.2-alpha`

Patch alpha release untuk penyelarasan komposisi layout dashboard terhadap referensi.

## Summary

Rilis ini memfokuskan penyamaan struktur visual dashboard agar lebih dekat ke referensi: urutan panel, proporsi kolom, serta format tabel aktivitas terbaru.

## Changes

- Rebuild komposisi dashboard menjadi 3 baris:
  - Welcome hero + quick actions
  - Okupansi ruang UKS + pulse kesehatan + kalender klinik
  - Intake terbaru full-width
- Perbarui kartu `Kalender Klinik` menjadi format agenda/list.
- Ubah `Intake Terbaru` menjadi tabel lebih lengkap (id number, gejala, waktu masuk, status, aksi).
- Tambah data backend untuk agenda klinik dan identitas pasien pada data recent.

## Commits Included

- `d42ae7b` feat(dashboard): restructure layout to match reference composition

## Tag

- `v1.1.2-alpha`
