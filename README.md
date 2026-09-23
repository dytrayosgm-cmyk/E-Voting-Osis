# E-Voting OSIS

Sistem E-Voting pemilihan Ketua & Wakil OSIS berbasis web, dirancang untuk jaringan lokal sekolah (LAN/intranet). Sistem ini memungkinkan pelaksanaan pemilihan umum OSIS secara terdistribusi, cepat, akurat, dan transparan tanpa memerlukan koneksi internet eksternal.

Dibangun dengan stack modern berbasis PHP dan Laravel, sistem ini menyajikan antarmuka interaktif responsif melalui Livewire dan Tailwind CSS, serta dilengkapi mekanisme pengamanan transaksi database berlapis untuk memastikan integritas dan asas kerahasiaan suara (one student, one vote).

---

## Teknologi

| Komponen | Spesifikasi / Implementasi |
| --- | --- |
| **PHP** | `^8.3` |
| **Framework** | Laravel `^13.17` |
| **Komponen Reaktif** | Livewire `^4.4` |
| **Frontend** | Blade, Livewire, Alpine.js, Tailwind CSS v4, Vite |
| **Database** | SQLite (default rekomendasi lokal) atau MySQL |
| **Test Runner** | PHPUnit 12 |

---

## Arsitektur & Alur Data

Sistem memisahkan dua peran utama secara tegas: **Pemilih (Siswa)** yang berinteraksi melalui bilik suara digital publik tanpa akun login, dan **Panitia Pemilihan** yang mengendalikan jalannya pemilihan melalui Control Center terproteksi.

### Alur Siswa (Bilik Suara)

```text
[Buka /] ──> [Input NIS/NIK] ──> [Validasi NIS & Sesi] ──> [Tampil Kandidat Aktif]
                                                                     │
[Auto-Reset 4s] <── [Tampil Layar Sukses] <── [1 Transaksi DB: Vote] <┘
```

1. **Siswa membuka `/`** pada perangkat bilik suara.
2. **Masukkan NIS/NIK** sesuai yang terdaftar pada Daftar Pemilih Tetap (DPT).
3. **Sistem memeriksa NIS**, memastikan status `has_voted` bernilai `false`, dan memverifikasi status sesi voting dalam kondisi `OPEN`.
4. **Tampilkan kandidat aktif** beserta nomor urut, foto, nama, visi, dan misi.
5. **Pilih kandidat → konfirmasi** pilihan melalui dialog modal verifikasi.
6. **Eksekusi dalam 1 transaksi database atomik**:
   - Membuat record baru pada tabel `votes`.
   - Mengubah status siswa menjadi `has_voted = true` dan mencatat timestamp `voted_at`.
   - Meng-increment counter `vote_count` pada kandidat terkait.
   - Mencatat log audit aktivitas pada tabel `system_logs`.
7. **Layar sukses auto-reset 4 detik** untuk mengembalikan bilik suara ke keadaan awal sehingga siap digunakan oleh pemilih berikutnya.

### Alur Panitia (Control Center)

1. **Login via `/admin/login`** menggunakan guard autentikasi khusus `admin`.
2. **Pastikan sesi CLOSED** saat menyiapkan konfigurasi dan data awal pemilihan.
3. **Import DPT via CSV** atau kelola data kandidat (nomor urut, foto, nama, visi, dan misi).
4. **Buka sesi voting** ketika seluruh persiapan bilik suara telah selesai dan waktu pemungutan suara dimulai.
5. **Pantau dashboard real-time** dengan pembaruan otomatis (Livewire polling setiap 5 detik) untuk memantau total suara masuk, persentase partisipasi, klasemen suara sementara, kesehatan latensi database, dan 25 log keamanan terbaru.
6. **Tutup sesi** segera setelah batas waktu pemilihan berakhir untuk mengunci hak suara.
7. **Export laporan CSV** untuk rekapitulasi data kehadiran dan hasil pemilihan, serta lakukan pencadangan data (*backup*).

---

## Struktur Direktori

```text
app/
  Http/Controllers/Admin/       Controller login admin, manajemen admin, export laporan
  Livewire/Admin/               Dashboard dan manajemen kandidat
  Livewire/Voter/               Bilik suara dan proses voting
  Models/                       Admin, AdminSession, Student, Candidate, Vote, Setting, SystemLog
database/
  migrations/                   Skema tabel aplikasi
  seeders/                      Data awal admin, siswa contoh, kandidat contoh
resources/views/
  admin/                        Halaman login admin, kelola admin, ubah password
  layouts/                      Layout admin dan aplikasi
  livewire/                     Tampilan Livewire
routes/web.php                  Route voter dan admin
tests/                          Unit test dan feature test
public/build/                   Asset hasil build
storage/app/public/candidates/  Foto kandidat
```

---

## Model & Tabel

| Model | Tabel | Peran |
| --- | --- | --- |
| `Admin` | `admins` | Akun panitia, membedakan role `admin` dan `superadmin`. |
| `AdminSession` | `admin_sessions` | Tracking sesi login admin aktif untuk mencegah sesi ganda (*concurrent login*). |
| `Student` | `students` | Daftar pemilih tetap (DPT), NIS unik, nama, kelas, status `has_voted`, dan waktu memilih. |
| `Candidate` | `candidates` | Nomor urut, identitas calon ketua & wakil, foto, slogan, visi, misi, status aktif, dan counter suara. |
| `Vote` | `votes` | Satu record suara per siswa (menyimpan referensi kandidat, waktu, dan IP pengirim). |
| `Setting` | `settings` | Satu baris konfigurasi status sesi voting (`voting_session_status`). |
| `SystemLog` | `system_logs` | Audit trail lengkap seluruh aktivitas voting, autentikasi, dan aksi administratif. |

---

## Perlindungan Integritas Suara

Sistem menerapkan arsitektur perlindungan 3 lapis (*3-layer protection*) pada method `confirmVote()` untuk mencegah terjadinya pemilihan ganda (*double voting*) maupun kondisi perlombaan (*race conditions*):

```text
[Lapis 1] Flag Sesi Livewire + Cache::lock() (Application-level)
              │
              ▼
[Lapis 2] DB Transaction + lockForUpdate() (Pessimistic Row Lock)
              │
              ▼
[Lapis 3] Unique Constraint pada votes.student_id (Database-level)
```

1. **Application-Level Lock**:
   - Flag pemrosesan instan pada state Livewire (`$isSubmitting`) untuk memblokir klik ganda atau pengiriman form berulang dari sisi browser.
   - Pemanfaatan `Cache::lock("voting_lock_{$student->id}", 10)` berbasis ID siswa untuk memblokir request konkuren yang masuk dalam milidetik yang sama sebelum transaksi database dibuka.
2. **Database Transaction & Pessimistic Row Lock**:
   - Seluruh mutasi data dibungkus dalam blok `DB::transaction()`.
   - Menggunakan query `Student::where('id', $id)->lockForUpdate()->first()` untuk mengunci baris data siswa secara pesimistik (*pessimistic locking*), menjamin status `has_voted` diperiksa ulang secara mutlak sebelum record suara ditulis.
3. **Database-Level Protection**:
   - Skema tabel `votes` menerapkan indeks **Unique Constraint** pada kolom `student_id`. Jika terjadi anomali aplikasi sekalipun, database mesin secara langsung menolak penyimpanan suara kedua dengan integritas skema integritas referensial.

### Pengamanan Tambahan
- **Rate Limit Verifikasi NIS**: Dibatasi maksimal **15 percobaan per IP per 60 detik** untuk mencegah *brute-force* NIS pada bilik suara.
- **Rate Limit Login Admin**: Dibatasi maksimal **5 percobaan per IP per 60 detik** untuk memitigasi serangan tebak kredensial.
- **Single Active Session Admin**: Mencegah akun admin yang sama login di dua komputer berbeda secara simultan.
- **Audit Logging**: Setiap peristiwa kritis (`vote_success`, `duplicate_attempt`, `race_condition_blocked`, `admin_action`, `session_toggled`, `data_reset`) dicatat secara permanen di tabel `system_logs` bersama alamat IP dan rincian konteks.

---

## Route

| Method | URL | Nama | Akses |
| --- | --- | --- | --- |
| `GET` | `/` | `voter.booth` | Publik (Bilik Suara) |
| `GET` | `/admin/login` | `admin.login` | Guest Admin |
| `POST` | `/admin/login` | `admin.login` | Guest Admin |
| `POST` | `/admin/logout` | `admin.logout` | Admin Login |
| `GET` | `/admin/dashboard` | `admin.dashboard` | Admin Login |
| `GET` | `/admin/reports/export` | `admin.reports.export` | Admin Login |
| `GET` | `/admin/management` | `admin.management.index` | Superadmin |
| `GET` | `/admin/management/create` | `admin.management.create` | Superadmin |
| `POST` | `/admin/management` | `admin.management.store` | Superadmin |
| `GET` | `/admin/password` | `admin.password.form` | Admin Login |
| `POST` | `/admin/password` | `admin.password.update` | Admin Login |

---

## Prasyarat

Sebelum melakukan instalasi, pastikan lingkungan server/laptop memenuhi kebutuhan berikut:
- **PHP** versi `8.3` atau lebih tinggi (ekstensi: `pdo`, `pdo_sqlite` atau `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`).
- **Composer** versi `2.x`.
- **Node.js** (rekomendasi LTS v18+) & **npm**.
- **SQLite** (bawaan PHP) atau **MySQL 8.0+** / MariaDB.

---

## Instalasi

Jalankan perintah berikut secara berurutan di terminal:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

> **Catatan Pengembangan:** Pada terminal lain, jalankan perintah `npm run dev` untuk mengaktifkan Vite hot-reload saat memodifikasi tampilan antarmuka.

---

## Konfigurasi .env

### Konfigurasi SQLite (Default)

Sangat direkomendasikan untuk kemudahan instalasi di jaringan lokal sekolah tanpa memerlukan instalasi database server terpisah:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/ke/database/database.sqlite
SESSION_DRIVER=file
CACHE_STORE=file
FILESYSTEM_DISK=public
```

> [!NOTE]
> Pastikan direktori `database/` memiliki izin baca dan tulis (*writable*) oleh user web server.

### Konfigurasi MySQL (Opsional)

Jika ingin menggunakan MySQL/MariaDB:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=evoting_osis
DB_USERNAME=root
DB_PASSWORD=
```

> [!IMPORTANT]
> - `FILESYSTEM_DISK` harus bernilai `public` agar file foto kandidat yang diunggah dapat diakses publik melalui link `public/storage`.
> - `SESSION_DRIVER` dan `CACHE_STORE` harus bernilai `file` karena aplikasi ini tidak menggunakan tabel migrasi khusus database untuk sesi dan cache.

---

## Deployment di Jaringan LAN Sekolah

Sistem E-Voting OSIS didesain untuk berjalan sepenuhnya *offline* pada infrastruktur intranet sekolah (tanpa koneksi internet).

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

### Konsep Arsitektur LAN
- **1 Laptop Utama bertindak sebagai Server (Admin)**: Menjalankan aplikasi Laravel dan database.
- **Laptop/Komputer Voter bertindak sebagai Klien**: Hanya membuka web browser menuju alamat IP laptop server.
- Seluruh perangkat terhubung dalam satu jaringan Wi-Fi lokal atau Switch LAN sekolah yang sama.
- Tidak memerlukan koneksi internet aktif.

### Langkah-Langkah Deployment:
1. **Periksa Alamat IP Laptop Admin**:
   Buka terminal pada laptop server dan jalankan:
   ```bash
   hostname -I
   # atau
   ip addr
   ```
   *Contoh output IP: `192.168.1.100`*

2. **Sesuaikan Variabel Environment pada `.env`**:
   Tambahkan IP server ke dalam `APP_URL` dan daftar host yang diizinkan (`APP_ALLOWED_HOSTS`):
   ```dotenv
   APP_URL=http://192.168.1.100:8000
   APP_ALLOWED_HOSTS=localhost,127.0.0.1,192.168.1.100
   ```

3. **Jalankan Web Server**:
   Jalankan artisan serve dengan binding host ke seluruh antarmuka jaringan:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. **Akses dari Perangkat Bilik Suara**:
   Buka browser (Google Chrome, Firefox, dll.) pada laptop bilik suara dan arahkan ke alamat:
   ```text
   http://192.168.1.100:8000/
   ```
   Bilik suara kini siap digunakan oleh pemilih.

---

## Panduan Penggunaan

### Untuk Panitia

1. **Login ke Sistem**:
   Buka URL `/admin/login` melalui browser di komputer admin.
   - Username default: `admin`
   - Password default: `password123`
2. **Ganti Password Default**:
   **WAJIB** mengganti password akun default sebelum hari pelaksanaan melalui menu ubah password (`/admin/password`).
3. **Siapkan Data Pemilih Tetap (DPT)**:
   - Pastikan status sesi voting dalam kondisi **CLOSED**.
   - Buka menu dashboard, lakukan upload file CSV DPT yang berisi seluruh siswa yang berhak memilih.
4. **Siapkan Kandidat**:
   - Pastikan sesi voting dalam status **CLOSED**.
   - Buka menu Manajemen Kandidat, masukkan data calon ketua dan wakil, nomor urut unik (1–99), foto kandidat resmi, slogan, visi, dan misi.
5. **Buka Sesi Voting**:
   - Ketika bilik suara sudah siap dan jam pemilihan dimulai, klik tombol **Buka Sesi Voting** di dashboard.
6. **Pantau Dashboard Pemilihan**:
   - Pantau grafik partisipasi suara dan log sistem secara berkala tanpa perlu merefresh halaman (auto-refresh via polling 5 detik).
7. **Tutup Sesi & Export Laporan**:
   - Setelah waktu pemilihan habis, klik tombol **Tutup Sesi Voting**.
   - Klik tombol **Export Laporan (CSV)** untuk mengunduh rekapitulasi data perolehan suara resmi beserta timestamp dan audit log.

### Untuk Pemilih

1. Masuk ke bilik suara dan perhatikan layar yang menampilkan halaman utama.
2. Masukkan **NIS** atau **NIK** yang terdaftar pada kotak formulir yang tersedia.
3. Tekan tombol **Verifikasi & Lanjutkan**.
4. Pelajari informasi calon paslon: foto, visi, misi, dan program kerja.
5. Tekan tombol **Pilih Paslon Ini** pada kandidat yang diinginkan, kemudian tekan **Ya, Kirim Suara** pada dialog konfirmasi.
6. Tunggu hingga layar menampilkan notifikasi sukses. Layar akan melakukan **auto-reset dalam 4 detik** kembali ke halaman depan untuk pemilih berikutnya.

### Format CSV DPT

File CSV untuk import siswa wajib menyertakan baris tajuk (*header*) tepat dengan format berikut:

```csv
nis,name,class
2024001,Andi Pratama,XII IPA 1
2024002,Bunga Citra Lestari,XII IPA 1
```

> [!TIP]
> - Delimiter koma (`,`) maupun titik koma (`;`) didukung secara otomatis.
> - Kolom `nis` dan `name` bersifat wajib diisi. Kolom `class` opsional.
> - Duplikasi NIS dalam file yang sama akan ditolak secara atomik demi integritas data.

---

## Backup & Maintenance

### Backup Database SQLite

Lakukan pencadangan database secara berkala sebelum dan sesudah pelaksanaan voting:

```bash
cp database/database.sqlite database/backup-$(date +%Y%m%d-%H%M%S).sqlite
```

Untuk mencadangkan seluruh foto kandidat:

```bash
tar -czf storage/backup-candidates-$(date +%Y%m%d-%H%M%S).tar.gz storage/app/public/candidates
```

### Backup MySQL

Jika menggunakan MySQL:

```bash
mysqldump -u root -p evoting_osis > backup-evoting-$(date +%Y%m%d-%H%M%S).sql
```

### Reset Data Voting (Simulasi)

Fitur ini disediakan khusus untuk simulasi atau gladi bersih sebelum pemilihan sebenarnya:
1. Buka dashboard admin dan pastikan status sesi voting **CLOSED**.
2. Masuk ke bagian **Reset Voting**.
3. Ketikkan kata konfirmasi `RESET` dengan huruf kapital.
4. Tekan tombol konfirmasi reset.

> [!CAUTION]
> Tindakan ini **hanya menghapus data perolehan suara (tabel `votes`)** dan mengembalikan status siswa menjadi belum memilih (`has_voted = false`). Fitur ini **TIDAK** menghapus data master siswa maupun data kandidat yang telah disusun.

---

## Testing

Aplikasi dilengkapi unit dan feature test otomatis berbasis PHPUnit. Jalankan test suite melalui:

```bash
php artisan test --compact
```

Cakupan pengujian otomatis (*feature tests*):
- **Import CSV siswa**: Verifikasi parsing baris CSV dan penyimpanan data siswa baru.
- **Penolakan NIS duplikat**: Memastikan file CSV yang memuat NIS ganda ditolak secara keseluruhan.
- **Update siswa tanpa mereset status**: Pembaruan nama/kelas siswa tidak mereset status `has_voted` yang sudah ada.
- **Redirect guest admin**: Pengunjung tanpa autentikasi diarahkan ke halaman login saat mencoba mengakses dashboard.
- **Blokir login ganda admin**: Mencegah akun admin login secara bersamaan dari sesi yang berbeda.
- **Middleware domain security**: Menolak request dari domain/host yang tidak terdaftar dalam whitelist `APP_ALLOWED_HOSTS`.

---

## Batasan

- **Polling Dashboard**: Dashboard admin menggunakan mekanisme Livewire polling (5 detik) alih-alih WebSocket. Pendekatan ini sangat ringan dan ideal untuk jaringan lokal sekolah, namun disarankan membatasi jumlah akun panitia yang membuka dashboard aktif.
- **Kerahasiaan Laporan Export**: File CSV hasil export memuat data NIS, nama pemilih, kelas, waktu memilih, dan IP bilik suara. Simpan file ini di tempat yang aman dan perlakukan sebagai data rahasia.
- **Penyimpanan Foto**: File foto kandidat disimpan pada disk `public`. Pastikan perintah symlink `php artisan storage:link` sudah dieksekusi agar foto tampil normal di browser.
- **Fitur Reset Voting**: Tombol Reset Voting menghapus seluruh data suara yang telah terkumpul secara permanen. Pastikan hanya digunakan pada saat simulasi gladi bersih.

---

## Lisensi

Project ini dikembangkan untuk keperluan internal **SMK An-Nurmaniyah**. Hak cipta dilindungi undang-undang.
