# E-Voting OSIS — SMK An-Nurmaniyah

Sistem E-Voting pemilihan Ketua & Wakil OSIS berbasis web, dirancang khusus untuk jaringan lokal sekolah (LAN/intranet) di **SMK An-Nurmaniyah Tangerang**. Sistem ini memungkinkan pelaksanaan pemilihan umum OSIS secara terdistribusi, cepat, akurat, dan transparan tanpa memerlukan koneksi internet eksternal.

Dibangun dengan stack modern berbasis PHP dan Laravel 13, sistem ini menyajikan antarmuka interaktif responsif melalui Livewire 4, Alpine.js, dan Tailwind CSS v4, serta dilengkapi mekanisme pengamanan transaksi database berlapis untuk memastikan integritas dan asas kerahasiaan suara (*one student, one vote*).

> 📖 **Dokumentasi Operasional Lengkap**: Untuk panduan langkah demi langkah pelaksanaan hari-H, tata cara panitia, panduan pemilih, dan penanganan error, silakan baca [PANDUAN_LENGKAP_DAN_TROUBLESHOOTING.md](file:///opt/lampp/htdocs/evoting-osis/PANDUAN_LENGKAP_DAN_TROUBLESHOOTING.md).

---

## Teknologi

| Komponen | Spesifikasi / Implementasi |
| --- | --- |
| **PHP** | `^8.3` |
| **Framework Backend** | Laravel `^13.17` |
| **Komponen Reaktif** | Livewire `^4.4` |
| **Frontend UI** | Blade, Alpine.js, Tailwind CSS v4, Instrument Sans, Vite `^8.0` |
| **Database** | SQLite (default rekomendasi lokal) atau MySQL 8.0+ |
| **Format Standar Kode** | Laravel Pint (PSR-12) |
| **Test Runner** | PHPUnit 12 (25 Feature & Unit Tests, 83 Assertions) |

---

## Arsitektur & Alur Data

Sistem memisahkan dua peran utama secara tegas: **Pemilih (Siswa)** yang berinteraksi melalui bilik suara digital publik tanpa akun login, dan **Panitia Pemilihan** yang mengendalikan jalannya pemilihan melalui Control Center terproteksi.

### Alur Siswa (Bilik Suara Digital)

```text
[Buka /] ──> [Langkah 1: Input NIS] ──> [Validasi NIS & Sesi] ──> [Langkah 2: Tampil Paslon]
                                                                            │
[Auto-Reset 4s] <── [Layar Sukses] <── [1 Transaksi DB: Vote & Log] <───────┘
```

1. **Siswa membuka `/`** pada perangkat bilik suara.
2. **Langkah 1 (Verifikasi Identitas)**: Masukkan NIS/NIK sesuai yang terdaftar pada Daftar Pemilih Tetap (DPT).
3. **Sistem memeriksa NIS**: Memastikan status `has_voted` bernilai `false`, dan memverifikasi status sesi voting dalam kondisi `OPEN`.
4. **Langkah 2 (Pilih Pasangan Calon)**: Menampilkan kartu identitas siswa terverifikasi dengan badge hak suara aktif, foto berdampingan Ketua & Wakil, nomor urut, slogan, visi, dan program misi paslon.
5. **Pilih Paslon → Dialog Konfirmasi**: Dialog verifikasi akhir memastikan siswa yakin sebelum suara dikirimkan.
6. **Eksekusi dalam 1 Transaksi Database Atomik**:
   - Menyimpan record baru pada tabel `votes` (terenkripsi dan mencatat hash SHA-256).
   - Menandai siswa bersangkutan dengan penugasan langsung: `$student->has_voted = true; $student->voted_at = now(); $student->save();`.
   - Meng-increment counter suara kandidat terkait.
   - Mencatat log audit aktivitas pada tabel `system_logs`.
7. **Layar Sukses & Auto-Reset 4 Detik**: Layar menampilkan tanda terima voting digital lalu melakukan auto-reset otomatis kembali ke halaman awal untuk pemilih berikutnya.

### Alur Panitia (Control Center / Admin)

1. **Login via `/admin/login`** menggunakan guard autentikasi khusus `admin`.
2. **Sesi Pengaturan Awal (Status CLOSED)**:
   - Download template CSV DPT langsung dari dashboard (`template-dpt-siswa.csv`).
   - Import DPT siswa secara massal dengan validasi baris atomik.
   - Kelola data paslon (nomor urut, foto ketua & wakil, slogan, visi, dan misi).
3. **Buka Sesi Voting**: Saat waktu pemungutan suara dimulai, panitia membuka sesi voting dengan satu klik.
4. **Monitoring Real-Time (Live Count & Logs)**:
   - Dashboard auto-refresh setiap 5 detik via Livewire polling.
   - 4 Kartu KPI: Total Suara Masuk, Persentase Partisipasi vs Kuorum 50%, Status Sesi, dan Latensi DB Health.
   - Klasemen Suara Real-Time dengan bar progres, ranking, dan badge enkripsi SHA-256.
   - Live Security Audit Trail dengan filter tab (`Semua`, `Sesi`, `Suara`, `Peringatan`).
   - **Monitoring Pemilih (DPT)**: Pantau daftar siswa yang belum dan sudah memilih secara real-time, cari per NIS/nama, filter per kelas, dan unduh daftar siswa belum memilih (CSV) untuk pemanggilan panitia di kelas.
5. **Akhiri Sesi & Pengumuman Pemenang**:
   - Klik **Akhiri Sesi & Umumkan Pemenang** untuk menyelesaikan pemungutan suara secara resmi.
   - Sistem akan langsung menampilkan **Kartu Pemenang Resmi (Golden Spotlight)** beraksen emas dengan foto Ketua & Wakil terpilih, persentase kemenangan, dan efek animasi selebrasi *confetti*.
6. **Export Berita Acara & Rekapitulasi**:
   - Klik **Export Laporan (CSV)** untuk mengunduh dokumen Berita Acara resmi lengkap dengan rekapitulasi suara riil per paslon dari tabel `votes`, statistik persentase kehadiran DPT, serta audit detail setiap suara masuk.

---

## Struktur Direktori

```text
app/
  Http/Controllers/Admin/
    AdminAuthController.php       Autentikasi admin, rate limit login, dan proteksi sesi tunggal
    AdminManagementController.php Manajemen akun admin & fitur ubah password
    ReportController.php          Export laporan perolehan suara streaming CSV (ramah memori)
  Http/Middleware/
    Authenticate.php              Redirect cerdas guard admin
    SecureDomainMiddleware.php    Whitelist domain/IP lokal via APP_ALLOWED_HOSTS
  Livewire/Admin/
    Dashboard.php                 Dashboard KPI, Live count, Security logs, Import DPT, Reset
    CandidateManager.php          CRUD kandidat paslon, upload foto, modal visi-misi, override lock
  Livewire/Voter/
    VotingBooth.php               Komponen bilik suara reaktif (verifikasi NIS, voting, auto-reset)
  Models/
    Admin.php, AdminSession.php, Student.php, Candidate.php, Vote.php, Setting.php, SystemLog.php
database/
  migrations/                     7 skema migrasi tabel aplikasi
  seeders/                        Seeder akun admin default, 8 siswa DPT, dan 2 paslon resmi
resources/
  css/app.css                     Konfigurasi Tailwind CSS v4 dan Instrument Sans typography
  views/
    admin/auth/login.blade.php    Halaman login Control Center dengan logo sekolah
    admin/management/             Manajemen admin, form tambah admin, dan form ubah password
    layouts/admin.blade.php       Top navbar admin: Logo SMK, status Server Live, profil AD
    layouts/app.blade.php         Layout bilik suara pemilih dengan favicon sekolah
    livewire/admin/               View dashboard KPI dan manajemen paslon
    livewire/voter/               View bilik suara digital (Langkah 1 & Langkah 2)
public/
  favicon.ico                     Favicon resmi SMK An-Nurmaniyah
  images/logo-smk.jpg             Logo sekolah SMK An-Nurmaniyah resolusi tinggi
  storage/candidates/             Symlink penyimpanan foto calon Ketua & Wakil OSIS
routes/web.php                    Semua route bilik suara (publik) dan control center (auth guard)
tests/Feature/                    25 automated feature tests (100% lulus)
```

---

## Model & Tabel

| Model | Tabel | Peran & Catatan Penting |
| --- | --- | --- |
| `Admin` | `admins` | Akun panitia (`admin` / `superadmin`). Field `last_login_at` dan `last_login_ip` diisi via direct assignment. |
| `AdminSession` | `admin_sessions` | Tracking sesi login aktif untuk mencegah satu akun login di dua perangkat sekaligus. |
| `Student` | `students` | DPT siswa, NIS unik, nama, kelas. Field `has_voted` dan `voted_at` diisi via direct assignment. |
| `Candidate` | `candidates` | Nomor urut, identitas ketua & wakil, foto, slogan, visi, misi, status aktif. Field `vote_count` diisi via `increment()`. |
| `Vote` | `votes` | Satu record suara per siswa (menyimpan referensi kandidat, waktu, dan IP bilik suara). |
| `Setting` | `settings` | Konfigurasi status sesi voting (`is_voting_open`, `opened_at`, `closed_at`, `is_finalized`, `finalized_at`). |
| `SystemLog` | `system_logs` | Audit trail immutable seluruh aktivitas pemilihan, verifikasi NIS, dan aksi administratif. |

---

## Perlindungan Integritas Suara

Sistem menerapkan arsitektur perlindungan 3 lapis (*3-layer protection*) pada proses voting:

```text
[Lapis 1] Flag Sesi Livewire ($isProcessing) + Cache::lock() (Application-level)
                                  │
[Lapis 2] DB Transaction + Pessimistic Lock (lockForUpdate pada Student & Candidate)
                                  │
[Lapis 3] Unique Constraint pada Database (votes.student_id UNIQUE)
```

1. **Lapis 1 — Application Lock**: Flag `$isProcessing` di level Livewire dan `Cache::lock()` mencegah klik ganda atau request paralel dari browser yang sama.
2. **Lapis 2 — Database Pessimistic Lock**: Menggunakan `DB::transaction()` dan `Student::lockForUpdate()` untuk memastikan status pemilih diverifikasi secara atomik tepat sebelum record suara dibuat.
3. **Lapis 3 — Database Unique Constraint**: Constraint unik pada kolom `votes.student_id` menjamin bahwa satu siswa secara fisik tidak dapat memiliki lebih dari satu baris suara pada database.

---

## Prasyarat Sistem

- **PHP 8.3+** dengan ekstensi `pdo_sqlite` (atau `pdo_mysql`), `mbstring`, `fileinfo`, `gd`/`imagick`, `curl`.
- **Composer 2.x**
- **Node.js 18+** & npm
- **Web Browser Modern** (Chrome, Edge, Firefox, Safari)

---

## Instalasi & Menjalankan di Localhost

Jalankan perintah berikut secara berurutan di terminal:

```bash
# 1. Masuk ke direktori project
cd /opt/lampp/htdocs/evoting-osis

# 2. Install dependensi PHP dan Node.js
composer install
npm install

# 3. Salin environment dan generate key
cp .env.example .env
php artisan key:generate

# 4. Pastikan layanan MySQL aktif (misal di XAMPP / LAMPP: sudo /opt/lampp/lampp startmysql)
# Database default: evoting_osis (MySQL localhost:3306)
# Jalankan migrasi dan seeder awal:
php artisan migrate --seed

# (Alternatif jika ingin SQLite: ganti DB_CONNECTION=sqlite di .env dan buat database/database.sqlite)

# 5. Hubungkan penyimpanan storage publik (untuk foto kandidat)
php artisan storage:link

# 6. Build asset frontend (Tailwind CSS v4 & Vite)
npm run build

# 7. Jalankan server lokal
php artisan serve
```

Aplikasi kini dapat dibuka di browser:
- **Bilik Suara Pemilih**: `http://127.0.0.1:8000/`
- **Control Center Admin**: `http://127.0.0.1:8000/admin/login`
  - *Username default*: `admin`
  - *Password default*: `password123`

---

## Konfigurasi Jaringan LAN Sekolah (Hari-H)

Sistem didesain untuk berjalan sepenuhnya *offline* pada jaringan intranet sekolah:

```text
┌────────────────────────────────────────┐
│         Laptop Server (Admin)          │
│ IP: 192.168.1.100 (Contoh)             │
│ php artisan serve --host=0.0.0.0       │
└──────────────────┬─────────────────────┘
                   │
           [Router Wi-Fi / Switch LAN]
                   │
       ┌───────────┴───────────┐
       ▼                       ▼
┌──────────────┐        ┌──────────────┐
│ Bilik Suara 1│        │ Bilik Suara 2│
│ Browser:     │        │ Browser:     │
│ 192.168.1.100│        │ 192.168.1.100│
└──────────────┘        └──────────────┘
```

1. **Cek Alamat IP Laptop Admin**:
   ```bash
   hostname -I
   # misal didapat IP: 192.168.1.100
   ```
2. **Sesuaikan File `.env`**:
   ```dotenv
   APP_URL=http://192.168.1.100:8000
   APP_ALLOWED_HOSTS=localhost,127.0.0.1,192.168.1.100
   ```
3. **Jalankan Server dengan Binding ke Semua Antarmuka**:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
4. **Akses dari Laptop Bilik Suara Siswa**:
   Buka browser di bilik suara dan ketik: `http://192.168.1.100:8000/`

---

## Pengujian Otomatis (*Automated Testing*)

Proyek ini dilengkapi dengan test suite lengkap berbasis PHPUnit 12 yang mencakup seluruh alur krusial:

```bash
php artisan test --compact
```

### Hasil Test Suite Terkini:
```text
Pass: 25 tests, 83 assertions (100% Lulus)
```

Daftar berkas pengujian:
- **`tests/Feature/VotingFlowTest.php`**: Alur lengkap bilik suara, update status `has_voted`, pencegahan vote ganda, dan blokir vote saat sesi ditutup.
- **`tests/Feature/AdminSecurityTest.php`**: Redirect tamu admin, blokir login ganda dari perangkat lain, proteksi domain whitelist, dan pencatatan IP/waktu login.
- **`tests/Feature/AdminManagementTest.php`**: Hak akses superadmin, pembuatan admin baru, ganti password, export CSV, buka/tutup sesi, download template CSV DPT, dan reset voting simulasi.
- **`tests/Feature/CandidateManagerTest.php`**: CRUD paslon, proteksi penghapusan kandidat yang sudah memiliki suara, proteksi penguncian data saat sesi terbuka, dan fitur override lock.
- **`tests/Feature/StudentImportTest.php`**: Validasi file CSV DPT, deteksi delimiter otomatis, dan penolakan duplikasi NIS atomik.

---

## Lisensi & Hak Cipta

Aplikasi ini dikembangkan untuk keperluan pemilihan Ketua & Wakil OSIS di **SMK An-Nurmaniyah Tangerang**. Dilindungi hak cipta untuk kepentingan internal sekolah.
