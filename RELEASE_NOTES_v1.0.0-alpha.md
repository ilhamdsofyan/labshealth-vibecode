# LabsHealth UKS `v1.0.0-alpha`

Major alpha release untuk pembaruan visual menyeluruh berdasarkan referensi desain light/dark mode.

## Summary

Rilis ini melakukan overhaul layout dan sistem pewarnaan aplikasi agar konsisten dengan arah desain baru: sidebar modern, header baru, token warna light/dark, dan interaksi tema yang persisten.

## What’s New

### 1. Major App Shell Redesign

- Struktur global layout (`sidebar`, `top navbar`, `main content`) didesain ulang.
- Visual komponen menyesuaikan referensi desain light/dark yang diberikan.
- Navigasi bawah di sidebar (settings/logout) ikut disesuaikan.

### 2. Dual Theme System (Light/Dark)

- Menambahkan sistem tema berbasis CSS variables.
- Tema dapat di-toggle langsung dari header.
- Preferensi tema disimpan di `localStorage` agar persisten.
- `meta theme-color` otomatis menyesuaikan mode aktif.

### 3. Visual Language Upgrade

- Font global diganti ke `Public Sans`.
- Palet warna utama, surface, border, dan teks diharmonisasi ulang.
- Komponen utama ikut dituning: card, table, form, button, dropdown, alert.
- Overlay loading async disesuaikan agar menyatu dengan kedua mode tema.

## Commits Included

- `110eb12` feat(ui)!: overhaul app shell to reference-based light/dark theme

## Tag

- `v1.0.0-alpha`
