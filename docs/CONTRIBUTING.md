# Panduan Kontribusi Hyper Web School

Terima kasih ingin berkontribusi! Berikut panduannya:

## Project Structure Important Notice

**⚠️ CRITICAL DEPRECATION NOTICE ⚠️**: This repository contains two applications:

1. **Main Application** (root directory) - **ACTIVE**: HyperVel framework (Laravel-style with Swoole support)
   - **Primary focus for all development**
   - High-performance with Swoole coroutine support
   - Comprehensive school management features
   - All new features should be implemented here

2. **Legacy Application** (`web-sch-12/` directory) - **FULLY DEPRECATED**: Laravel 12 with modular architecture
   - **NO NEW DEVELOPMENT** should occur in this application
   - **WILL BE REMOVED** in the next major release
   - Contains modules: ERaport, LaporanAnalitik, ManajemenSekolah, SistemMonetisasi
   - Only maintained for reference purposes

**CRITICAL**: All development efforts must be focused on the main application in the root directory. The `web-sch-12` directory is fully deprecated and will be completely removed. Any work done in the legacy application will be lost when it's removed.

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

## Security Guidelines
- Never commit real credentials, API keys, or sensitive data to the repository
- Use placeholder values in .env.example (e.g., your-server@your-ip instead of real server details)
- Keep sensitive configuration in environment-specific files not tracked by Git
