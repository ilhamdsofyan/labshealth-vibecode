# LabsHealth UKS `v1.1.1-alpha`

Patch alpha release untuk perbaikan keterbacaan dark mode.

## Summary

Rilis ini fokus pada kontras teks di mode gelap agar konten dashboard dan komponen utama tidak redup/gelap.

## Fixes

- Menaikkan level kontras token teks dark mode (`main`, `muted`, `sidebar`).
- Menyelaraskan variabel warna Bootstrap dark mode agar tidak kembali ke warna default low-contrast.
- Menambahkan override keterbacaan untuk card/table/form di mode gelap.
- Menambahkan override spesifik dashboard (judul section, legend, kalender, okupansi bed, hero subtitle).

## Commits Included

- `273f06d` fix(ui): improve dark mode text contrast for dashboard readability

## Tag

- `v1.1.1-alpha`
