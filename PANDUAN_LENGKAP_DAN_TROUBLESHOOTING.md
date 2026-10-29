# BUKU PANDUAN PENGGUNAAN, INSTALASI & PENANGANAN MASALAH (TROUBLESHOOTING)
## SISTEM E-VOTING OSIS — SMK AN-NURMANIYAH TANGERANG

Buku panduan ini disusun secara komprehensif, terstruktur, dan ramah bagi **Pemula IT**, **Panitia Pemilihan OSIS**, **Teknisi / Operator Komputer**, serta **Saksi**. Panduan ini mencakup seluruh tahapan dari nol: persiapan alat, unduh berkas, instalasi, konfigurasi jaringan intranet lokal (LAN/Wi-Fi), pengoperasian seluruh fitur terbaru (termasuk *Dual-Mode: Paslon vs Kandidat Individu*, autentikasi NIS + Token 4 Karakter, dan monitoring DPT), hingga prosedur pemeliharaan dan penanganan masalah saat hari pemungutan suara.

---

## DAFTAR ISI

1. [Prinsip Dasar & Konsep Sistem E-Voting](#1-prinsip-dasar--konsep-sistem-e-voting)
2. [Aplikasi & Alat (Tools) yang Diperlukan](#2-aplikasi--alat-tools-yang-diperlukan)
   - [2.1 Perangkat Keras (Hardware)](#21-perangkat-keras-hardware)
   - [2.2 Perangkat Lunak (Software & Tools)](#22-perangkat-lunak-software--tools)
   - [2.3 Cara Mengecek Kesiapan Tools di Komputer](#23-cara-mengecek-kesiapan-tools-di-komputer)
3. [Tahapan Mendapatkan Berkas Aplikasi (Source Code)](#3-tahapan-mendapatkan-berkas-aplikasi-source-code)
   - [Opsi A: Download File ZIP dari Google Drive](#opsi-a-download-file-zip-dari-google-drive)
   - [Opsi B: Mengunduh / Clone Melalui Git / GitHub](#opsi-b-mengunduh--clone-melalui-git--github)
4. [Panduan Instalasi & Konfigurasi Aplikasi dari Nol](#4-panduan-instalasi--konfigurasi-aplikasi-dari-nol)
   - [Langkah 1: Masuk ke Folder Project](#langkah-1-masuk-ke-folder-project)
   - [Langkah 2: Memasang Dependensi PHP (Composer)](#langkah-2-memasang-dependensi-php-composer)
   - [Langkah 3: Memasang Dependensi Frontend (NPM)](#langkah-3-memasang-dependensi-frontend-npm)
   - [Langkah 4: Konfigurasi File Environment (.env) & App Key](#langkah-4-konfigurasi-file-environment-env--app-key)
   - [Langkah 5: Konfigurasi & Migrasi Database](#langkah-5-konfigurasi--migrasi-database)
   - [Langkah 6: Menghubungkan Folder Penyimpanan Foto (Storage Link)](#langkah-6-menghubungkan-folder-penyimpanan-foto-storage-link)
   - [Langkah 7: Kompilasi Asset Tampilan (Vite Build)](#langkah-7-kompilasi-asset-tampilan-vite-build)
   - [Langkah 8: Verifikasi Uji Sistem (Automated Tests)](#langkah-8-verifikasi-uji-sistem-automated-tests)
   - [Langkah 9: Menjalankan Server Lokal](#langkah-9-menjalankan-server-lokal)
5. [Pengaturan Jaringan Intranet LAN / Wi-Fi Sekolah (Hari-H)](#5-pengaturan-jaringan-intranet-lan--wi-fi-sekolah-hari-h)
   - [5.1 Topologi Jaringan Sederhana](#51-topologi-jaringan-sederhana)
   - [5.2 Mengetahui Alamat IP Komputer Server](#52-mengetahui-alamat-ip-komputer-server)
   - [5.3 Menyesuaikan Whitelist IP di File .env](#53-menyesuaikan-whitelist-ip-di-file-env)
   - [5.4 Menjalankan Server untuk Seluruh Jaringan LAN](#54-menjalankan-server-untuk-seluruh-jaringan-lan)
   - [5.5 Menghubungkan Komputer Bilik Suara](#55-menghubungkan-komputer-bilik-suara)
6. [Panduan Penggunaan Lengkap Admin / Panitia (Control Center)](#6-panduan-penggunaan-lengkap-admin--panitia-control-center)
   - [6.1 Login, Proteksi Sesi & Fitur Auto-Logout](#61-login-proteksi-sesi--fitur-auto-logout)
   - [6.2 Mengubah Password Admin](#62-mengubah-password-admin)
   - [6.3 Manajemen Akun Panitia (Admin & Superadmin)](#63-manajemen-akun-panitia-admin--superadmin)
   - [6.4 Mengatur Periode / Hari Pemilihan Dinamis](#64-mengatur-periode--hari-pemilihan-dinamis)
   - [6.5 Dual-Mode Sistem Pemilihan: Paslon vs Kandidat Individu](#65-dual-mode-sistem-pemilihan-paslon-vs-kandidat-individu)
   - [6.6 Mengelola Daftar Pemilih Tetap (DPT Siswa) & Token](#66-mengelola-daftar-pemilih-tetap-dpt-siswa--token)
   - [6.7 Membuka Sesi Pemungutan Suara](#67-membuka-sesi-pemungutan-suara)
   - [6.8 Monitoring Pemilih DPT Real-Time](#68-monitoring-pemilih-dpt-real-time)
   - [6.9 Klasemen Live Count & Badge Proyeksi Jabatan](#69-klasemen-live-count--badge-proyeksi-jabatan)
   - [6.10 Mengakhiri Sesi & Pengumuman Juara (Golden Spotlight)](#610-mengakhiri-sesi--pengumuman-juara-golden-spotlight)
   - [6.11 Export Dokumen Berita Acara Rekapitulasi (CSV)](#611-export-dokumen-berita-acara-rekapitulasi-csv)
7. [Panduan Penggunaan untuk Siswa (Pemilih di Bilik Suara)](#7-panduan-penggunaan-untuk-siswa-pemilih-di-bilik-suara)
   - [Langkah 1: Input NIS & Token 4 Karakter (Keyboard-Friendly)](#langkah-1-input-nis--token-4-karakter-keyboard-friendly)
   - [Langkah 2: Memilih Paslon / Kandidat](#langkah-2-memilih-paslon--kandidat)
   - [Langkah 3: Konfirmasi Akhir Suara (Secret Ballot)](#langkah-3-konfirmasi-akhir-suara-secret-ballot)
   - [Langkah 4: Tanda Terima Digital & Auto-Reset 4 Detik](#langkah-4-tanda-terima-digital--auto-reset-4-detik)
8. [Panduan Pemeliharaan (Maintenance) & Backup Sistem](#8-panduan-pemeliharaan-maintenance--backup-sistem)
   - [8.1 Prosedur Simulasi / Gladi Bersih & Reset Data](#81-prosedur-simulasi--gladi-bersih--reset-data)
   - [8.2 Prosedur Backup Database](#82-prosedur-backup-database)
   - [8.3 Prosedur Backup Foto Kandidat](#83-prosedur-backup-foto-kandidat)
   - [8.4 Prosedur Pembersihan Cache Aplikasi](#84-prosedur-pembersihan-cache-aplikasi)
9. [Penanganan Masalah Lengkap (Troubleshooting & Error Handling)](#9-penanganan-masalah-lengkap-troubleshooting--error-handling)
   - [9.1 Kendala Bilik Suara Siswa](#91-kendala-bilik-suara-siswa)
   - [9.2 Kendala Login & Akun Admin](#92-kendala-login--akun-admin)
   - [9.3 Kendala Import File CSV DPT](#93-kendala-import-file-csv-dpt)
   - [9.4 Kendala Foto Kandidat Tidak Tampil](#94-kendala-foto-kandidat-tidak-tampil)
   - [9.5 Kendala Jaringan LAN & Akses Komputer Bilik](#95-kendala-jaringan-lan--akses-komputer-bilik)
   - [9.6 Kendala Database (MySQL / SQLite)](#96-kendala-database-mysql--sqlite)
10. [Cheatsheet Perintah Terminal Darurat (CLI)](#10-cheatsheet-perintah-terminal-darurat-cli)

---

## 1. PRINSIP DASAR & KONSEP SISTEM E-VOTING

Sistem E-Voting SMK An-Nurmaniyah dibangun berdasarkan asas pemilihan **LUBER JURDIL** (Langsung, Umum, Bebas, Rahasia, Jujur, dan Adil) yang ditopang oleh teknologi modern:

1. **Jaringan Lokal (100% Offline-Ready)**: Seluruh sistem berjalan pada jaringan intranet sekolah (LAN / Wi-Fi lokal). Bilik suara **tidak memerlukan internet luar**, sehingga bebas dari risiko koneksi lemot, kuota habis, atau server down dari luar.
2. **One Student, One Vote**: Setiap siswa hanya berhak memilih tepat satu kali. Status memilih (`has_voted`) dikunci secara atomik pada database setelah suara masuk.
3. **Kerahasiaan Suara Terjamin (*Secret Ballot*)**: Pilihan kandidat siswa dicatat secara terpisah ke dalam tabel `votes`. Token atau identitas unik siswa **tidak disimpan** di baris suara pilihan tersebut, sehingga kerahasiaan pilihan terjaga mutlak.
4. **Audit Trail Keamanan**: Seluruh aktivitas administratif, pembukaan/penutupan sesi, dan waktu masuknya suara dicatat permanen di tabel `system_logs` dengan enkripsi jejak integritas.

---

## 2. APLIKASI & ALAT (TOOLS) YANG DIPERLUKAN

Sebelum mulai menginstal aplikasi, pastikan perangkat keras dan perangkat lunak pendukung telah siap.

### 2.1 Perangkat Keras (Hardware)
1. **1 Unit Laptop / Komputer Server (Admin)**:
   - Processor minimal Dual Core / Core i3.
   - RAM minimal 4 GB (disarankan 8 GB).
   - Sistem Operasi: Windows 10/11, Linux (Ubuntu/Debian/Fedora), atau macOS.
2. **Laptop / Komputer / Tablet untuk Bilik Suara**:
   - Jumlah disesuaikan dengan kuota bilik suara (contoh: 2 sampai 6 unit bilik).
   - Memiliki web browser modern (Google Chrome, Microsoft Edge, atau Mozilla Firefox).
3. **1 Unit Router Wi-Fi atau Switch LAN**:
   - Berfungsi sebagai penghubung komunikasi antara laptop server dan komputer bilik suara.
   - Tidak perlu tersambung ke kabel internet Telkom/Indihome (cukup jaringan lokal).

### 2.2 Perangkat Lunak (Software & Tools)

Berikut adalah aplikasi yang wajib terinstal pada **Laptop Server**:

| No | Software / Tool | Fungsi | Versi Minimum | Link Unduh Resmi |
|:---:|---|---|:---:|---|
| 1 | **XAMPP** (atau LAMPP) | Web server Apache & Database MySQL | PHP `8.3` atau lebih baru | [apachefriends.org](https://www.apachefriends.org) |
| 2 | **Composer** | Manajemen pustaka PHP Laravel | Versi `2.x` | [getcomposer.org](https://getcomposer.org) |
| 3 | **Node.js & NPM** | Compiler asset tampilan frontend (Vite & Tailwind CSS v4) | Node.js `v18.x` / `v20.x` LTS | [nodejs.org](https://nodejs.org) |
| 4 | **Git** | Alat clone / pull source code | Versi `2.x` | [git-scm.com](https://git-scm.com) |
| 5 | **Google Chrome / Edge** | Peramban web untuk membuka aplikasi | Versi terbaru | Bawaan OS / Chrome |

### 2.3 Cara Mengecek Kesiapan Tools di Komputer

Buka **Terminal** (di Linux/macOS) atau **Command Prompt / PowerShell** (di Windows), lalu ketikkan perintah berikut satu per satu:

```bash
# 1. Cek PHP (harus >= 8.3)
php -v

# 2. Cek Composer
composer -v

# 3. Cek Node.js & NPM
node -v
npm -v

# 4. Cek Git
git -v
```

> [!TIP]
> Jika salah satu perintah menampilkan pesan *`command not found`* atau *`is not recognized as an internal or external command`*, pastikan Anda sudah menginstal tool tersebut dan memilih opsi **"Add to PATH"** saat proses instalasi di Windows.

---

## 3. TAHAPAN MENDAPATKAN BERKAS APLIKASI (SOURCE CODE)

Terdapat 2 opsi mudah untuk mendapatkan source code aplikasi ke komputer server Anda:

```text
               CARA MENDAPATKAN SOURCE CODE
                            │
            ┌───────────────┴───────────────┐
            ▼                               ▼
    [OPSI A: GOOGLE DRIVE]          [OPSI B: GITHUB / GIT]
    • Unduh file ZIP                • Buka terminal di folder htdocs
    • Ekstrak file                  • Jalankan: git clone <url>
    • Pindahkan ke htdocs           • Langsung siap pakai
```

### Opsi A: Download File ZIP dari Google Drive
1. Buka tautan Google Drive yang dibagikan oleh tim pengembang.
2. Unduh file arsip berformat `.zip` (misal: `evoting-osis.zip`).
3. Ekstrak file zip tersebut menggunakan klik kanan -> **Extract All** (Windows) atau `unzip` (Linux).
4. Pindahkan folder hasil ekstraksi ke direktori web server:
   - **Di Linux (XAMPP/LAMPP)**: `/opt/lampp/htdocs/evoting-osis`
   - **Di Windows (XAMPP)**: `C:\xampp\htdocs\evoting-osis`

### Opsi B: Mengunduh / Clone Melalui Git / GitHub
Jika Anda memiliki akses ke repositori GitHub, buka terminal dan masuk ke folder `htdocs`:

```bash
# Untuk Linux:
cd /opt/lampp/htdocs

# Untuk Windows (Command Prompt / Git Bash):
cd C:\xampp\htdocs

# Jalankan perintah clone:
git clone https://github.com/username/evoting-osis.git

# Masuk ke folder project:
cd evoting-osis
```

> Jika project sudah pernah di-clone sebelumnya dan ingin memperbarui ke versi terbaru, cukup jalankan:
> ```bash
> git pull origin main
> ```

---

## 4. PANDUAN INSTALASI & KONFIGURASI APLIKASI DARI NOL

Ikuti 9 langkah berikut secara berurutan pada terminal laptop server:

### Langkah 1: Masuk ke Folder Project
```bash
# Linux:
cd /opt/lampp/htdocs/evoting-osis

# Windows:
cd C:\xampp\htdocs\evoting-osis
```

### Langkah 2: Memasang Dependensi PHP (Composer)
Perintah ini akan memasang framework Laravel 13, Livewire 4, dan seluruh pustaka backend:
```bash
composer install
```
*(Tunggu hingga proses unduh paket selesai dan muncul tulisan hijau `Package manifest generated successfully`)*.

### Langkah 3: Memasang Dependensi Frontend (NPM)
Perintah ini memasang Tailwind CSS v4, Alpine.js, dan Vite:
```bash
npm install
```

### Langkah 4: Konfigurasi File Environment (.env) & App Key
Salin template konfigurasi dan buat kunci keamanan aplikasi:

```bash
# Salin template .env.example menjadi .env
# Linux:
cp .env.example .env

# Windows (Command Prompt):
copy .env.example .env

# Generate kunci rahasia aplikasi:
php artisan key:generate
```

### Langkah 5: Konfigurasi & Migrasi Database

Buka file `.env` menggunakan teks editor (Notepad, VS Code, atau nano).

#### Pilihan 1: Menggunakan MySQL (Standar Rekomendasi XAMPP)
1. Buka **XAMPP Control Panel**, lalu klik tombol **Start** pada modul **Apache** dan **MySQL**.
2. Buka browser dan buka `http://localhost/phpmyadmin`.
3. Buat database baru bernama: `evoting_osis` (Collation: `utf8mb4_unicode_ci`).
4. Pastikan baris database pada file `.env` sesuai:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=evoting_osis
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Jalankan perintah migrasi tabel dan seeder awal:
   ```bash
   php artisan migrate --seed
   ```

#### Pilihan 2: Menggunakan SQLite (Sangat Praktis Tanpa Perlu Setup MySQL)
Jika Anda tidak ingin repot menyalakan MySQL XAMPP, Anda bisa menggunakan SQLite:
1. Buat file database kosong:
   ```bash
   # Linux:
   touch database/database.sqlite

   # Windows (PowerShell):
   New-Item database/database.sqlite
   ```
2. Pada file `.env`, ubah bagian database menjadi:
   ```dotenv
   DB_CONNECTION=sqlite
   ```
3. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```

### Langkah 6: Menghubungkan Folder Penyimpanan Foto (Storage Link)
> [!IMPORTANT]
> **Langkah ini WAJIB dijalankan** agar foto Calon Ketua & Wakil OSIS dapat diakses dan tampil di browser!

```bash
php artisan storage:link
```
*(Perintah ini akan membuat tautan pintas `public/storage` ke folder `storage/app/public`)*.

Pastikan juga pada file `.env` pengaturan disk penyimpanan adalah:
```dotenv
FILESYSTEM_DISK=public
```

### Langkah 7: Kompilasi Asset Tampilan (Vite Build)
Kompilasi styling Tailwind CSS v4 dan JavaScript agar halaman berjalan super cepat dan ringan:
```bash
npm run build
```
*(Pastikan output menampilkan `✓ built in ...ms` tanpa error)*.

### Langkah 8: Verifikasi Uji Sistem (Automated Tests)
Jalankan pengujian otomatis untuk memastikan seluruh fitur (autentikasi, voting flow, dual-mode, CSV report) siap digunakan 100%:
```bash
php artisan test --compact
```
Output yang benar akan menampilkan:
```text
{"tool":"phpunit","result":"passed","tests":49,"passed":49,"assertions":232,"duration_ms":1225}
```
*(Semua 49 tests berhasil lolos / Passed!)*

### Langkah 9: Menjalankan Server Lokal
Jalankan server aplikasi di laptop server:
```bash
php artisan serve
```
Sekarang buka browser di laptop server:
- **Halaman Bilik Suara**: `http://127.0.0.1:8000/`
- **Halaman Login Admin**: `http://127.0.0.1:8000/admin/login`
  - *Username*: `admin`
  - *Password*: `password123`

---

## 5. PENGATURAN JARINGAN INTRANET LAN / WI-FI SEKOLAH (HARI-H)

Pada hari pemilihan, laptop server dan seluruh komputer bilik suara harus terhubung ke satu jaringan lokal yang sama agar siswa di bilik suara dapat mengakses aplikasi server.

### 5.1 Topologi Jaringan Sederhana

```text
┌─────────────────────────────────────────────────────────┐
│               LAPTOP SERVER UTAMA (ADMIN)               │
│               IP Lokal: 192.168.1.100                   │
│         Menjalankan: php artisan serve --host=0.0.0.0   │
└───────────────────────────┬─────────────────────────────┘
                            │
                [Router Wi-Fi / Switch LAN]
                 (SSID: "EVOTING_SMK_OFFLINE")
                            │
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ Bilik Suara 1│    │ Bilik Suara 2│    │ Bilik Suara 3│
│ Browser buka:│    │ Browser buka:│    │ Browser buka:│
│192.168.1.100 │    │192.168.1.100 │    │192.168.1.100 │
└──────────────┘    └──────────────┘    └──────────────┘
```

### 5.2 Mengetahui Alamat IP Komputer Server
Hubungkan laptop server ke Wi-Fi / Router LAN, lalu cek IP:
- **Di Windows**: Buka Command Prompt, ketik `ipconfig`. Cari baris *IPv4 Address* (contoh: `192.168.1.100`).
- **Di Linux**: Buka Terminal, ketik `hostname -I` (contoh hasil: `192.168.1.100`).

### 5.3 Menyesuaikan Whitelist IP di File .env
Buka file `.env` di laptop server dan tambahkan IP tersebut ke konfigurasi:
```dotenv
APP_URL=http://192.168.1.100:8000
APP_ALLOWED_HOSTS=localhost,127.0.0.1,192.168.1.100
```
Setelah mengubah `.env`, jalankan pembersihan cache:
```bash
php artisan config:clear
```

### 5.4 Menjalankan Server untuk Seluruh Jaringan LAN
Jalankan server dengan parameter `--host=0.0.0.0` agar server menerima koneksi dari perangkat lain:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

> [!NOTE]
> Jika laptop server menggunakan Windows, saat muncul jendela *Windows Security Alert (Windows Defender Firewall)*, beri centang pada **Private networks** dan klik **Allow access**.

### 5.5 Menghubungkan Komputer Bilik Suara
1. Hubungkan komputer/laptop bilik suara ke jaringan Wi-Fi sekolah yang sama.
2. Buka browser Google Chrome di komputer bilik suara.
3. Ketik alamat URL:
   ```text
   http://192.168.1.100:8000
   ```
   *(Ganti `192.168.1.100` dengan IP laptop server Anda)*.
4. Halaman Bilik Suara Digital siap digunakan oleh siswa pemilih!
5. Tekan tombol <kbd>F11</kbd> pada keyboard komputer bilik suara untuk mengaktifkan mode layar penuh (*Full Screen*).

---

## 6. PANDUAN PENGGUNAAN LENGKAP ADMIN / PANITIA (CONTROL CENTER)

### 6.1 Login, Proteksi Sesi & Fitur Auto-Logout
1. Akses halaman login admin melalui URL: `http://127.0.0.1:8000/admin/login`.
2. Masukkan username `admin` dan password default `password123`.
3. **Penanganan Sesi Menggantung (Active Session Protection)**:
   - Jika admin sebelumnya menutup browser tanpa logout, sistem akan mendeteksi sesi lama masih aktif.
   - **Solusi Cepat**: Beri centang pada kotak **`[✓] Paksa Masuk & Cabut Sesi Lain`**, lalu klik **Masuk ke Control Center**. Sistem akan otomatis membatalkan sesi lama dan mengizinkan Anda masuk.
   - **Auto-Logout via Beacon API**: Sistem sekarang otomatis mengeluarkan admin ketika tab atau jendela browser ditutup.
4. **Tombol Logout Cepat**: Tombol merah **Logout** tersedia langsung di header atas Dashboard Admin.

### 6.2 Mengubah Password Admin
> [!WARNING]
> Sangat disarankan mengganti password default sebelum hari-H pemungutan suara!

1. Klik avatar profil panitia di pojok kanan atas, pilih menu **Ubah Password** (atau buka `/admin/password`).
2. Masukkan password lama (`password123`).
3. Masukkan password baru minimal 8 karakter, konfirmasi, lalu klik **Simpan Password Baru**.

### 6.3 Manajemen Akun Panitia (Admin & Superadmin)
1. Klik tombol **+ Buat Akun Admin** yang berada di header navbar dashboard (atau buka `/admin/management`).
2. Klik tombol **+ Tambah Admin**.
3. Masukkan nama lengkap panitia, username unik, password, dan pilih Role:
   - **Admin**: Dapat memantau dashboard, membuka/menutup sesi, dan mengunduh laporan.
   - **Superadmin**: Memiliki seluruh wewenang admin ditambah hak membuat akun admin baru.
4. Klik **Simpan Admin**.

### 6.4 Mengatur Periode / Hari Pemilihan Dinamis
Panitia dapat menyesuaikan teks nama periode atau hari pelaksanaan pemilihan (misal: *"Periode 2025/2026"* atau *"Hari Pemilihan: 28 Oktober 2026"*):
1. Buka modal tambah/edit kandidat paslon pada dashboard admin.
2. Pada kotak biru bertuliskan **Periode / Hari Pemilihan (Tampil di Bilik Suara)**, ketik teks yang diinginkan.
3. Klik **Simpan**.
4. Teks ini akan otomatis tersimpan ke pengaturan sistem dan langsung tampil pada sub-bar bilik suara siswa.

---

### 6.5 Dual-Mode Sistem Pemilihan: Paslon vs Kandidat Individu

Sistem mendukung 2 mode pemilihan yang dapat dialihkan kapan saja:

```text
                  PILIHAN MODE SISTEM PEMILIHAN
                                │
        ┌───────────────────────┴───────────────────────┐
        ▼                                               ▼
[MODE 1: SISTEM PASLON]                     [MODE 2: KANDIDAT INDIVIDU]
• Berpasangan: Ketua & Wakil                • Figur tunggal (1 orang)
• Form isi 2 foto & 2 nama                  • Form isi 1 foto & 1 nama
• Juara: 1 Paslon pemenang                  • Juara: Peringkat 1 = Ketua OSIS
                                                     Peringkat 2 = Wakil Ketua OSIS
                                                     Peringkat 3 & 4 = Pengurus Inti
```

#### A. Mengganti Mode Pemilihan
Di bagian header **Manajemen Paslon / Kandidat**, klik tombol mode yang diinginkan:
- **Mode Paslon**: Untuk pemilihan sistem pasangan (Ketua & Wakil).
- **Mode Kandidat Individu**: Untuk pemilihan sistem figur tunggal perorangan.

#### B. Menambahkan Data Paslon (Mode Paslon)
1. Pastikan tombol segmented control di modal berada pada **Mode Paslon (Ketua & Wakil)**.
2. Masukkan:
   - **Nomor Urut**: 1, 2, 3, dst.
   - **Nama Calon Ketua** & **Nama Calon Wakil**.
   - **Foto Ketua** & **Foto Wakil** (format JPG/PNG maks 2 MB).
   - **Slogan / Tagline**, **Visi**, dan **Program Misi** (1 baris = 1 poin misi).
3. Klik **Simpan Paslon**.

#### C. Menambahkan Data Kandidat (Mode Kandidat Individu)
1. Pilih tab **Mode Kandidat Individu** pada modal.
2. Input Calon Wakil akan disembunyikan secara otomatis.
3. Form berubah menjadi *full-width* yang proporsional:
   - **Nomor Urut**: 1, 2, 3, 4, dst.
   - **Nama Lengkap Kandidat**.
   - **Foto Kandidat**.
   - **Slogan / Tagline**, **Visi**, dan **Program Misi**.
4. Klik **Simpan Kandidat**.

#### D. Proteksi Integritas & Fitur Override Lock
- Saat sesi voting berstatus **OPEN**, data kandidat dikunci secara otomatis demi integritas suara.
- Jika terjadi situasi darurat (misal koreksi salah ketik nama), klik tautan **Buka Kunci Sementara (Override)** pada banner kuning. Lakukan perbaikan, lalu klik **Kunci Kembali**.

---

### 6.6 Mengelola Daftar Pemilih Tetap (DPT Siswa) & Token

#### A. Mengunduh Template CSV
Pada kartu **Import Daftar Pemilih Tetap (DPT)** di dashboard, klik tombol **Download Template CSV Contoh**. File `template-dpt-siswa.csv` akan terunduh.

#### B. Format File CSV Siswa
Buka file CSV menggunakan Microsoft Excel atau Notepad. Pastikan baris pertama berisi header persis seperti ini:
```csv
nis,name,class
2024001,Andi Pratama,XII RPL 1
2024002,Bunga Citra Lestari,XII AKL 2
2024003,Citra Kirana,XI OTKP 1
```
- **nis**: Nomor Induk Siswa (unik, angka/karakter).
- **name**: Nama lengkap siswa.
- **class**: Kelas siswa (contoh: `XII RPL 1`, `XI AKL 2`, `X OTKP 1`).

> [!TIP]
> **Token 4 Karakter Otomatis Dibuat**: Anda **tidak perlu** membuat token manual di Excel. Saat file CSV diimpor, sistem secara otomatis meng-generate Token/Kode Unik 4 karakter acak (contoh: `A7B2`, `VMQ5`, `9K3P`) untuk setiap siswa!

#### C. Mengunggah File DPT
1. Pastikan status sesi voting dalam kondisi **CLOSED**.
2. Tarik file CSV ke area upload atau klik untuk memilih file.
3. Klik tombol **Import Siswa**.
4. Sistem akan memproses dan menampilkan pesan sukses berisi jumlah siswa yang berhasil diimpor.

#### D. Mengosongkan Seluruh Data DPT (Hapus Seluruh DPT)
Ketika Anda ingin memulai periode pemilihan baru atau membersihkan seluruh data DPT:
1. Pastikan status sesi dalam kondisi **CLOSED**.
2. Klik tombol merah **Hapus Seluruh DPT** di atas tabel monitoring pemilih.
3. Konfirmasi modal yang muncul: klik **Ya, Hapus Seluruh Data**.
4. Seluruh data siswa, riwayat suara, dan reset perolehan suara akan dibersihkan secara aman dalam satu transaksi database.

---

### 6.7 Membuka Sesi Pemungutan Suara
1. Setelah seluruh komputer bilik suara siap dan saksi hadir, klik tombol hijau **Buka Sesi Voting** di kanan atas Dashboard.
2. Konfirmasi pada modal: klik **Ya, Buka Sesi**.
3. Status berubah menjadi **OPEN (● AKTIF)**. Seluruh bilik suara siswa seketika aktif dan siap melayani pemilih.

---

### 6.8 Monitoring Pemilih DPT Real-Time
Pada bagian **Monitoring Pemilih (DPT Real-Time)** di dashboard, panitia dapat memantau kehadiran siswa:
- **Tab Filter Kehadiran**:
  - **Belum Memilih**: Menampilkan daftar siswa yang belum menyalurkan hak suaranya (lengkap dengan Token 4 karakter untuk dibagikan panitia).
  - **Sudah Memilih**: Menampilkan siswa yang telah selesai memilih.
- **Filter Angkatan / Tingkat**: Filter instan satu klik untuk siswa **Kelas X**, **Kelas XI**, atau **Kelas XII**.
- **Filter Kelas**: Dropdown otomatis memilih kelas spesifik (misal: *XII RPL 1*).
- **Pencarian Cepat**: Cari siswa berdasarkan NIS atau Nama langsung di kolom pencarian.
- **Pengaturan Baris Halaman**: Atur tampilan 10, 25, 50, atau 100 baris per halaman.
- **Tombol Unduh Siswa Belum Memilih (CSV)**: Klik tombol ini untuk mengunduh daftar siswa yang belum memilih beserta kelas dan tokennya. Berikan file/cetakan ini kepada panitia lapangan untuk menjemput siswa di kelas bersangkutan.

---

### 6.9 Klasemen Live Count & Badge Proyeksi Jabatan
Klasemen suara auto-update secara real-time setiap 5 detik tanpa perlu me-refresh halaman:
- **Progress Bar Persentase**: Menampilkan perolehan suara dan persentase setiap kandidat.
- **Pada Mode Paslon**: Menampilkan nama pasangan calon dan peringkat perolehan.
- **Pada Mode Kandidat Individu**:
  - Menampilkan 1 foto avatar kandidat per baris.
  - **Badge Proyeksi Jabatan Otomatis**:
    - **Peringkat 1**: Badge hijau/emerald `Peringkat 1 • Calon Ketua OSIS`
    - **Peringkat 2**: Badge biru `Peringkat 2 • Calon Wakil Ketua OSIS`
    - **Peringkat 3**: Badge ungu `Peringkat 3 • Kandidat Ketiga`
    - **Peringkat 4**: Badge slate `Peringkat 4 • Kandidat Keempat`
  - Saat sesi ditutup/diakhiri, kata *"Calon"* otomatis berubah menjadi *"Terpilih"*.

---

### 6.10 Mengakhiri Sesi & Pengumuman Juara (Golden Spotlight)
1. Tepat saat jam pemilihan selesai, klik tombol kuning/oranye **Akhiri Sesi & Umumkan Pemenang**.
2. Konfirmasi: klik **Ya, Akhiri & Umumkan**.
3. Sesi resmi berstatus **CLOSED / FINAL**. Bilik suara siswa seketika terkunci.
4. **Golden Spotlight Winner Celebration Card** langsung muncul di bagian atas dashboard dengan animasi semburan confetti warna-warni:
   - **Jika Mode Paslon**: Menampilkan Paslon No. Urut terpilih, foto Ketua & Wakil, persentase suara, dan total suara.
   - **Jika Mode Kandidat Individu**:
     - Foto Juara 1 (badge `KETUA`) dan Juara 2 (badge `WAKIL`) tampil berdampingan dengan cincin emas di sisi kiri.
     - Judul resmi: `No. XX [Nama Juara 1] (Ketua) & No. YY [Nama Juara 2] (Wakil)`.
     - Kotak statistik kanan: Rincian suara Ketua dan Wakil secara rinci.
     - Sub-grid bawah: Menampilkan kandidat peringkat 3 dan 4 lengkap dengan perolehan suara mereka.
5. *(Opsi Darurat)* Jika sesi ingin dibuka kembali untuk pemungutan suara susulan, klik tombol **Buka Kembali Hasil Pemilihan**.

---

### 6.11 Export Dokumen Berita Acara Rekapitulasi (CSV)
1. Di kanan atas dashboard, klik tombol **Export Laporan (CSV)**.
2. File `rekapitulasi-resmi-voting-osis-[tanggal].csv` akan otomatis terunduh.
3. Berkas memuat dokumen resmi:
   - Header Berita Acara SMK An-Nurmaniyah & Waktu Penetapan.
   - Ringkasan Tingkat Partisipasi Pemilih (DPT, Suara Masuk, Persentase Kehadiran).
   - Tabel Rekapitulasi Perolehan Suara & Status Penetapan Jabatan.
   - Audit Log Transaksi Bilik Suara (NIS, Nama Siswa, Kelas, Pilihan Kandidat, Waktu Memilih, dan IP Bilik).
4. Buka file di Microsoft Excel, simpan/cetak sebagai berkas fisik Berita Acara untuk ditandatangani oleh Kepala Sekolah, Pembina OSIS, Ketua Panitia, dan Saksi.

---

## 7. PANDUAN PENGGUNAAN UNTUK SISWA (PEMILIH DI BILIK SUARA)

Petugas meja bilik suara cukup mengarahkan siswa mengikuti 4 langkah mudah berikut:

```text
┌────────────────────────────────────────────────────────┐
│                        LANGKAH 1                       │
│ Siswa mengetik NIS dan Token/Kode Unik 4 Karakter      │
│ (Bisa langsung tekan Enter ↵ di keyboard)              │
└───────────────────────────┬────────────────────────────┘
                            │ (NIS & Token Cocok)
                            ▼
┌────────────────────────────────────────────────────────┐
│                        LANGKAH 2                       │
│ Muncul Kartu Identitas & Foto Calon Kandidat / Paslon  │
│ Siswa memilih calon (Bisa tekan angka keyboard [1],[2])│
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                   KONFIRMASI AKHIR                     │
│ Muncul dialog: "Pastikan Pilihan Anda"                 │
│ Tekan tombol "✓ Ya, Kirim Suara" (atau Enter ↵)        │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                      LAYAR SUKSES                      │
│ Muncul tanda terima centang hijau digital              │
│ Layar otomatis reset kembali dalam 4 detik             │
└────────────────────────────────────────────────────────┘
```

### Langkah 1: Input NIS & Token 4 Karakter (Keyboard-Friendly)
1. Siswa duduk di bilik suara digital.
2. Masukkan **NIS** pada kotak pertama, lalu tekan tombol <kbd>Enter ↵</kbd> pada keyboard. Kursor akan otomatis berpindah ke kotak Token.
3. Masukkan **Token 4 Karakter** yang diberikan panitia, lalu tekan tombol <kbd>Enter ↵</kbd> sekali lagi.
4. Sistem memeriksa kecocokan data ke DPT.

### Langkah 2: Memilih Paslon / Kandidat
1. Layar menampilkan kartu **Identitas Terverifikasi** nama siswa dengan badge hijau *Hak Suara Aktif*.
2. Siswa melihat kartu kandidat:
   - **Pada Mode Paslon**: Menampilkan foto Calon Ketua & Wakil, nomor urut, slogan, visi, dan tombol klik *Program Misi*.
   - **Pada Mode Kandidat Individu**: Menampilkan foto figur kandidat berukuran besar, nomor urut, visi, dan misinya.
3. Siswa dapat menekan tombol biru **Pilih Paslon/Kandidat Ini** dengan mouse, atau **menekan tombol angka keyboard** <kbd>[1]</kbd>, <kbd>[2]</kbd>, dst. sesuai nomor urut yang dipilih.

### Langkah 3: Konfirmasi Akhir Suara (Secret Ballot)
1. Kotak dialog konfirmasi muncul menampilkan nomor urut dan foto kandidat yang dipilih siswa.
2. Siswa menekan tombol **✓ Ya, Kirim Suara** (atau menekan tombol <kbd>Enter ↵</kbd>).
3. Jika ingin mengganti pilihan sebelum dikirim, siswa dapat menekan tombol **Kembali** (atau tombol <kbd>Esc</kbd>).

### Langkah 4: Tanda Terima Digital & Auto-Reset 4 Detik
1. Layar menampilkan ikon centang hijau besar: *"Suara Anda Berhasil Tercatat!"*.
2. Siswa dipersilakan meninggalkan bilik suara.
3. Dalam hitungan **4 detik**, layar akan melakukan reset otomatis kembali ke halaman awal (Langkah 1) sehingga siap digunakan oleh siswa berikutnya tanpa meninggalkan jejak pilihan sebelumnya.

---

## 8. PANDUAN PEMELIHARAAN (MAINTENANCE) & BACKUP SISTEM

### 8.1 Prosedur Simulasi / Gladi Bersih & Reset Data
Sebelum hari pemilihan tiba, panitia disarankan melakukan simulasi pencoblosan. Setelah simulasi selesai, bersihkan data uji coba dengan langkah berikut:
1. Pastikan status sesi voting dalam kondisi **CLOSED**.
2. Gulir ke bagian paling bawah Dashboard Admin menuju kotak merah **Danger Zone: Reset Voting & Suara**.
3. Klik tombol **Reset Voting & Suara**.
4. Ketik kata konfirmasi: `RESET` (huruf kapital).
5. Klik **Reset Sekarang**.
6. **Hasil Reset**:
   - Seluruh suara di tabel `votes` kembali menjadi 0.
   - Status pemilih di DPT kembali menjadi belum memilih (`has_voted = false`).
   - Perolehan suara kandidat kembali 0.
   - **Data master siswa (DPT) dan data profil kandidat TETAP AMAN dan TIDAK terhapus**.

### 8.2 Prosedur Backup Database
Lakukan backup database secara berkala:

#### Jika Menggunakan MySQL (XAMPP):
Buka terminal dan jalankan mysqldump:
```bash
# Linux:
/opt/lampp/bin/mysqldump -u root evoting_osis > ~/backup-evoting-$(date +%Y%m%d_%H%M%S).sql

# Windows (Command Prompt):
C:\xampp\mysql\bin\mysqldump.exe -u root evoting_osis > C:\backup-evoting.sql
```

#### Jika Menggunakan SQLite:
Cukup salin file database SQLite:
```bash
# Linux:
cp database/database.sqlite database/backup-$(date +%Y%m%d_%H%M%S).sqlite

# Windows (Command Prompt):
copy database\database.sqlite database\backup-database.sqlite
```

### 8.3 Prosedur Backup Foto Kandidat
Salin folder foto kandidat yang tersimpan di server:
- Lokasi folder: `storage/app/public/candidates/`
- Simpan salinan folder tersebut ke flashdisk sebagai cadangan.

### 8.4 Prosedur Pembersihan Cache Aplikasi
Jika terjadi perubahan konfigurasi atau tampilan terasa macet, jalankan pembersihan cache:
```bash
php artisan optimize:clear
```

---

## 9. PENANGANAN MASALAH LENGKAP (TROUBLESHOOTING & ERROR HANDLING)

Berikut adalah panduan solusi praktis saat menghadapi kendala teknis di lapangan:

### 9.1 Kendala Bilik Suara Siswa

#### Error: "NIS tidak ditemukan di Daftar Pemilih Tetap (DPT)."
- **Penyebab**: Siswa salah mengetik nomor NIS, atau nama siswa belum masuk ke file CSV DPT.
- **Solusi**:
  1. Periksa kartu pelajar atau daftar hadir fisik siswa untuk memastikan nomor NIS yang benar.
  2. Jika siswa memang belum terdaftar di DPT, panitia dapat menambahkan baris siswa tersebut ke file CSV dan mengimpor ulang di Dashboard saat sesi ditutup sementara.

#### Error: "Token / Kode Unik tidak cocok dengan NIS ini."
- **Penyebab**: Siswa salah memasukkan 4 karakter token unik.
- **Solusi**:
  1. Panitia membuka tabel **Monitoring Pemilih (DPT)** pada tab **Belum Memilih**.
  2. Cari nama siswa bersangkutan dan periksa kolom **Token / Kode Unik** (misal: `A7B2`).
  3. Masukkan token yang sesuai (huruf kapital dan angka).

#### Error: "NIS ini SUDAH melakukan pemilihan sebelumnya."
- **Penyebab**: NIS tersebut telah tercatat mengirimkan suara di database.
- **Solusi**:
  1. Sistem menerapkan asas *one student, one vote* secara mutlak untuk mencegah manipulasi suara ganda.
  2. Buka menu **System Security Logs** di Dashboard Admin untuk memeriksa jam berapa suara NIS tersebut masuk dan dari IP bilik mana.

#### Error: "Sesi voting saat ini sedang ditutup oleh panitia."
- **Penyebab**: Sesi pemilihan di dashboard admin masih berstatus **CLOSED**.
- **Solusi**: Panitia membuka Dashboard Admin lalu klik tombol **Buka Sesi Voting**.

#### Error: "Terlalu banyak percobaan. Coba lagi dalam X detik."
- **Penyebab**: Terjadi salah ketik lebih dari 15 kali berturut-turut dari komputer yang sama (*Rate Limiter Brute Force Protection*).
- **Solusi**: Tunggu beberapa detik hingga masa tunggu selesai, atau jalankan perintah darurat di server:
  ```bash
  php artisan cache:clear
  ```

#### Layar Bilik Suara Macet / Freeze
- **Solusi**: Cukup tekan tombol <kbd>F5</kbd> atau <kbd>Ctrl + R</kbd> pada keyboard komputer bilik suara untuk memuat ulang halaman. Suara siswa aman dan tidak akan hilang karena transaksi tersimpan di server.

---

### 9.2 Kendala Login & Akun Admin

#### Error: "Akun admin ini terdeteksi sedang aktif di sesi/tab lain..."
- **Penyebab**: Tab admin ditutup sebelumnya tanpa menekan tombol Logout.
- **Solusi Sangat Mudah**: Pada form login (`/admin/login`), **centang pilihan `[✓] Paksa Masuk & Cabut Sesi Lain`**, lalu klik tombol **Masuk ke Control Center**.

#### Lupa Password Admin / Akun Terkunci
- **Solusi Cepat via Terminal**: Buka terminal laptop server dan reset password secara instan:
  ```bash
  php artisan tinker --execute="\$a = App\Models\Admin::where('username', 'admin')->first(); \$a->password = bcrypt('password123'); \$a->save(); echo 'Password sukses direset ke password123';"
  ```

#### Error: "403 Forbidden" saat Membuka Halaman Admin
- **Penyebab**: IP laptop admin yang mengakses belum dimasukkan ke whitelist `APP_ALLOWED_HOSTS` pada file `.env`.
- **Solusi**: Buka file `.env` di laptop server, tambahkan IP ke baris `APP_ALLOWED_HOSTS`:
  ```dotenv
  APP_ALLOWED_HOSTS=localhost,127.0.0.1,192.168.1.100,192.168.1.150
  ```
  Lalu bersihkan cache: `php artisan config:clear`.

---

### 9.3 Kendala Import File CSV DPT

#### Error: "Baris X: duplikasi NIS YYY dalam file CSV."
- **Penyebab**: Ada dua siswa atau lebih yang memiliki nomor NIS sama di dalam file CSV.
- **Solusi**: Buka file CSV di Microsoft Excel, gunakan fitur *Conditional Formatting -> Highlight Cells Rules -> Duplicate Values* untuk menemukan baris ganda, perbaiki, lalu simpan ulang.

#### Error: "Daftar siswa hanya dapat diimpor saat sesi voting ditutup."
- **Penyebab**: Panitia mencoba mengimpor DPT saat sesi voting berstatus **OPEN**.
- **Solusi**: Klik tombol **Tutup Sesi Voting** di dashboard terlebih dahulu sebelum mengimpor DPT.

#### Karakter Nama Siswa Menjadi Tanda Tanya / Simbol Aneh di Excel
- **Solusi**: Saat menyimpan file dari Excel, pastikan memilih tipe format **CSV UTF-8 (Comma delimited) (*.csv)**.

---

### 9.4 Kendala Foto Kandidat Tidak Tampil

#### Foto Kandidat Tampil sebagai Gambar Pecah / Ikon Silang Merah (404)
- **Penyebab**: Symlink storage public Laravel belum dibuat atau konfigurasi filesystem salah.
- **Solusi**:
  1. Pastikan di file `.env` tertulis:
     ```dotenv
     FILESYSTEM_DISK=public
     ```
  2. Jalankan perintah symlink di terminal server:
     ```bash
     php artisan storage:link
     ```

#### Error: "Kandidat ini sudah memiliki suara masuk dan tidak dapat dihapus."
- **Penyebab**: Panitia mencoba menghapus kandidat yang sudah memiliki perolehan suara sah di database.
- **Solusi**: Sistem secara sengaja memblokir penghapusan ini demi melindungi integritas hasil suara. Jika kandidat tersebut mengundurkan diri, klik **Edit** dan hilangkan centang **Tampilkan di Bilik Suara** agar tidak muncul lagi di bilik suara siswa.

---

### 9.5 Kendala Jaringan LAN & Akses Komputer Bilik

#### Komputer Bilik Tidak Bisa Membuka Server (`ERR_CONNECTION_TIMED_OUT`)
- **Penyebab 1**: Server dijalankan hanya dengan `php artisan serve` biasa tanpa host binding.
  - **Solusi**: Hentikan server (<kbd>Ctrl + C</kbd>), lalu jalankan dengan perintah:
    ```bash
    php artisan serve --host=0.0.0.0 --port=8000
    ```
- **Penyebab 2**: Firewall laptop server memblokir akses port 8000.
  - **Solusi di Windows**: Buka *Windows Defender Firewall -> Turn Windows Defender Firewall on or off*, matikan sementara firewall untuk Private Network saat pemilihan berlangsung.
  - **Solusi di Linux**: Jalankan `sudo ufw allow 8000/tcp`.
- **Penyebab 3**: Komputer bilik suara terhubung ke Wi-Fi yang berbeda dengan laptop server.
  - **Solusi**: Pastikan seluruh perangkat tersambung ke nama Wi-Fi (SSID) yang sama.

---

### 9.6 Kendala Database (MySQL / SQLite)

#### Error: "SQLSTATE[HY000] [2002] Connection refused" (MySQL)
- **Penyebab**: Layanan database MySQL belum berjalan di XAMPP.
- **Solusi**: Buka XAMPP Control Panel dan klik tombol **Start** pada modul MySQL hingga berwarna hijau.

#### Error: "database is locked" (SQLite)
- **Penyebab**: File `database.sqlite` diakses bersamaan tanpa mode WAL.
- **Solusi**: Buka terminal dan aktifkan mode WAL (*Write-Ahead Logging*):
  ```bash
  sqlite3 database/database.sqlite "PRAGMA journal_mode=WAL;"
  ```

---

## 10. CHEATSHEET PERINTAH TERMINAL DARURAT (CLI)

Simpan daftar perintah kilat ini untuk operator server saat bertugas di hari pemilihan:

```bash
# 1. Menjalankan Server Jaringan LAN (Semua Komputer Bisa Akses)
php artisan serve --host=0.0.0.0 --port=8000

# 2. Membersihkan Seluruh Cache Aplikasi (Jika Tampilan Macet / Tidak Update)
php artisan optimize:clear

# 3. Kompilasi Ulang Asset Tampilan Frontend
npm run build

# 4. Periksa Jumlah Suara Masuk & Total DPT Saat Ini
php artisan tinker --execute="echo 'Total DPT: ' . App\Models\Student::count() . ' | Total Suara Sah: ' . App\Models\Vote::count() . PHP_EOL;"

# 5. Cabut Seluruh Sesi Login Admin yang Menggantung
php artisan tinker --execute="App\Models\AdminSession::whereNull('revoked_at')->update(['revoked_at' => now()]); echo 'Semua sesi admin lama berhasil dicabut.';"

# 6. Reset Password Akun Admin ke Default (password123)
php artisan tinker --execute="\$a = App\Models\Admin::where('username', 'admin')->first(); \$a->password = bcrypt('password123'); \$a->save(); echo 'Password admin direset ke password123';"

# 7. Mengaktifkan Mode Paslon / Mode Kandidat via Terminal
# Untuk Mode Paslon:
php artisan tinker --execute="App\Models\Setting::current()->update(['voting_mode' => 'paslon']); echo 'Mode beralih ke Paslon.';"

# Untuk Mode Kandidat Individu:
php artisan tinker --execute="App\Models\Setting::current()->update(['voting_mode' => 'kandidat']); echo 'Mode beralih ke Kandidat Individu.';"

# 8. Menjalankan Pengujian Otomatis Sistem (49 Tests)
php artisan test --compact
```

---

*Buku panduan ini disusun secara resmi untuk pelaksanaan pesta demokrasi E-Voting OSIS SMK An-Nurmaniyah Tangerang. Selamat menyelenggarakan pemilihan yang jujur, adil, transparan, dan lancar!*
