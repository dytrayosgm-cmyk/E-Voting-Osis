# E-Voting OSIS — SMK An-Nurmaniyah

[![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![Livewire Version](https://img.shields.io/badge/Livewire-4.x-pink.svg?style=flat-square&logo=livewire)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-v4-blue.svg?style=flat-square&logo=tailwindcss)](https://tailwindcss.com)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.3-777BB4.svg?style=flat-square&logo=php)](https://php.net)
[![PHPUnit Tests](https://img.shields.io/badge/Tests-49%20Passed%20(232%20assertions)-brightgreen.svg?style=flat-square&logo=php)](file:///opt/lampp/htdocs/evoting-osis/tests)
[![License](https://img.shields.io/badge/License-Internal%20SMK-slate.svg?style=flat-square)](LICENSE)

Sistem E-Voting pemilihan pengurus OSIS berbasis web modern, cepat, transparan, dan terdistribusi untuk jaringan lokal sekolah (LAN / intranet Wi-Fi) di **SMK An-Nurmaniyah Tangerang**. Sistem ini beroperasi 100% *offline-ready* tanpa membutuhkan koneksi internet eksternal saat hari pemungutan suara berlangsung.

Dibangun dengan **Laravel 13**, komponen reaktif **Livewire 4**, **Tailwind CSS v4**, dan **Vite 8**, sistem ini menyajikan antarmuka modern yang responsif dan dirancang dengan arsitektur **Dual-Mode Sistem Pemilihan** (Sistem Paslon Ketua-Wakil vs Sistem Kandidat Individu), sistem autentikasi ganda bilik suara (**NIS + Token 4 Karakter**), dan pemantauan DPT *real-time* multi-level.

> 📖 **Panduan Operasional & Penanganan Masalah Lengkap**:
> Untuk panduan langkah demi langkah mulai dari instalasi bagi pemula, setup jaringan LAN, gladi bersih, hingga troubleshooting hari-H, silakan baca [PANDUAN_LENGKAP_DAN_TROUBLESHOOTING.md](file:///opt/lampp/htdocs/evoting-osis/PANDUAN_LENGKAP_DAN_TROUBLESHOOTING.md) atau unduh berkas PDF gabungannya di [DOKUMENTASI_DAN_PANDUAN_LENGKAP_EVOTING_OSIS.pdf](file:///opt/lampp/htdocs/evoting-osis/DOKUMENTASI_DAN_PANDUAN_LENGKAP_EVOTING_OSIS.pdf).

---

## 🌟 Daftar Lengkap & Penjelasan Seluruh Fitur Aplikasi

Sistem memisahkan dua peran utama secara tegas dan terisolasi: **Panel Admin (Control Center)** untuk pengelolaan dan pemantauan panitia, serta **Bilik Suara Digital (Voter Booth)** untuk pemungutan suara siswa.

```text
┌────────────────────────────────────────────────────────────────────────┐
│                        SISTEM E-VOTING OSIS                            │
├───────────────────────────────────┬────────────────────────────────────┤
│     TAMPILAN ADMIN (CONTROL)      │      TAMPILAN VOTERS (BILIK)       │
├───────────────────────────────────┼────────────────────────────────────┤
│ • KPI Real-time & Status Sesi     │ • Sub-Bar Periode/Hari Pemilihan   │
│ • Golden Spotlight Winner Banner  │ • Langkah 1: Input NIS + Token     │
│ • Live Count & Proyeksi Jabatan   │ • Navigasi Keyboard Enter Cepat    │
│ • Monitoring DPT (Filter Kelas)   │ • Anti-Autofill & Rate Limiter     │
│ • Import & Hapus DPT Massal       │ • Langkah 2: Kartu Paslon/Individu │
│ • Dual-Mode Paslon vs Individu    │ • Accordion Misi & Pintasan Angka  │
│ • Periode Hari Pemilihan Dinamis  │ • Modal Konfirmasi Akhir           │
│ • Security Logs Immutable SHA-256 │ • Secret Ballot (Anonim Mutlak)    │
│ • Manajemen Admin & Multi-Sesi    │ • Layar Sukses & Auto-Reset 4 Detik│
│ • Beacon Auto-Logout Saat Tutup   │                                    │
│ • Export Berita Acara Resmi (CSV) │                                    │
└───────────────────────────────────┴────────────────────────────────────┘
```

---

### A. Tampilan & Fitur Panel Admin (Control Center)

Panel Admin diakses melalui alamat `/admin/login` dengan guard autentikasi terisolasi `admin`. Seluruh fitur dirancang untuk memberikan kendali penuh kepada panitia pemilihan:

#### 1. Header Navbar & Kontrol Sesi Cepat
- **Indikator Server Live**: Menampilkan latensi server secara real-time (`Server Live 0.1ms • WS Aktif`) sebagai penanda bahwa service backend beroperasi normal.
- **Tombol `+ Buat Akun Admin`**: Tombol pintas di header atas untuk mendaftarkan panitia baru tanpa harus berpindah ke menu lain.
- **Tombol `Logout` Langsung**: Mengakhiri sesi administratif secara aman dan mencabut token sesi aktif dari database.
- **Profil Admin**: Menampilkan nama lengkap, inisial avatar, dan username panitia yang sedang login.

#### 2. Kartu Metrik KPI Real-Time
Dashboard atas menyajikan 3 kartu metrik utama yang otomatis diperbarui via *Livewire Polling* setiap beberapa detik:
- **Total Suara Masuk**: Menampilkan jumlah suara sah yang telah masuk berbanding total DPT terdaftar, dilengkapi kalkulator sisa pemilih yang belum menyalurkan suara.
- **Partisipasi Siswa**: Menampilkan persentase kehadiran pemilih secara live berbanding kuorum sah pemilihan (50%).
- **Status Sesi Voting**: Menampilkan status sesi (`OPEN` / `CLOSED`) beserta durasi aktif pemilihan (misal: *"Dibuka 1 jam yang lalu"* atau *"Ditutup 2 hari yang lalu"*).

#### 3. Kontrol Pembukaan & Penutupan Sesi
- **Tombol Buka Sesi Voting**: Mengaktifkan seluruh bilik suara digital siswa di seluruh jaringan LAN sekolah.
- **Tombol Tutup Sesi Voting**: Mengunci bilik suara siswa sementara waktu (misal saat istirahat sholat/makan siang).
- **Tombol Akhiri Sesi & Umumkan Pemenang**: Menutup pemungutan suara secara permanen dan menetapkan pemenang resmi secara sah.
- **Tombol Buka Kembali Hasil Pemilihan (Reopen)**: Tombol darurat bagi panitia jika terdapat pemilihan susulan sebelum penetapan final.

#### 4. Golden Spotlight Winner Celebration Banner (Banner Pemenang Emas)
Ketika sesi pemilihan resmi diakhiri (`CLOSED / Finalized`), sistem secara otomatis memunculkan kartu selebrasi beraksen emas di puncak dashboard dengan animasi semburan *confetti* dinamis:
- **Jika Mode Paslon Aktif**:
  - Menampilkan 1 Paslon Pemenang Terpilih dengan suara terbanyak.
  - Foto Calon Ketua dan Calon Wakil berdampingan dengan *gold ring*.
  - Menampilkan nomor urut, nama lengkap, slogan, total suara terpilih, dan persentase kemenangan.
- **Jika Mode Kandidat Individu Aktif**:
  - **Penetapan Pengurus Inti Terpilih**: Sistem memproyeksikan **Peringkat 1 sebagai Ketua OSIS Terpilih** (badge hijau `KETUA`) dan **Peringkat 2 sebagai Wakil Ketua OSIS Terpilih** (badge biru `WAKIL`).
  - Foto Juara 1 dan Juara 2 ditampilkan berdampingan di sisi kiri banner dengan *gold ring*.
  - Judul utama mencantumkan keduanya: `No. XX [Nama Juara 1] (Ketua) & No. YY [Nama Juara 2] (Wakil)`.
  - Kotak statistik kanan memuat rincian perolehan suara Ketua dan Wakil secara terpisah dan proporsional.
  - **Sub-Grid Kandidat 3 dan 4**: Di bawah banner emas, terdapat sub-grid rapi yang menampilkan profil Kandidat Peringkat 3 dan Peringkat 4 beserta perolehan suaranya.
  - **Proteksi Tanpa Crash (Zero-Crash Guard)**: Sistem dirancang aman dari error *undefined offset* sekalipun suara masih 0 atau jumlah kandidat kurang dari 2.

#### 5. Klasemen Suara Real-Time (Live Count)
- **Quick Count Live Bar**: Menampilkan bar progres visual perolehan suara masing-masing kandidat yang bergerak secara halus sesuai persentase suara masuk.
- **Nomor Urut & Avatar**: Tampilan nomor urut bernomor tebal dan foto identitas kandidat.
- **Badge Proyeksi Jabatan Otomatis (Khusus Mode Individu)**:
  - Peringkat 1: Badge hijau `Peringkat 1 • Calon Ketua OSIS`
  - Peringkat 2: Badge biru `Peringkat 2 • Calon Wakil Ketua OSIS`
  - Peringkat 3: Badge ungu `Peringkat 3 • Kandidat Ketiga`
  - Peringkat 4: Badge abu-abu `Peringkat 4 • Kandidat Keempat`
  - Saat sesi ditutup/final, kata `"Calon"` otomatis berubah menjadi `"Terpilih"`.
- **Badge SHA-256**: Indikator enkripsi transaksi suara yang menjamin integritas penghitungan.

#### 6. Monitoring Pemilih (DPT Real-Time) & Filter Multi-Level
Tabel monitoring pemilih yang dirancang interaktif untuk memantau status setiap siswa secara langsung:
- **Pemisahan Tab Status**:
  - **Tab Belum Memilih**: Menampilkan daftar siswa yang belum memilih, lengkap dengan kolom **Token / Kode Unik** untuk dibagikan panitia.
  - **Tab Sudah Memilih**: Menampilkan siswa yang telah sukses mencoblos beserta waktu memilihnya.
- **Filter Angkatan / Tingkat**: Filter instan satu klik untuk siswa Kelas X (10), Kelas XI (11), atau Kelas XII (12).
- **Filter Kelas Spesifik**: Dropdown dinamis yang mendeteksi seluruh kelas terdaftar di DPT (misal: *XII RPL 1*, *XI AKL 2*).
- **Pencarian Cepat**: Pencarian real-time berdasarkan NIS atau Nama Siswa tanpa me-reload halaman.
- **Paginasi Baris Fleksibel**: Pilihan tampilan 10, 25, 50, atau 100 baris per halaman dengan navigasi halaman bernomor.
- **Tombol Unduh Siswa Belum Memilih (CSV)**: Ekspor cepat daftar siswa belum memilih untuk mempermudah petugas jemput bola ke ruang kelas.
- **Tombol Hapus Seluruh DPT**: Tombol merah khusus untuk membersihkan seluruh data DPT dan riwayat suara saat akan memulai siklus pemilihan baru, dilindungi modal konfirmasi keamanan ganda.

#### 7. Import Data Pemilih Tetap (DPT Siswa)
- **Upload CSV Massal**: Area upload file drag-and-drop untuk memasukkan ratusan hingga ribuan data siswa dalam hitungan detik.
- **Pembuatan Token 4 Karakter Otomatis**: Panitia tidak perlu membuat token manual; sistem otomatis menghasilkan Token / Kode Unik 4 karakter acak (kombinasi huruf kapital & angka) untuk setiap siswa saat CSV diimpor.
- **Download Template CSV**: Tersedia tombol download template resmi (`template-dpt-siswa.csv`) berformat `nis,name,class`.
- **Validasi Baris Atomik**: Mencegah duplikasi NIS dan menolak baris yang tidak lengkap demi integritas database.

#### 8. Manajemen Paslon & Kandidat (Dual-Mode)
- **Segmented Mode Switcher**: Tombol pengalih sistem pemilihan di header manajemen: **Mode Paslon** vs **Mode Kandidat Individu**.
- **Form Modal Tambah/Edit Dinamis**:
  - Pilihan radio tab: `[ Mode Paslon (Ketua & Wakil) | Mode Kandidat Individu ]`.
  - Jika memilih **Mode Paslon**: Menampilkan 2 kolom input untuk Calon Ketua dan Calon Wakil (Nama & Foto masing-masing).
  - Jika memilih **Mode Kandidat Individu**: Input Calon Wakil disembunyikan secara mulus; form beralih menjadi *full-width* (`col-span-2`) dengan label **"Nama Lengkap Kandidat"** dan **"Foto Kandidat"**.
  - Judul modal dan tombol simpan berganti dinamis (*"Simpan Paslon"* / *"Simpan Kandidat"*).
- **Pengaturan Periode / Hari Pemilihan Dinamis**:
  - Kolom khusus untuk mengatur teks periode (contoh: *"Periode 2025/2026"* atau *"Hari Pemilihan: 28 Oktober 2026"*).
  - Teks ini tersimpan di tabel `settings` dan otomatis muncul pada sub-navbar bilik suara siswa.
- **Kelengkapan Visi & Misi**:
  - Kolom Slogan / Tagline paslon.
  - Textarea Visi kepemimpinan.
  - Textarea Program Misi (1 baris = 1 poin misi, otomatis disimpan sebagai array JSON).
- **Proteksi Integritas & Fitur Override Lock**:
  - Saat sesi voting terbuka (`OPEN`), modifikasi kandidat otomatis dikunci untuk mencegah kecurangan.
  - Tersedia tombol **Buka Kunci Sementara (Override)** untuk situasi darurat perbaikan data di hari-H.
- **Tabel Daftar Kandidat**:
  - Menampilkan 1 avatar foto & badge `"Individu"` untuk mode kandidat, atau 2 foto berdampingan untuk mode paslon.
  - Modal pratinjau Visi & Misi lengkap.
  - Proteksi hapus: Kandidat yang sudah memiliki suara sah masuk **tidak dapat dihapus** guna melindungi integritas database hasil suara.

#### 9. System Security Logs (Audit Trail Immutable)
- Menampilkan rekaman peristiwa sistem secara real-time yang tidak dapat dimanipulasi (*immutable ledger*).
- Filter tab:
  - **Semua**: Seluruh riwayat sistem.
  - **Sesi**: Pembukaan, penutupan, dan finalisasi sesi.
  - **Suara**: Rekam pencoblosan suara siswa (`VOTE CAST`).
  - **Peringatan**: Deteksi brute-force, NIS tidak terdaftar, atau upaya vote ganda.

#### 10. Manajemen Akun Admin & Keamanan Multi-Sesi
- **CRUD Panitia**: Membuat akun admin baru dengan pembagian wewenang `Admin` dan `Superadmin`.
- **Fitur Ubah Password**: Panitia dapat memperbarui password akunnya sendiri kapan saja.
- **Proteksi Multi-Sesi (Concurrent Session Protection)**: Mencegah satu akun digunakan login di 2 perangkat berbeda secara bersamaan.
- **Fitur Auto-Logout via Beacon API**: Sesi admin otomatis dicabut saat browser/tab ditutup mendadak.
- **Fitur Paksa Masuk & Cabut Sesi Lain**: Opsi centang pada form login untuk membersihkan sesi menggantung.

#### 11. Danger Zone: Reset Voting & Suara
- Fasilitas reset data untuk simulasi / gladi bersih pemilihan.
- Dilindungi konfirmasi ketik teks: `RESET`.
- Mengembalikan suara ke angka 0 dan status siswa menjadi belum memilih, sementara **data DPT siswa dan data kandidat tetap aman**.

#### 12. Export Berita Acara & Rekapitulasi Resmi (CSV)
- Mengunduh berkas rekapitulasi resmi dengan format Berita Acara lengkap.
- Menggunakan encoding **UTF-8 BOM** sehingga langsung rapi dan terbaca sempurna saat dibuka di Microsoft Excel.
- **Format Adaptif Sesuai Mode**:
  - Jika Mode Paslon: Mencatat nama pasangan calon dan status pemenang.
  - Jika Mode Kandidat Individu: Mencatat Peringkat 1 sebagai `TERPILIH (KETUA OSIS)`, Peringkat 2 sebagai `TERPILIH (WAKIL KETUA OSIS)`, serta Peringkat 3 dan 4.
- Memuat audit log transaksi setiap suara masuk dari bilik suara digital (NIS, Nama, Kelas, Pilihan Kandidat, Waktu, dan IP Bilik).

---

### B. Tampilan & Fitur Bilik Suara Siswa (Voters Booth)

Bilik suara siswa diakses melalui URL utama `/` tanpa memerlukan login akun, menjamin proses pemungutan suara berlangsung cepat dan ramah bagi seluruh siswa:

#### 1. Sub-Navbar Identitas Pemilihan
- Menampilkan logo resmi SMK An-Nurmaniyah Tangerang.
- Menampilkan teks dinamis **Periode / Hari Pemilihan** yang disinkronkan langsung dari pengaturan admin.

#### 2. Langkah 1: Verifikasi Identitas & Token 4 Karakter
- **Input NIS Siswa**: Kotak input nomor induk siswa dengan format numerik.
- **Input Token 4 Karakter**: Kotak input kode unik 4 karakter (huruf kapital otomatis).
- **Keyboard-Friendly (Akses Super Cepat Tanpa Perlu Mouse)**:
  - Siswa mengetik NIS -> tekan tombol <kbd>Enter ↵</kbd> otomatis berpindah ke kotak Token.
  - Siswa mengetik Token -> tekan tombol <kbd>Enter ↵</kbd> langsung mengeksekusi tombol verifikasi.
- **Proteksi Anti-Manipulasi & Autocomplete Off**:
  - Atribut `autocomplete="off"`, `autocorrect="off"`, dan `data-lpignore="true"` aktif untuk mencegah browser memunculkan popup rekomendasi NIS siswa sebelumnya.
- **Rate Limiting Anti-Brute Force**: Maksimal 15 kali percobaan per menit per alamat IP untuk mencegah penebakan token.
- **Pemeriksaan Hak Suara**:
  - Memastikan NIS terdaftar di DPT.
  - Memastikan Token 4 karakter cocok.
  - Memastikan siswa belum pernah memilih (`has_voted = false`).
  - Memastikan sesi voting sedang berstatus `OPEN`.

#### 3. Langkah 2: Pilih Paslon / Kandidat
Setelah verifikasi berhasil, siswa masuk ke bilik suara virtual:
- **Kartu Identitas Terverifikasi**: Menampilkan nama lengkap siswa, kelas, dan badge hijau *Hak Suara Aktif*.
- **Tampilan Kartu Pilihan Paslon (Mode Paslon)**:
  - Menampilkan 2 foto berdampingan (Calon Ketua & Calon Wakil).
  - Badge nomor urut di sudut foto.
  - Label overlay elegan `KETUA` dan `WAKIL` di bagian bawah foto.
  - Slogan / Tagline paslon.
  - Kotak Visi kepemimpinan.
  - Accordion dropdown **Program Misi** yang dapat diklik untuk membaca poin-poin kerja.
- **Tampilan Kartu Pilihan Kandidat (Mode Kandidat Individu)**:
  - Menampilkan 1 foto figur kandidat berukuran besar proporsional (*aspect-ratio 16:10*).
  - Badge nomor urut dan label `KANDIDAT INDIVIDU`.
  - Nama tunggal kandidat.
  - Slogan, Visi, dan poin-poin Program Misi.
- **Pintasan Keyboard Pemilihan Calon**:
  - Siswa dapat menekan tombol angka keyboard <kbd>[1]</kbd>, <kbd>[2]</kbd>, dst. untuk langsung memilih kandidat tanpa perlu mengarahkan kursor mouse.

#### 4. Modal Konfirmasi Akhir (Pastikan Pilihan Anda)
- Dialog pop-up verifikasi akhir sebelum suara dicatat permanen ke database:
  - Menampilkan foto dan nomor urut kandidat yang dipilih.
  - Menampilkan nama kandidat terpilih.
  - Peringatan: *"Pilihan suara bersifat rahasia, langsung, dan tidak dapat diubah setelah dikirimkan"*.
- **Navigasi Keyboard Konfirmasi**:
  - Tekan tombol <kbd>Enter ↵</kbd> untuk mengirim suara.
  - Tekan tombol <kbd>Esc</kbd> untuk kembali jika ingin mengubah pilihan.

#### 5. Eksekusi 1 Transaksi Database Atomik & Kerahasiaan Pilihan (Secret Ballot)
Proses penyimpanan suara dijalankan dalam satu transaksi database atomik terlindungi:
- Menyimpan record suara ke tabel `votes`. **Token `unique_code` siswa TIDAK PERNAH disimpan di tabel `votes`** untuk menjamin kerahasiaan pilihan secara mutlak (*secret ballot*).
- Menandai data siswa: `$student->has_voted = true; $student->voted_at = now(); $student->save();`.
- Meng-increment counter perolehan suara kandidat terkait secara atomik.
- Mencatat log aktivitas ke `system_logs` secara rahasia tanpa membocorkan kandidat yang dipilih siswa.

#### 6. Layar Sukses & Auto-Reset 4 Detik
- Menampilkan tanda terima digital berupa animasi centang hijau besar: *"Suara Anda Berhasil Tercatat!"*.
- Menampilkan pesan ucapan terima kasih atas partisipasi siswa.
- **Auto-Reset Otomatis 4 Detik**: Layar otomatis kembali ke Langkah 1 (Verifikasi NIS) dalam waktu 4 detik, membersihkan seluruh state sesi untuk pemilih berikutnya.

---

## 🛠️ Spesifikasi Teknologi

| Komponen | Spesifikasi / Library | Keterangan |
| --- | --- | --- |
| **PHP** | `^8.3` (kompatibel PHP 8.4 & 8.5) | Engine backend utama |
| **Framework Backend** | Laravel `^13.17` | Arsitektur MVC & Eloquent ORM |
| **Komponen Reaktif** | Livewire `^4.4` | Antarmuka interaktif real-time tanpa reload |
| **Frontend Styling** | Tailwind CSS `v4` | Desain modern, responsive & utilities-first |
| **Typography** | Instrument Sans | Font modern berstandar UI internasional |
| **Asset Bundler** | Vite `^8.0` | Kompilasi asset CSS & JS super cepat |
| **Pilihan Database** | MySQL 8.0+ / MariaDB 10.4+ atau SQLite 3 | Mendukung transaksi atomik ACID |
| **Testing Engine** | PHPUnit 12 (49 Feature Tests, 232 Assertions) | 100% lulus uji otomatis |
| **Standar Koding** | Laravel Pint (PSR-12) | Standar gaya penulisan kode bersih |

---

## 🛡️ Perlindungan Integritas Suara (3-Layer Protection)

Sistem menerapkan arsitektur perlindungan 3 lapis (*3-layer integrity protection*) untuk menjamin asas *one student, one vote*:

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

## 📁 Struktur Direktori Proyek

```text
app/
  Http/
    Controllers/Admin/
      AdminAuthController.php       # Autentikasi admin, rate limiter, sesi tunggal
      AdminManagementController.php # CRUD panitia admin dan ubah password
      ReportController.php          # Export berita acara rekapitulasi suara (CSV)
    Middleware/
      Authenticate.php              # Pengarah cerdas guard 'admin'
      SecureDomainMiddleware.php    # Whitelist host/IP via APP_ALLOWED_HOSTS
  Livewire/
    Admin/
      Dashboard.php                 # Dashboard KPI, monitoring DPT, winner banner, security logs
      CandidateManager.php          # CRUD paslon/kandidat individu, upload foto, visi-misi
    Voter/
      VotingBooth.php               # Alur bilik suara (NIS + Token, pilih kandidat, konfirmasi)
  Models/
    Admin.php                       # Model panitia admin
    AdminSession.php                # Tracking sesi aktif admin
    Candidate.php                   # Model kandidat (paslon & individu)
    Setting.php                     # Konfigurasi sesi global & mode pemilihan
    Student.php                     # Data DPT siswa & status memilih
    SystemLog.php                   # Audit trail immutable pemilihan
    Vote.php                        # Record suara sah pemilih
database/
  migrations/                       # 8 berkas migrasi skema database resmi
  seeders/                          # Seeder akun admin awal, DPT contoh, dan kandidat
resources/
  css/app.css                       # Konfigurasi Tailwind CSS v4 & theme tokens
  views/
    admin/                          # Template blade panel admin
    layouts/                        # Master layout admin & bilik suara pemilih
    livewire/                       # View komponen reaktif Livewire
public/
  favicon.ico                       # Favicon resmi
  images/logo-smk.jpg               # Logo sekolah SMK An-Nurmaniyah
  storage/candidates/               # Symlink penyimpanan berkas foto kandidat
tests/
  Feature/                          # 49 automated feature tests (100% lulus)
```

---

## 🚀 Panduan Ringkas Instalasi (Localhost)

Jalankan perintah berikut di terminal:

```bash
# 1. Masuk ke direktori aplikasi
cd /opt/lampp/htdocs/evoting-osis

# 2. Pasang dependensi PHP dan Node.js
composer install
npm install

# 3. Salin berkas lingkungan dan generate Application Key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi Database (.env)
# Pastikan MySQL aktif (misal via XAMPP) dan database evoting_osis telah dibuat:
php artisan migrate --seed

# 5. Hubungkan direktori storage publik untuk foto kandidat
php artisan storage:link

# 6. Kompilasi asset antarmuka Vite
npm run build

# 7. Jalankan server lokal
php artisan serve
```

Akses melalui web browser:
- **Bilik Suara Pemilih**: `http://127.0.0.1:8000/`
- **Control Center Admin**: `http://127.0.0.1:8000/admin/login`
  - *Username default*: `admin`
  - *Password default*: `password123`

---

## 🧪 Pengujian Otomatis (*Automated Testing*)

Proyek ini telah dilengkapi dengan rangkaian pengujian fitur komprehensif menggunakan PHPUnit 12 dengan in-memory SQLite:

```bash
php artisan test --compact
```

### Hasil Test Suite Terkini:
```text
{"tool":"phpunit","result":"passed","tests":49,"passed":49,"assertions":232,"duration_ms":1225}
```

Daftar berkas pengujian:
- `tests/Feature/DualModeElectionTest.php`: Pengujian sistem mode paslon vs mode kandidat individu, banner spotlight pemenang, proyeksi jabatan live count, export CSV, dan bilik suara.
- `tests/Feature/CandidateManagerTest.php`: Pengujian switch mode pemilihan, CRUD kandidat paslon dan individu tanpa wakil, proteksi hapus kandidat yang telah punya suara, dan override lock.
- `tests/Feature/VotingFlowTest.php`: Pengujian verifikasi NIS + Token, secret ballot, pemblokiran vote ganda, dan auto-reset.
- `tests/Feature/AdminSecurityTest.php`: Pengujian proteksi multi-sesi, beacon auto-logout, whitelist IP domain, dan rate limiter.
- `tests/Feature/AdminManagementTest.php`: Pengujian hak akses superadmin, pembuatan admin, export CSV, dan reset simulasi.
- `tests/Feature/StudentImportTest.php`: Pengujian import CSV DPT massal, validasi delimiter, dan pencegahan duplikasi NIS.

---

## 📄 Hak Cipta & Lisensi

Aplikasi E-Voting OSIS ini dikembangkan untuk kebutuhan pemilihan pengurus OSIS di lingkungan **SMK An-Nurmaniyah Tangerang**. Dilindungi hak cipta untuk kepentingan internal sekolah.
