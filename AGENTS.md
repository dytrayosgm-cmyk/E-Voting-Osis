# E-Voting OSIS — Panduan Agen

Repositori ini berisi aplikasi E-Voting pemilihan Ketua & Wakil OSIS berbasis Laravel 13, Livewire 4, dan Tailwind CSS v4.

## Teknologi Utama

- **Backend**: PHP 8.3+, Laravel 13, Livewire 4
- **Frontend**: Blade, Alpine.js (via Livewire), Tailwind CSS v4, Vite
- **Database**: MySQL (default `evoting_osis`) atau SQLite
- **Test**: PHPUnit 12 (in-memory SQLite)

## Perintah Penting

```bash
# Install dependencies
composer install
npm install

# Setup database
php artisan migrate --seed

# Build frontend assets
npm run build

# Jalankan server
php artisan serve

# Jalankan test
php artisan test --compact
```

## Struktur Kunci

- `app/Livewire/Voter/VotingBooth.php` — Komponen bilik suara (verifikasi NIS + voting)
- `app/Livewire/Admin/Dashboard.php` — Dashboard admin (statistik, import siswa, reset)
- `app/Livewire/Admin/CandidateManager.php` — Kelola kandidat (CRUD + foto)
- `app/Http/Controllers/Admin/` — Auth admin, manajemen admin, export laporan
- `app/Models/` — Admin, AdminSession, Student, Candidate, Vote, Setting, SystemLog
- `routes/web.php` — Semua route (voter publik + admin terproteksi)
- `config/auth.php` — Guard `admin` dengan provider `admins`

## Catatan Penting

- `Student::$fillable` sengaja **tidak** menyertakan `has_voted` dan `voted_at`. Gunakan assignment langsung (`$student->has_voted = true; $student->save()`) bukan `$student->update([...])`.
- `Admin::$fillable` sengaja **tidak** menyertakan `last_login_at` dan `last_login_ip`. Gunakan assignment langsung.
- Jangan panggil `env()` di luar file `config/`. Gunakan `config()`.
- `FILESYSTEM_DISK` harus `public` agar foto kandidat bisa diakses browser.
- `SESSION_DRIVER` dan `CACHE_STORE` harus `file` (tidak ada migration session/cache).
