# Dokumentasi Instalasi, Pemakaian, dan Maintenance

Panduan operasional E-Voting OSIS untuk developer, operator/panitia, dan administrator server.

## 1. Prasyarat

- PHP 8.3 atau lebih baru.
- Composer 2.x.
- Node.js dan npm.
- SQLite atau MySQL.
- Web server yang mengarahkan document root ke folder `public/` untuk deployment Apache/Nginx.
- Hak tulis proses web pada `storage/` dan `bootstrap/cache/`.

Periksa versi:

```bash
php -v
composer --version
node --version
npm --version
```

## 2. Instalasi Lokal

Jalankan dari root project:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Database SQLite

Buat file database jika belum ada:

```bash
touch database/database.sqlite
```

Atur `.env`:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/opt/lampp/htdocs/evoting-osis/database/database.sqlite
SESSION_DRIVER=file
CACHE_STORE=file
```

`SESSION_DRIVER=file` dan `CACHE_STORE=file` adalah pilihan praktis untuk instalasi ini karena migration project tidak membuat tabel session/cache. Cache file tetap mendukung lock yang dipakai untuk mencegah pengiriman vote bersamaan.

### Database MySQL

Buat database kosong, lalu isi `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=evoting_osis
DB_USERNAME=evoting_user
DB_PASSWORD=ganti_dengan_password_yang_kuat
SESSION_DRIVER=file
CACHE_STORE=file
```

Gunakan user database khusus aplikasi dengan hak minimum yang diperlukan. Hindari memakai root pada server produksi.

### Migrasi, data contoh, dan asset

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

`--seed` membuat:

- Setting sesi default dalam keadaan **CLOSED**.
- Akun admin contoh `admin` dengan password `password123`.
- Delapan siswa contoh.
- Dua kandidat contoh.

Password admin contoh **wajib diganti atau akun contoh dinonaktifkan sebelum digunakan pada hari pemilihan**. Data siswa dan kandidat dari seeder hanya untuk development/simulasi.

### Menjalankan aplikasi

```bash
php artisan serve
```

Buka:

- Bilik suara: `http://localhost:8000/`
- Login admin: `http://localhost:8000/admin/login`

Saat mengubah CSS/JavaScript dalam development, jalankan `npm run dev` pada terminal terpisah.

## 3. Persiapan Hari Pemilihan

### Urutan yang disarankan

1. Pastikan backup database dan `.env` tersimpan di lokasi aman.
2. Login ke Control Center.
3. Pastikan status sesi **CLOSED**.
4. Import DPT siswa.
5. Tambah atau edit kandidat dan upload foto.
6. Verifikasi nomor urut, nama, visi, misi, dan status kandidat.
7. Lakukan simulasi dengan beberapa NIS contoh.
8. Reset data simulasi setelah uji selesai.
9. Pastikan hasil reset benar-benar kosong.
10. Buka sesi voting ketika panitia siap.
11. Setelah waktu selesai, tutup sesi.
12. Export laporan dan lakukan backup akhir.

### Import DPT siswa

File harus berformat CSV atau TXT dengan header **persis** seperti berikut:

```csv
nis,name,class
2026001,Andi Pratama,XII IPA 1
2026002,Bunga Citra Lestari,XII IPA 1
2026003,Chandra Wijaya,XII IPA 2
```

Aturan import:

- Kolom wajib: `nis`, `name`, `class`.
- Delimiter koma atau titik koma didukung.
- `nis` wajib diisi dan maksimal 30 karakter.
- `name` wajib diisi dan maksimal 255 karakter.
- `class` boleh kosong dan maksimal 50 karakter.
- NIS duplikat di dalam satu file membuat seluruh import ditolak.
- NIS yang sudah ada akan diperbarui nama/kelasnya tanpa mereset `has_voted`.
- Import hanya diizinkan saat sesi voting **CLOSED**.
- Ukuran file maksimal 5 MB.

> Sebelum import DPT asli, simpan salinan file CSV sumber di lokasi yang aksesnya terbatas. File tersebut berisi data pribadi siswa.

### Mengelola kandidat

Kandidat hanya dapat ditambah, diedit, atau dihapus ketika sesi voting **CLOSED**.

Data yang tersedia:

- Nomor urut, unik, bilangan 1 sampai 99.
- Nama ketua dan wakil.
- Foto ketua dan wakil, maksimal 2 MB per foto dan harus berupa image.
- Slogan maksimal 150 karakter.
- Visi maksimal 2.000 karakter.
- Misi: satu baris menjadi satu poin.
- Status aktif/nonaktif.

Kandidat yang sudah memiliki suara tidak dapat dihapus. Nonaktifkan kandidat jika perlu menyembunyikannya dari bilik suara tanpa merusak histori.

### Membuka dan menutup sesi

- **Buka Sesi Voting** mengizinkan NIS terdaftar yang belum memilih untuk masuk ke bilik suara.
- **Tutup Sesi Voting** menghentikan verifikasi dan pengiriman suara baru.
- Perubahan status sesi dicatat pada `settings` dan `system_logs`.
- Jangan mengubah data kandidat atau DPT ketika sesi sudah terbuka.

## 4. Cara Pemakaian Siswa

1. Buka halaman bilik suara.
2. Masukkan NIS/NIK sesuai DPT.
3. Tekan **Verifikasi & Lanjutkan**.
4. Baca visi, misi, dan identitas kandidat.
5. Tekan **Pilih Paslon Ini**.
6. Periksa dialog konfirmasi.
7. Tekan **Ya, Kirim Suara** hanya setelah pilihan benar.
8. Tunggu pesan bahwa suara berhasil tercatat.

Satu NIS hanya dapat memilih satu kali. Setelah suara berhasil dikirim, pilihan tidak dapat diubah melalui aplikasi.

## 5. Cara Pemakaian Panitia

### Login

Buka `/admin/login`, masukkan username dan password admin, lalu gunakan **Logout** setelah selesai mengelola sistem.

### Memantau dashboard

Dashboard menampilkan:

- Total suara masuk.
- Total pemilih terdaftar.
- Persentase partisipasi.
- Status sesi dan waktu buka/tutup.
- Kesehatan koneksi database serta latency query sederhana.
- Klasemen suara.
- 25 system log terbaru.

Dashboard diperbarui otomatis setiap lima detik melalui polling Livewire.

### Export laporan

Gunakan **Export Laporan (CSV)** setelah sesi ditutup. Laporan berisi nomor, NIS, nama, kelas, kandidat, nomor urut, waktu memilih, dan IP address.

Simpan hasil export di folder terbatas. Jangan mengunggahnya ke repository publik atau membagikannya tanpa kebutuhan yang sah.

### Reset Voting

Fitur ini hanya untuk simulasi/gladi bersih. Syaratnya:

1. Sesi harus CLOSED.
2. Buka bagian **Reset Voting**.
3. Ketik `RESET` persis.
4. Pastikan database memang bukan database hari-H.

> Reset menghapus seluruh baris `votes`, mengembalikan semua siswa ke `has_voted = false`, mengosongkan `voted_at`, mereset `candidate.vote_count`, dan menutup sesi. Tindakan ini tidak dapat dibatalkan tanpa backup.

## 6. Maintenance Rutin

### Sebelum dan sesudah pemilihan

- Periksa status disk, koneksi database, dan hak tulis `storage/`.
- Verifikasi `APP_DEBUG=false` pada produksi.
- Pastikan `APP_KEY` tidak kosong dan tidak berubah setelah data dibuat.
- Uji login admin, import DPT, upload foto, voting, export, dan logout.
- Simpan backup database sebelum reset atau migration.
- Setelah voting selesai, export laporan dan cocokkan jumlah laporan dengan dashboard.

### Backup SQLite

Lakukan saat aktivitas tulis berhenti atau gunakan mekanisme backup SQLite yang sesuai:

```bash
mkdir -p backups
cp database/database.sqlite "backups/database-$(date +%Y%m%d-%H%M%S).sqlite"
```

Backup juga folder foto kandidat jika memakai disk public:

```bash
tar -czf "backups/candidate-images-$(date +%Y%m%d-%H%M%S).tar.gz" storage/app/public/candidates
```

### Backup MySQL

```bash
mkdir -p backups
mysqldump -u evoting_user -p evoting_osis > "backups/database-$(date +%Y%m%d-%H%M%S).sql"
```

Jangan menaruh password database langsung pada command yang tersimpan di shell history. Ikuti kebijakan backup sekolah untuk retensi dan lokasi penyimpanan.

### Log dan audit

Pantau log aplikasi:

```bash
tail -f storage/logs/laravel.log
```

Jenis log audit yang penting:

- `vote_success`: suara berhasil dicatat.
- `duplicate_attempt`: percobaan memakai NIS yang sudah memilih.
- `race_condition_blocked`: percobaan vote bersamaan berhasil diblokir.
- `nis_not_found`: NIS tidak ditemukan.
- `admin_action`: aktivitas admin.
- `session_toggled`: sesi dibuka atau ditutup.
- `data_reset`: data voting di-reset.

Log dapat berisi NIS, IP address, dan informasi aktivitas. Batasi akses dan kebijakan retensinya.

### Update aplikasi

Sebelum update dependency atau kode:

```bash
git status
git pull --ff-only
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

Jalankan test sebelum deployment:

```bash
php artisan test --compact
```

Review `composer.lock`, `package-lock.json`, migration baru, dan perubahan konfigurasi sebelum menjalankan update di produksi.

## 7. Deployment Web Server

Document root harus menunjuk ke folder `public/`, bukan root repository. Pastikan:

- `.env` berada di luar akses publik.
- `storage/` dan `bootstrap/cache/` writable oleh user web server.
- HTTPS aktif.
- `APP_DEBUG=false`.
- `APP_KEY` tersedia.
- `php artisan storage:link` sudah dijalankan.
- Queue worker disiapkan bila konfigurasi queue database digunakan untuk pekerjaan asynchronous di masa depan.
- PHP-FPM/Apache direstart bila perubahan runtime memerlukannya.

Setelah deployment, bersihkan dan bangun cache konfigurasi:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jangan menjalankan `config:cache` sebelum `.env` produksi benar, karena nilai environment akan dibaca dan disimpan dalam cache konfigurasi.

## 8. Troubleshooting

### Halaman menampilkan error Vite atau asset tidak ditemukan

```bash
npm install
npm run build
php artisan optimize:clear
```

### Foto kandidat tidak tampil

```bash
php artisan storage:link
```

Pastikan file berada di `storage/app/public/candidates` dan `FILESYSTEM_DISK` menggunakan disk yang sesuai.

### Login admin gagal setelah seeding

Pastikan seeder sudah dijalankan:

```bash
php artisan db:seed --class=AdminSeeder
```

Untuk instalasi baru, kredensial contoh adalah `admin` / `password123`. Ganti password tersebut sebelum sistem dipakai nyata.

### Import siswa ditolak

Periksa tiga hal: sesi harus CLOSED, header harus persis `nis,name,class`, dan tidak boleh ada NIS duplikat di file.

### Siswa tidak dapat memilih

Periksa status sesi, keberadaan NIS pada tabel `students`, dan kolom `has_voted`. Jika NIS sudah memilih, aplikasi memang menolak vote kedua.

### Dashboard menunjukkan database terputus atau lambat

Periksa kredensial database, koneksi jaringan, kapasitas disk, dan log Laravel. Dashboard mengukur query `SELECT 1`; status ini bukan pengganti monitoring database menyeluruh.

## 9. Checklist Serah Terima

- [ ] Repository dan branch deployment benar.
- [ ] `.env` produksi tidak masuk Git.
- [ ] `APP_KEY` tersedia dan rahasia.
- [ ] `APP_DEBUG=false`.
- [ ] Database sudah dimigrasi.
- [ ] Password admin default sudah diganti.
- [ ] DPT sudah diverifikasi dan di-backup.
- [ ] Kandidat, nomor urut, foto, visi, dan misi sudah diverifikasi.
- [ ] `storage:link` aktif.
- [ ] Build frontend berhasil.
- [ ] Sesi masih CLOSED sebelum hari-H.
- [ ] Simulasi voting dan export berhasil.
- [ ] Backup database dan prosedur restore sudah diuji.
- [ ] Akses laporan dan log dibatasi kepada petugas berwenang.