# LabsHealth UKS `v1.1.0-alpha`

Minor alpha release untuk penyelarasan visual lanjutan dan redesign konten dashboard agar mengikuti referensi secara lebih presisi.

## Summary

Rilis ini fokus memperbaiki kesesuaian komponen yang belum identik, khususnya posisi menu footer sidebar (settings/logout), akurasi warna dark mode, serta penyusunan ulang dashboard utama.

## What’s New

### 1. Sidebar Footer Alignment

- Sidebar kini menggunakan layout kolom penuh (`flex-column`) agar area footer menempel di bawah.
- Menu `Settings` dan `Logout` di footer memakai treatment visual yang sama dengan item navigasi utama.
- Hasilnya lebih dekat dengan referensi desain light/dark.

### 2. Dark Mode Color Matching

- Penyesuaian tone dark mode untuk top navbar dan search input agar selaras dengan referensi.
- Layering surface dark (`#121212`, `#1e1e1e`, `#2a2a2a`) diperkuat untuk kontras yang konsisten.

### 3. Dashboard Content Rebuild (Bahasa Indonesia)

Dashboard dibangun ulang dengan section berikut:

- Welcome message + CTA
- Aksi Cepat
- Okupansi Ruang UKS (layout placeholder)
- Pulse Kesehatan dengan donut chart kategori pasien
- Filter bulan & tahun untuk statistik bulanan
- Kalender Klinik bulanan
- Intake Terbaru

Semua label utama dashboard disesuaikan ke Bahasa Indonesia.

## Commits Included

- `f81b929` feat(dashboard): align shell and rebuild dashboard to reference layout

## Tag

- `v1.1.0-alpha`
