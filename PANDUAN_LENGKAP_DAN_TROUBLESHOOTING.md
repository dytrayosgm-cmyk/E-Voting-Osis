# PANDUAN PENGGUNAAN & PENANGANAN MASALAH (TROUBLESHOOTING)
## E-VOTING OSIS — SMK AN-NURMANIYAH TANGERANG

Buku panduan ini disusun sebagai pedoman operasional lengkap bagi **Panitia Pemilihan OSIS**, **Teknisi/Operator Server**, dan **Saksi** dalam menyelenggarakan Pemilihan Ketua & Wakil OSIS SMK An-Nurmaniyah menggunakan sistem E-Voting berbasis jaringan intranet lokal (LAN/Wi-Fi).

---

## DAFTAR ISI

1. [Prinsip & Arsitektur Sistem](#1-prinsip--arsitektur-sistem)
2. [Persiapan Teknis Pra-Hari H](#2-persiapan-teknis-pra-hari-h)
3. [Panduan Operasional Panitia (Control Center)](#3-panduan-operasional-panitia-control-center)
   - [3.1 Login ke Sistem](#31-login-ke-sistem)
   - [3.2 Mengganti Password Default](#32-mengganti-password-default)
   - [3.3 Mengelola Daftar Pemilih Tetap (DPT)](#33-mengelola-daftar-pemilih-tetap-dpt)
   - [3.4 Mengelola Kandidat Pasangan Calon](#34-mengelola-kandidat-pasangan-calon)
   - [3.5 Membuka Sesi Pemilihan](#35-membuka-sesi-pemilihan)
   - [3.6 Memantau Real-Time Monitor (Live Count & Logs)](#36-memantau-real-time-monitor-live-count--logs)
   - [3.7 Manajemen Akun Panitia (Khusus Superadmin)](#37-manajemen-akun-panitia-khusus-superadmin)
   - [3.8 Menutup Sesi & Mengunduh Laporan Resmi](#38-menutup-sesi--mengunduh-laporan-resmi)
4. [Panduan Pemilih (Siswa di Bilik Suara)](#4-panduan-pemilih-siswa-di-bilik-suara)
5. [Panduan Simulasi & Gladi Bersih (Reset Data)](#5-panduan-simulasi--gladi-bersih-reset-data)
6. [Panduan Penanganan Masalah & Handling Error](#6-panduan-penanganan-masalah--handling-error)
   - [6.1 Kendala Bilik Suara Siswa](#61-kendala-bilik-suara-siswa)
   - [6.2 Kendala Autentikasi & Akun Admin](#62-kendala-autentikasi--akun-admin)
   - [6.3 Kendala Import File CSV DPT](#63-kendala-import-file-csv-dpt)
   - [6.4 Kendala Foto & Data Kandidat](#64-kendala-foto--data-kandidat)
   - [6.5 Kendala Jaringan LAN & Database Server](#65-kendala-jaringan-lan--database-server)
7. [Cheatsheet Perintah Terminal Darurat (CLI)](#7-cheatsheet-perintah-terminal-darurat-cli)

---

## 1. PRINSIP & ARSITEKTUR SISTEM

Sistem E-Voting OSIS SMK An-Nurmaniyah dirancang berlandaskan asas **LUBER JURDIL** (Langsung, Umum, Bebas, Rahasia, Jujur, dan Adil) yang diwujudkan melalui teknologi informasi:

1. **One Student, One Vote**: Setiap siswa yang tercatat dalam Daftar Pemilih Tetap (DPT) hanya memiliki hak memilih tepat satu kali. Setelah memilih, status siswa terkunci secara permanen (`has_voted = true`).
2. **Kerahasiaan Suara**: Pilihan paslon disimpan ke tabel `votes` tanpa relasi langsung yang dapat dibuka kembali di layar publik bilik suara.
3. **Audit Trail SHA-256**: Seluruh transaksi pencoblosan dan aktivitas panitia dicatat ke dalam buku besar log keamanan (`system_logs`) yang tidak dapat diubah (*immutable*).
4. **Jaringan Lokal (Offline-First)**: Seluruh sistem berjalan pada jaringan lokal sekolah (Wi-Fi/LAN) tanpa membutuhkan koneksi internet publik, menjamin kelancaran pemilihan meski jaringan internet luar mengalami gangguan.

```text
┌─────────────────────────────────────────────────────────┐
│               LAPTOP SERVER UTAMA (ADMIN)               │
│ IP: 192.168.1.100 • Menjalankan Laravel & Database      │
└───────────────────────────┬─────────────────────────────┘
                            │
               [Router Wi-Fi / Switch LAN]
                            │
       ┌────────────────────┼────────────────────┐
       ▼                    ▼                    ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ Bilik Suara 1│     │ Bilik Suara 2│     │ Bilik Suara 3│
│ (Laptop/Tab) │     │ (Laptop/Tab) │     │ (Laptop/Tab) │
└──────────────┘     └──────────────┘     └──────────────┘
```

---

## 2. PERSIAPAN TEKNIS PRA-HARI H

Lakukan langkah instalasi dan pengujian minimal **1 hingga 2 hari sebelum pelaksanaan pemilihan**:

### 2.1 Kebutuhan Perangkat
- **1 Laptop Server (Admin)**: RAM minimal 4 GB, terpasang PHP 8.3+, Composer, Node.js, dan Git.
- **Laptop/Tablet Bilik Suara**: Minimal browser Google Chrome, Mozilla Firefox, atau Microsoft Edge versi modern.
- **1 Router Wi-Fi / Switch LAN**: Router lokal sekolah untuk menghubungkan laptop server dengan perangkat bilik suara.

### 2.2 Langkah Instalasi di Laptop Server
Buka terminal dan jalankan urutan perintah berikut:

```bash
# 1. Pindah ke direktori web server
cd /opt/lampp/htdocs/evoting-osis

# 2. Pasang pustaka PHP & JavaScript
composer install
npm install

# 3. Siapkan file konfigurasi environment
cp .env.example .env
php artisan key:generate

# 4. Buat database SQLite dan pasang data awal (seeder)
touch database/database.sqlite
php artisan migrate --seed

# 5. Hubungkan direktori foto publik
php artisan storage:link

# 6. Kompilasi asset frontend (Tailwind CSS v4 & Vite)
npm run build

# 7. Uji seluruh sistem menggunakan automated tests
php artisan test --compact
```
> Pastikan output test menampilkan: **25 tests passed (100%)**.

---

## 3. PANDUAN OPERASIONAL PANITIA (CONTROL CENTER)

### 3.1 Login ke Sistem
1. Buka browser pada laptop server, ketik alamat: `http://127.0.0.1:8000/admin/login` (atau menggunakan IP LAN jika diakses dari laptop panitia lain).
2. Masukkan akun default:
   - **Username**: `admin`
   - **Password**: `password123`
3. Klik tombol **Masuk ke Control Center**.

### 3.2 Mengganti Password Default
> [!IMPORTANT]
> **WAJIB** mengganti password akun default sebelum hari pemilihan demi keamanan hasil suara!

1. Klik avatar profil panitia di pojok kanan atas navbar, pilih **Ubah Password** (atau buka `/admin/password`).
2. Masukkan password saat ini (`password123`).
3. Masukkan password baru (minimal 8 karakter kombinasi huruf dan angka) dan ketik ulang untuk konfirmasi.
4. Klik **Simpan Password Baru**.
5. Sistem akan otomatis logout dan menampilkan pesan sukses. Login kembali menggunakan password baru Anda.

---

### 3.3 Mengelola Daftar Pemilih Tetap (DPT)

Pengelolaan pemilih dilakukan melalui kartu **Import Daftar Pemilih Tetap (DPT)** pada Dashboard:

#### A. Mengunduh Template CSV
1. Pada bagian Import DPT di dashboard, klik tombol **Download Template CSV Contoh**.
2. File `template-dpt-siswa.csv` akan otomatis terunduh ke komputer Anda.

#### B. Menyiapkan Data Siswa
Buka file CSV tersebut menggunakan Microsoft Excel atau Text Editor. Pastikan baris pertama berisi header:
```csv
nis,name,class
2024001,Andi Pratama,XII RPL 1
2024002,Bunga Citra Lestari,XII AKL 2
2024003,Citra Kirana,XI OTKP 1
```
- **nis**: Nomor Induk Siswa unik (wajib).
- **name**: Nama lengkap siswa (wajib).
- **class**: Kelas/Jurusan siswa (opsional, contoh: `XII RPL 1`).

> [!TIP]
> Saat menyimpan file dari Excel, pilih format **CSV (Comma delimited) (*.csv)**. Sistem mendukung pemisah koma (`,`) maupun titik koma (`;`).

#### C. Mengunggah File DPT
1. **Pastikan status sesi voting dalam kondisi CLOSED** (Import DPT diblokir saat sesi terbuka demi integritas suara).
2. Tarik dan letakkan (*drag & drop*) file CSV ke kotak abu-abu bertanda ikon awan, atau klik **Pilih file CSV**.
3. Klik tombol **Import Siswa**.
4. Sistem akan memvalidasi data secara otomatis. Jika berhasil, pesan hijau akan muncul menginformasikan jumlah siswa yang berhasil ditambahkan/diperbarui.

---

### 3.4 Mengelola Kandidat Pasangan Calon

Pengelolaan paslon dilakukan pada bagian **Manajemen Paslon** di Dashboard Admin:

#### A. Menambah Paslon Baru
1. Pastikan sesi voting **CLOSED** (atau aktifkan fitur *Override* jika terpaksa).
2. Klik tombol **+ Tambah Paslon**.
3. Isi data formulir:
   - **Nomor Urut**: Angka 1, 2, 3, dst.
   - **Nama Calon Ketua**: Nama lengkap calon ketua.
   - **Nama Calon Wakil**: Nama lengkap calon wakil.
   - **Foto Ketua & Wakil**: Unggah foto resmi paslon (format JPG/PNG maks 2 MB).
   - **Slogan / Tagline**: Contoh: `"Bersama Kita Berkarya, Mandiri dan Berprestasi"`.
   - **Visi**: Uraian visi kepemimpinan paslon.
   - **Program Misi**: Tuliskan poin-poin misi dengan menekan enter untuk setiap poin baru (1 baris = 1 poin misi).
   - **Tampilkan di Bilik Suara**: Centang agar muncul pada layar siswa.
4. Klik **Simpan Paslon**.

#### B. Melihat Detail Visi & Misi
- Pada tabel daftar paslon, klik tautan biru **Visi & Misi** untuk memunculkan modal pratinjau lengkap visi dan poin-poin program kerja paslon bersangkutan.

#### C. Proteksi Integritas & Fitur Override Lock
- Saat sesi pemilihan sedang dibuka (**OPEN**), tombol tambah, edit, dan hapus paslon dikunci secara otomatis dengan banner peringatan berwarna kuning.
- Jika terjadi situasi darurat (misal: perbaikan salah ketik nama paslon), klik tautan **Buka Kunci Sementara (Override)** pada banner kuning. Setelah selesai, klik **Kunci Kembali**.

---

### 3.5 Membuka Sesi Pemilihan
1. Pastikan seluruh bilik suara siswa telah terhubung dan menampilkan layar awal E-Voting.
2. Pada bagian kanan atas Dashboard Admin, klik tombol hijau **Buka Sesi Voting**.
3. Muncul modal konfirmasi: klik **Ya, Buka Sesi**.
4. Status pada kartu metrik akan berubah menjadi **OPEN (● AKTIF)**. Seluruh bilik suara siswa kini dapat menerima input NIS.

---

### 3.6 Memantau Real-Time Monitor (Live Count & Logs)
Dashboard dilengkapi pembaruan otomatis setiap 5 detik (*Livewire polling*):
- **Kartu 1 (Total Suara Masuk)**: Menampilkan jumlah suara masuk berbanding total DPT serta sisa suara yang belum memilih.
- **Kartu 2 (Partisipasi Siswa)**: Menampilkan persentase pemilih yang hadir dan status ketercapaian batas kuorum (50%).
- **Kartu 3 (Status Sesi Voting)**: Menampilkan durasi sesi telah berjalan sejak dibuka.
- **Kartu 4 (DB Transaction Health)**: Menampilkan kesehatan respon database dan latensi query (di bawah 50 ms bertanda hijau *Sehat*).
- **Klasemen Suara Real-Time**: Bar progres persentase perolehan masing-masing kandidat paslon secara live.
- **Monitoring Pemilih (DPT Real-Time)**:
  - Tab status: **Belum Memilih**, **Sudah Memilih**, dan **Semua**.
  - **Filter Angkatan / Tingkat**: Filter instan untuk siswa Kelas X (10), Kelas XI (11), atau Kelas XII (12).
  - **Filter Kelas & Pencarian**: Cari berdasarkan NIS, nama siswa, atau spesifik per kelas.
  - **Pengaturan Baris & Paginasi**: Tampilkan 5, 10, 25, 50, hingga 100 baris per halaman dengan kontrol navigasi halaman bernomor rapi.
  - **Unduh Siswa Belum Memilih (CSV)**: Tombol unduh cepat untuk mempermudah petugas jemput bola ke kelas-kelas.
  - **Hapus Seluruh DPT**: Tombol merah di header tabel untuk mengosongkan seluruh data DPT siswa beserta riwayat suara saat akan memulai siklus pemilihan baru (dilindungi konfirmasi modal dan hanya dapat dijalankan saat sesi voting CLOSED).
- **System Security Logs**: Riwayat audit real-time dengan filter:
  - Tab **Semua**: Seluruh kejadian sistem.
  - Tab **Sesi**: Pembukaan dan penutupan sesi voting.
  - Tab **Suara**: Rekam pencoblosan suara siswa (`VOTE CAST`).
  - Tab **Peringatan**: Percobaan login NIS tak terdaftar atau vote ganda.

---

### 3.7 Manajemen Akun Panitia (Khusus Superadmin)
1. Klik avatar profil di navbar atas, pilih **Kelola Admin** (`/admin/management`).
2. Klik tombol **Tambah Admin**.
3. Masukkan nama lengkap, username unik, password, dan tentukan Role:
   - **Admin**: Dapat memantau dashboard, membuka/menutup sesi, dan mengunduh laporan.
   - **Superadmin**: Memiliki seluruh hak akses admin ditambah wewenang membuat akun panitia baru.
4. Klik **Simpan Admin**.

---

### 3.8 Menutup Sesi & Mengunduh Laporan Resmi
1. Tepat pada batas akhir jam pemilihan, klik tombol merah **Tutup Sesi Voting** di kanan atas Dashboard.
2. Konfirmasi pada modal: klik **Ya, Tutup Sesi**.
3. Status berubah menjadi **CLOSED**. Bilik suara siswa seketika terkunci dan tidak dapat menerima suara baru.
4. Klik tombol **Export Laporan (CSV)**.
5. File laporan resmi `laporan-voting-osis-[tanggal].csv` akan terunduh. File ini memuat data berita acara lengkap: No, NIS, Nama Siswa, Kelas, Paslon yang Dipilih, Waktu Memilih, dan IP Bilik Suara.
6. Cetak laporan tersebut untuk ditandatangani oleh Ketua Panitia, Pembina OSIS, dan Saksi Paslon.

---

## 4. PANDUAN PEMILIH (SISWA DI BILIK SUARA)

Petugas jaga bilik suara mengarahkan siswa pemilih mengikuti langkah berikut:

```text
┌────────────────────────────────────────────────────────┐
│                        LANGKAH 1                       │
│ Siswa memasukkan NIS dan Token/Kode Unik 4 Karakter    │
│ Tekan tombol "Verifikasi & Lanjutkan"                  │
└───────────────────────────┬────────────────────────────┘
                            │ (NIS & Token Cocok, Belum Memilih)
                            ▼
┌────────────────────────────────────────────────────────┐
│                        LANGKAH 2                       │
│ Muncul Kartu Identitas Terverifikasi & Foto Paslon     │
│ Pelajari Visi, Misi, dan Slogan Paslon                 │
│ Tekan tombol "✓ Pilih Paslon Ini"                      │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                   KONFIRMASI AKHIR                     │
│ Muncul kotak dialog berisi foto paslon yang dipilih    │
│ Tekan tombol "✓ Ya, Kirim Suara"                       │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                      LAYAR SUKSES                      │
│ Muncul centang hijau tanda suara berhasil dicatat      │
│ Layar reset otomatis dalam 4 detik untuk siswa berikut │
└────────────────────────────────────────────────────────┘
```

1. **Langkah 1 (Input NIS & Token/Kode Unik 4 Karakter)**: Siswa mengetikkan NIS mereka dan Token/Kode Unik 4 karakter yang diberikan panitia pada formulir, lalu menekan tombol **Verifikasi & Lanjutkan**. Sistem memvalidasi bahwa NIS terdaftar di tabel `voters` dan `unique_code` cocok dengan NIS tersebut.
2. **Langkah 2 (Pilih Paslon)**: Sistem menampilkan kartu nama siswa dengan tanda `IDENTITAS TERVERIFIKASI` dan menampilkan kartu paslon nomor 1, 2, dst. Siswa dapat menekan tulisan **Program Misi** untuk membaca rincian program kerja.
3. **Pilih Paslon**: Siswa menekan tombol **Pilih Paslon Ini** pada kandidat pilihannya.
4. **Dialog Konfirmasi**: Muncul jendela kecil verifikasi akhir. Siswa menekan tombol **Ya, Kirim Suara**. Pilihan suara dicatat secara terpisah tanpa token guna menjamin asas rahasia (*secret ballot*).
5. **Selesai**: Layar menampilkan centang hijau: *"Suara Anda Berhasil Tercatat!"*. Siswa dipersilakan meninggalkan bilik suara. Layar akan kembali otomatis ke halaman awal dalam **4 detik**.

---

## 5. PANDUAN SIMULASI & GLADI BERSIH (RESET DATA)

Sebelum hari-H, panitia disarankan melakukan simulasi pencoblosan menggunakan akun siswa contoh. Setelah simulasi selesai, bersihkan seluruh data suara uji coba:

1. Pastikan sesi voting dalam status **CLOSED**.
2. Gulir ke bagian paling bawah Dashboard Admin menuju kotak merah **Danger Zone: Reset Voting & Suara**.
3. Klik tombol **Reset Voting & Suara**.
4. Pada jendela modal yang muncul, ketik kata konfirmasi: `RESET` (huruf kapital).
5. Klik **Reset Sekarang**.
6. **Hasil Reset**:
   - Seluruh suara di tabel `votes` dikosongkan (kembali 0 suara).
   - Status seluruh siswa di DPT dikembalikan menjadi belum memilih (`has_voted = false`).
   - Perolehan suara kandidat dikembalikan menjadi 0.
   - **Data master siswa (DPT) dan data profil kandidat TETAP AMAN dan TIDAK terhapus**.

---

## 6. PANDUAN PENANGANAN MASALAH & HANDLING ERROR

Berikut adalah daftar lengkap kemungkinan error, akar masalah, dan langkah perbaikannya:

### 6.1 Kendala Bilik Suara Siswa

#### Error: "NIS tidak ditemukan di Daftar Pemilih Tetap (DPT). Hubungi panitia jika ini keliru."
- **Penyebab**: Siswa salah mengetikkan angka NIS, atau nama siswa belum masuk ke dalam file CSV DPT yang diunggah panitia.
- **Solusi**:
  1. Periksa kartu pelajar siswa dan pastikan angka NIS yang diketik sudah benar.
  2. Jika memang belum terdaftar di DPT, panitia dapat menambahkan data siswa ke file CSV lalu mengunggah ulang di Dashboard saat sesi ditutup sementara.

#### Error: "NIS ini SUDAH melakukan pemilihan sebelumnya. Satu siswa hanya berhak memilih satu kali."
- **Penyebab**: NIS tersebut telah tercatat mengirimkan suara di database.
- **Solusi**:
  1. Petugas meja registrasi mencocokkan dengan daftar hadir fisik siswa.
  2. Buka menu **System Security Logs** di Dashboard Admin untuk memverifikasi jam berapa suara dari NIS tersebut masuk dan dari alamat IP mana. Sistem secara mutlak menolak pencoblosan kedua demi asas keadilan.

#### Error: "Sesi voting saat ini sedang ditutup oleh panitia."
- **Penyebab**: Siswa mencoba memasukkan NIS saat status sesi di Dashboard masih **CLOSED**.
- **Solusi**: Buka Dashboard Admin dan klik tombol **Buka Sesi Voting**.

#### Error: "Terlalu banyak percobaan. Coba lagi dalam X detik."
- **Penyebab**: Terjadi salah ketik NIS lebih dari 15 kali dalam 1 menit dari perangkat yang sama (*Brute-force protection rate limiter aktif*).
- **Solusi**: Tunggu hingga waktu hitung mundur detik habis, atau jalankan perintah reset cache pada server jika sangat mendesak:
  ```bash
  php artisan cache:clear
  ```

#### Layar Bilik Suara Macet / Freeze
- **Solusi**: Tekan tombol **F5** atau **Ctrl + R** pada keyboard komputer bilik suara untuk memuat ulang halaman. Status transaksi tidak akan hilang karena diamankan di database server.

---

### 6.2 Kendala Autentikasi & Akun Admin

#### Error: "Akun admin ini terdeteksi sedang aktif di sesi/tab lain..."
- **Penyebab**: Sistem mendeteksi akun admin masih tercatat aktif di browser/tab lain (*Concurrent session protection*). Ini biasanya terjadi jika tab ditutup mendadak tanpa menekan tombol Logout.
- **Solusi Praktis & Cepat**:
  1. Pada form login admin (`/admin/login`), **centang opsi `[✓] Paksa Masuk & Cabut Sesi Lain`**, lalu klik tombol **Masuk ke Control Center**. Sistem akan langsung mencabut sesi lama dan mengizinkan Anda masuk.
  2. Sistem juga secara otomatis mencabut sesi lama yang tidak ada aktivitas lebih dari 2 menit (*auto-expire*).
  3. Sistem telah dilengkapi fitur **Auto-Logout via Beacon API** yang otomatis keluar saat tab/jendela browser ditutup.
  4. (Alternatif darurat via terminal):
     ```bash
     php artisan tinker --execute="App\Models\AdminSession::whereNull('revoked_at')->update(['revoked_at' => now()]); echo 'Semua sesi admin lama berhasil dicabut.';"
     ```

#### Lupa Password Admin / Akun Terkunci
- **Solusi**: Buka terminal laptop server dan reset password akun admin secara instan menggunakan artisan tinker:
  ```bash
  php artisan tinker --execute="\$a = App\Models\Admin::where('username', 'admin')->first(); \$a->password = bcrypt('password123'); \$a->save(); echo 'Password berhasil direset ke password123';"
  ```

#### Error: "403 Forbidden" saat Membuka Halaman Admin
- **Penyebab**: Laptop admin mengakses melalui IP atau domain yang belum terdaftar di whitelist `APP_ALLOWED_HOSTS` pada file `.env`.
- **Solusi**: Buka file `.env` di server, tambahkan IP laptop admin ke baris `APP_ALLOWED_HOSTS`:
  ```dotenv
  APP_ALLOWED_HOSTS=localhost,127.0.0.1,192.168.1.100,192.168.1.105
  ```
  Lalu bersihkan cache konfigurasi:
  ```bash
  php artisan config:clear
  ```

---

### 6.3 Kendala Import File CSV DPT

#### Error: "Baris X: duplikasi NIS YYY dalam file CSV."
- **Penyebab**: Ada dua siswa atau lebih yang memiliki nomor NIS yang sama di dalam file CSV yang diunggah.
- **Solusi**: Buka file CSV di Excel, gunakan fitur *Conditional Formatting -> Highlight Duplicates* untuk menemukan NIS yang kembar, perbaiki NIS tersebut, simpan, lalu unggah ulang.

#### Error: "Daftar siswa hanya dapat diimpor saat sesi voting ditutup."
- **Penyebab**: Panitia mencoba mengunggah file DPT saat sesi voting berstatus **OPEN**.
- **Solusi**: Klik tombol **Tutup Sesi Voting** di dashboard terlebih dahulu sebelum melakukan import DPT.

#### Karakter Nama Siswa Rusak / Tanda Baca Aneh di Excel
- **Solusi**: Saat menyimpan file di Excel, pastikan memilih format **CSV UTF-8 (Comma delimited) (*.csv)**. Sistem telah dilengkapi UTF-8 BOM byte recorder untuk kompatibilitas penuh dengan Excel.

---

### 6.4 Kendala Foto & Data Kandidat

#### Foto Paslon Tidak Tampil di Bilik Suara Siswa (Ikon Gambar Rusak / 404)
- **Penyebab**: Symlink storage public Laravel belum terpasang atau konfigurasi filesystem disk salah.
- **Solusi**:
  1. Pastikan baris berikut ada di `.env`:
     ```dotenv
     FILESYSTEM_DISK=public
     ```
  2. Jalankan perintah symlink di terminal:
     ```bash
     php artisan storage:link
     ```
  3. Pastikan folder `storage/app/public/candidates/` memiliki izin akses baca.

#### Error: "Kandidat ini sudah memiliki suara masuk dan tidak dapat dihapus."
- **Penyebab**: Panitia mencoba menghapus paslon yang sudah memiliki perolehan suara sah di database. Hal ini sengaja dicegah demi integritas hasil suara.
- **Solusi**: Jika paslon mengundurkan diri di tengah jalan, ubah status paslon menjadi **Nonaktif** (hilangkan centang *Tampilkan di Bilik Suara*), jangan menghapus record kandidat.

---

### 6.5 Kendala Jaringan LAN & Database Server

#### Laptop Bilik Suara Tidak Dapat Membuka Halaman Server (`ERR_CONNECTION_TIMED_OUT` / `ERR_CONNECTION_REFUSED`)
- **Penyebab 1**: Server dijalankan hanya dengan `php artisan serve` tanpa opsi host binding ke seluruh IP jaringan.
  - **Solusi**: Matikan server dan jalankan ulang dengan binding `0.0.0.0`:
    ```bash
    php artisan serve --host=0.0.0.0 --port=8000
    ```
- **Penyebab 2**: Firewall pada laptop server memblokir koneksi masuk (*incoming port 8000*).
  - **Solusi Windows**: Buka *Windows Defender Firewall -> Allow an app through Firewall*, atau izinkan port 8000 TCP.
  - **Solusi Linux (UFW)**: Jalankan `sudo ufw allow 8000/tcp`.
- **Penyebab 3**: Laptop server dan bilik suara berada di jaringan Wi-Fi yang berbeda.
  - **Solusi**: Pastikan seluruh laptop terhubung ke SSID Wi-Fi lokal yang sama.

#### Error Database: "database is locked" (SQLite)
- **Penyebab**: File database SQLite sedang diakses bersamaan tanpa mode WAL.
- **Solusi**:
  1. Pastikan folder `database/` memiliki hak akses tulis (*write permissions*):
     ```bash
     chmod 775 database/ database/database.sqlite
     ```
  2. Aktifkan WAL (*Write-Ahead Logging*) mode via sqlite CLI:
     ```bash
     sqlite3 database/database.sqlite "PRAGMA journal_mode=WAL;"
     ```

---

## 7. CHEATSHEET PERINTAH TERMINAL DARURAT (CLI)

Simpan rangkuman perintah berikut untuk operator server saat bertugas di hari pemilihan:

```bash
# 1. Menjalankan Server LAN
php artisan serve --host=0.0.0.0 --port=8000

# 2. Membersihkan Seluruh Cache Aplikasi (Jika Tampilan Macet)
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 3. Compile Ulang Frontend
npm run build

# 4. Melakukan Backup Database SQLite Kilat
cp database/database.sqlite database/backup-$(date +%Y%m%d-%H%M%S).sqlite

# 5. Cek Total Suara & Siswa via Tinker
php artisan tinker --execute="echo 'Total DPT: ' . App\Models\Student::count() . ' | Total Suara: ' . App\Models\Vote::count() . PHP_EOL;"

# 6. Reset Sesi Admin yang Menggantung
php artisan tinker --execute="App\Models\AdminSession::whereNull('revoked_at')->update(['revoked_at' => now()]); echo 'Semua sesi admin lama berhasil ditutup.';"

# 7. Menjalankan Seluruh Pengujian Otomatis
php artisan test --compact
```

---

*Dokumen ini diterbitkan secara resmi untuk Panitia Pemilihan OSIS SMK An-Nurmaniyah Tangerang. Selamat melaksanakan pesta demokrasi sekolah dengan jujur, adil, aman, dan transparan!*
