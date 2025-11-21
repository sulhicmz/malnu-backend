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

## Security Guidelines

### 🚨 Critical Security Rules

- **NEVER** commit real credentials, API keys, passwords, or tokens
- **ALWAYS** use placeholder values in `.env.example` (e.g., `your-server@your-ip`)
- **KEEP** sensitive configuration in environment-specific files
- **USE** environment variables for all runtime secrets
- **VALIDATE** all user inputs and sanitize outputs
- **FOLLOW** secure coding practices (OWASP guidelines)

### Security Best Practices

1. **Input Validation**: Always validate and sanitize user inputs
2. **Database Security**: Use parameterized queries, avoid raw SQL
3. **Authentication**: Implement proper authentication and authorization
4. **Dependencies**: Keep dependencies updated and scan for vulnerabilities
5. **Error Handling**: Don't expose sensitive information in error messages

### Reporting Security Issues

If you discover a security vulnerability:
- **DO NOT** open a public issue
- **DO** follow our [Security Policy](SECURITY.md)
- **DO** report via private GitHub advisory or email
- **WILL** receive credit for responsible disclosure

### Code Review Security

During code reviews, pay attention to:
- Hard-coded secrets or credentials
- Insecure data handling
- Missing input validation
- Improper error handling
- Outdated dependencies

For detailed security information, see our [Security Policy](SECURITY.md).
