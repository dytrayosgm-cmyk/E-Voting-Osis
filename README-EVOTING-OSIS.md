# E-Voting OSIS

Dokumen ini adalah dokumentasi teknis tambahan untuk aplikasi pemilihan Ketua dan Wakil OSIS berbasis Laravel dan Livewire.

## Ringkasan

E-Voting OSIS menyediakan dua area utama:

- **Bilik Suara** (`/`): halaman publik untuk siswa. Siswa memverifikasi NIS/NIK, melihat kandidat aktif, lalu mengirim satu suara.
- **Control Center Admin** (`/admin/login`): area panitia untuk mengelola sesi voting, kandidat, daftar pemilih, hasil, dan log keamanan.

Aplikasi tidak memakai akun siswa. Hak memilih ditentukan oleh NIS yang terdaftar pada tabel `students`. Akun terpisah pada tabel `admins` hanya digunakan panitia.

## Teknologi

| Komponen | Versi/implementasi |
| --- | --- |
| PHP | `^8.3` |
| Laravel | `^13.17` (runtime saat ini 13.30.1) |
| Livewire | `^4.4` |
| Frontend | Blade, Livewire, Alpine.js melalui Livewire, Tailwind CSS v4, Vite |
| Database default contoh | SQLite |
| Database runtime dapat digunakan | SQLite atau MySQL |
| Build asset | Vite |
| Test runner | PHPUnit 12 |

## Struktur Direktori Penting

```text
app/
  Http/Controllers/Admin/       Controller login admin dan export laporan
  Livewire/Admin/               Dashboard dan manajemen kandidat
  Livewire/Voter/               Bilik suara dan proses voting
  Models/                       Admin, Student, Candidate, Vote, Setting, SystemLog
database/
  migrations/                   Skema tabel aplikasi
  seeders/                      Data awal admin, siswa contoh, kandidat contoh
resources/views/
  admin/                        Halaman login admin
  layouts/                      Layout admin dan aplikasi
  livewire/                     Tampilan Livewire
routes/web.php                  Route voter dan admin
tests/                          Unit test dan feature test
public/build/                   Asset hasil build, diabaikan Git
storage/app/public/candidates/  Foto kandidat setelah kandidat dibuat
```

## Arsitektur dan Alur Data

### Alur siswa

1. Siswa membuka `/`.
2. Siswa memasukkan NIS/NIK.
3. Sistem memeriksa NIS, status `has_voted`, dan status sesi voting.
4. Sistem menampilkan kandidat dengan `is_active = true`.
5. Siswa memilih kandidat dan mengonfirmasi.
6. Dalam satu transaksi database, sistem membuat record `votes`, menandai siswa sudah memilih, menambah `vote_count`, dan mencatat log.
7. Layar sukses di-reset otomatis setelah beberapa detik agar siap digunakan pemilih berikutnya.

### Alur panitia

1. Login melalui `/admin/login` dengan guard `admin`.
2. Pastikan sesi **CLOSED** saat menyiapkan kandidat dan daftar siswa.
3. Import DPT atau kelola kandidat.
4. Buka sesi voting ketika persiapan selesai.
5. Pantau partisipasi, klasemen, kesehatan database, dan security log di dashboard.
6. Tutup sesi setelah pemilihan selesai.
7. Export laporan CSV dan simpan backup database serta file laporan secara aman.

## Komponen Backend

### Livewire

- `App\Livewire\Voter\VotingBooth`: verifikasi NIS, pemilihan kandidat, dan pencatatan suara.
- `App\Livewire\Admin\Dashboard`: statistik, toggle sesi, import siswa, reset data simulasi, dan log terbaru. Dashboard melakukan polling setiap 5 detik.
- `App\Livewire\Admin\CandidateManager`: tambah/edit/hapus kandidat dan upload foto.

### Controller

- `AdminAuthController`: login dan logout admin, rate limit lima percobaan per IP per menit.
- `ReportController`: export seluruh vote sebagai CSV secara streaming agar tidak memuat seluruh laporan ke memori.

### Model dan tabel

| Model | Tabel | Peran |
| --- | --- | --- |
| `Admin` | `admins` | Akun panitia, role `admin` atau `superadmin` |
| `Student` | `students` | Daftar pemilih, NIS unik, status sudah memilih |
| `Candidate` | `candidates` | Nomor urut, identitas, foto, visi, misi, status aktif, counter suara |
| `Vote` | `votes` | Satu record suara untuk satu siswa |
| `Setting` | `settings` | Satu baris konfigurasi status sesi voting |
| `SystemLog` | `system_logs` | Audit trail aktivitas voting dan admin |

Relasi penting:

- Satu `Student` memiliki maksimal satu `Vote`.
- Satu `Candidate` memiliki banyak `Vote`.
- `Vote.student_id` memiliki unique constraint sebagai perlindungan database terhadap vote ganda.
- `Candidate.vote_count` adalah counter cache. Sumber data suara tetap tabel `votes`.

## Perlindungan Integritas Suara

Proses `confirmVote()` memakai beberapa lapis perlindungan:

1. Flag pemrosesan pada sesi Livewire untuk menahan klik ganda.
2. `Cache::lock()` berdasarkan ID siswa untuk request bersamaan.
3. `lockForUpdate()` pada baris siswa di dalam transaksi.
4. Unique constraint pada `votes.student_id` sebagai perlindungan terakhir.

Verifikasi NIS juga dibatasi 15 percobaan per IP dalam 60 detik. Login admin dibatasi lima percobaan per IP dalam 60 detik. Kejadian penting dicatat di `system_logs`.

## Route

| Method | URL | Nama | Akses |
| --- | --- | --- | --- |
| GET | `/` | `voter.booth` | Publik |
| GET | `/admin/login` | `admin.login` | Guest admin |
| POST | `/admin/login` | `admin.login` | Guest admin |
| POST | `/admin/logout` | `admin.logout` | Admin login |
| GET | `/admin/dashboard` | `admin.dashboard` | Admin login |
| GET | `/admin/reports/export` | `admin.reports.export` | Admin login |

## Konfigurasi Environment

Salin `.env.example` menjadi `.env`, kemudian sesuaikan minimal:

```dotenv
APP_NAME="E-Voting OSIS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Untuk database SQLite
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/ke/database/database.sqlite

# Pilihan praktis untuk instalasi tanpa tabel tambahan
SESSION_DRIVER=file
CACHE_STORE=file
FILESYSTEM_DISK=public
```

Untuk produksi, gunakan `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` dengan HTTPS, dan `APP_KEY` yang dihasilkan oleh Laravel. Jangan commit `.env`, password, atau data pemilih ke repository.

Jika memakai MySQL, isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`. Pastikan engine database mendukung transaksi dan row locking.

## Perintah Pengembangan

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Pada terminal lain saat mengembangkan frontend, gunakan `npm run dev`. Script `composer run setup` juga tersedia untuk instalasi otomatis, tetapi tetap periksa konfigurasi database dan environment sebelum menjalankannya.

## Testing dan Build

```bash
php artisan test --compact
composer run test
npm run build
```

Test suite menggunakan SQLite in-memory melalui `phpunit.xml`. Test feature yang tersedia mencakup import CSV, penolakan NIS duplikat dalam file, dan pembaruan siswa tanpa menghapus status vote.

## Dokumen Operasional

Panduan untuk panitia, pemilih, instalasi, backup, deployment, dan maintenance tersedia di [DOKUMENTASI-INSTALASI-MAINTENANCE.md](DOKUMENTASI-INSTALASI-MAINTENANCE.md).

## Batasan yang Perlu Diketahui

- Tidak ada fitur reset password admin di aplikasi. Perubahan password perlu dilakukan melalui prosedur administrator/database yang aman.
- Reset Voting menghapus semua suara dan hanya dimaksudkan untuk simulasi/gladi bersih.
- Export laporan memuat NIS, nama, kelas, waktu memilih, dan IP address. Perlakukan file tersebut sebagai data rahasia.
- Dashboard memakai polling Livewire, bukan WebSocket. Ini sesuai untuk jaringan lokal sekolah, tetapi perlu pengujian tambahan untuk beban yang jauh lebih besar.
- File foto kandidat disimpan pada disk public dan memerlukan `php artisan storage:link`.