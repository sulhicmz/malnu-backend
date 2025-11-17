# Panduan Kontribusi Hyper Web School

Terima kasih ingin berkontribusi! Berikut panduannya:

## Project Structure Important Notice

**⚠️ CRITICAL**: This repository contains two applications:
1. **Main Application** (root directory) - **ACTIVE**: HyperVel framework (Laravel-style with Swoole support)
2. **Legacy Application** (`web-sch-12/` directory) - **DEPRECATED**: Laravel 12 with modular architecture

**ALL development should happen in the main application (root directory).** The web-sch-12 directory is maintained only for legacy purposes and will be deprecated.

## Cara Berkontribusi
1. Fork repository ini
2. Buat branch baru (`git checkout -b fitur/namafitur`)
3. Commit perubahan (`git commit -m 'Tambahkan fitur x'`)
4. Push ke branch (`git push origin fitur/namafitur`)
5. Buat Pull Request

## Standar Koding
- Ikuti [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Gunakan bahasa Inggris/Indonesia untuk komentar kode
- Dokumentasikan fungsi baru

## Sistem Issue/Task
- Gunakan template issue yang tersedia
- Label issue dengan:
  - `bug` untuk error
  - `enhancement` untuk fitur baru
  - `documentation` untuk perbaikan docs

## Komunikasi
- Diskusikan fitur besar di Issues sebelum coding
- Join Discord kami: https://discord.gg/Zy9d5rcJ

## Lingkungan Development
- PHP 8.2+ (required for HyperVel)
- HyperVel framework (main application)
- Laravel 12 (legacy application in web-sch-12/)
- MySQL 8
