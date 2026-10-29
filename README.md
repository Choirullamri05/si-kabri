# SI-KABRI

**Sistem Informasi Klasifikasi Berita Prioritas** untuk pengelolaan media pada Divisi Komunikasi Publik Diskominfo Kota Medan. Aplikasi ini mengelola kliping berita, iklan koran, dan arsip spanduk, serta mengklasifikasikan judul berita menjadi **Prioritas** atau **Non Prioritas** melalui model pembelajaran mesin.

## Fitur

- Login dengan peran **Admin** dan **Staf**.
- Dashboard ringkasan kliping, sentimen berita, kategori prioritas, iklan koran, dan spanduk.
- Penambahan, pengubahan, dan penghapusan kliping berita.
- Klasifikasi judul berita menggunakan model Naive Bayes dan vektorisasi TF-IDF, dengan prapemrosesan bahasa Indonesia melalui Sastrawi.
- Pencatatan sentimen **Positif** atau **Negatif** yang dipilih pengguna.
- Koreksi kategori berita dan penandaan berita sebagai data latih.
- Laporan kliping dengan filter serta ekspor Excel (`.xlsx`).
- Laporan data latih dan ekspor dataset dalam format CSV.
- Pengelolaan iklan koran beserta foto, laporan, ekspor Excel, dan tampilan cetak.
- Pengelolaan arsip spanduk beserta lokasi pemasangan dan dokumentasi foto.
- Manajemen pengguna oleh Admin, pengaturan profil, dan halaman FAQ.

## Teknologi

| Bagian | Teknologi |
| --- | --- |
| Backend | PHP native dengan MySQLi |
| Database | MySQL |
| Antarmuka | Bootstrap, SB Admin 2, jQuery, DataTables, Font Awesome, SweetAlert2 |
| Grafik | Chart.js |
| Ekspor Excel | PhpSpreadsheet |
| Klasifikasi berita | Python, scikit-learn, joblib, Sastrawi |
| Pengelola dependensi | Composer dan npm |

## Kebutuhan lingkungan

- Laragon atau lingkungan server web lain yang mendukung PHP dan MySQL.
- **PHP 8.3 atau lebih baru, 64-bit**, untuk memenuhi dependensi pada `composer.lock`, termasuk ZipStream. PhpSpreadsheet sendiri mensyaratkan PHP `^8.1`.
- Ekstensi PHP: `mysqli`, `ctype`, `dom`, `fileinfo`, `gd`, `iconv`, `libxml`, `mbstring`, `simplexml`, `xml`, `xmlreader`, `xmlwriter`, `zip`, dan `zlib`.
- **MySQL 8.0** direkomendasikan karena dump database menggunakan collation `utf8mb4_0900_ai_ci`.
- Composer.
- Python. Konfigurasi saat ini mengarah ke Python 3.10 pada instalasi Laragon.
- Node.js dan npm jika ingin memasang dependensi JavaScript dari `package-lock.json`.
- Akses internet untuk memasang dependensi dan memuat aset yang dirujuk melalui CDN, termasuk Google Fonts, Bootstrap Icons, SweetAlert2 pada halaman login, dan Chart.js pada dashboard.

## Instalasi lokal dengan Laragon

### 1. Siapkan proyek dan layanan

Letakkan proyek di direktori web Laragon:

```text
D:\laragon\www\si-kabri
```

Jalankan layanan server web dan MySQL melalui Laragon. Buka terminal Laragon, lalu masuk ke direktori proyek:

```bat
cd /d D:\laragon\www\si-kabri
```

Perintah pada panduan ini ditujukan untuk terminal Windows CMD atau terminal Laragon yang menggunakan CMD.

### 2. Pasang dependensi PHP

```bat
composer install
composer check-platform-reqs
```

Pastikan versi PHP terminal dan versi PHP server web sama-sama memenuhi kebutuhan di atas.

### 3. Siapkan database

Melalui HeidiSQL, phpMyAdmin, atau klien MySQL lain:

1. Buat database bernama **`simada-kp`**.
2. Pilih database tersebut.
3. Impor berkas **`database/sikabri.sql`**.

Nama berkas SQL adalah `sikabri.sql`, tetapi nama database pada konfigurasi aplikasi adalah `simada-kp`.

Sesuaikan koneksi di `inc/koneksi.php` dengan lingkungan lokal. Konfigurasi bawaan:

```php
<?php
$koneksi = new mysqli("localhost", "root", "", "simada-kp");
?>
```

Urutan parameter adalah host, username, password, dan nama database. Jika memakai nama database atau kredensial lain, ubah nilai tersebut.

### 4. Siapkan Python untuk klasifikasi

Kode penambahan kliping di `staf/kliping_berita/input/add_kliping.php` menggunakan interpreter berikut:

```php
$pythonPath = "D:\\laragon\\bin\\python\\python-3.10\\python.exe";
```

Jika lokasi Python berbeda, sesuaikan `$pythonPath` pada berkas tersebut. Pasang paket pada interpreter yang sama:

```bat
"D:\laragon\bin\python\python-3.10\python.exe" -m pip install scikit-learn joblib Sastrawi
```

Pastikan ketiga berkas berikut tersedia dalam `staf/kliping_berita/input/`:

```text
predict.py
model_prioritas.pkl
vectorizer_tfidf.pkl
```

Uji prediksi dari direktori utama proyek:

```bat
"D:\laragon\bin\python\python-3.10\python.exe" staf\kliping_berita\input\predict.py "Pemerintah Kota Medan memperbaiki jalan"
```

Keluaran yang diharapkan adalah salah satu label: `Prioritas` atau `Non Prioritas`.

Versi paket Python belum dikunci dalam berkas dependensi. Jika model gagal dimuat atau hasilnya bermasalah, gunakan versi scikit-learn dan joblib yang sesuai dengan lingkungan pelatihan model.

Aplikasi memanggil Python melalui `shell_exec`. Pastikan fungsi tersebut tersedia pada konfigurasi PHP server web dan interpreter dapat dijalankan oleh proses server.

### 5. Pasang dependensi JavaScript jika diperlukan

```bat
npm ci
```

`package.json` mencantumkan Chart.js dan belum memiliki skrip `dev`, `build`, atau `test`. Halaman aplikasi juga menggunakan aset dari `vendor/`, `dist/`, `build/`, dan CDN; aplikasi dijalankan melalui server PHP.

### 6. Periksa direktori unggahan

Pastikan proses server web memiliki izin menulis ke `uploads/`, terutama direktori yang digunakan untuk avatar, iklan koran, dan dokumentasi spanduk.

### 7. Buka aplikasi

Akses melalui browser:

```text
http://localhost/si-kabri/
```

Jika virtual host otomatis Laragon aktif, aplikasi juga dapat diakses melalui:

```text
http://si-kabri.test/
```

Pengguna yang belum login akan diarahkan ke `login.php`.

## Akun dan peran pengguna

| Peran | Menu utama |
| --- | --- |
| Admin | Dashboard, input media, laporan, manajemen pengguna, profil, dan FAQ |
| Staf | Dashboard, input media, laporan, profil, dan FAQ |

Dump database menyertakan akun Admin dengan username **`adminaplikasi`**. Password disimpan sebagai hash pada kolom `pengguna.password_hash`; password asli tidak dicantumkan dalam proyek. Gunakan kredensial dari pengelola aplikasi.

Setelah masuk sebagai Admin, pengguna tambahan dapat dibuat melalui menu **Manajemen User**.

## Alur penggunaan

### Kliping berita

1. Masuk ke **Menu Input > Kliping Berita**, lalu pilih tambah data.
2. Isi tanggal, sumber media, judul berita, dan sentimen.
3. Simpan data. PHP memanggil `predict.py` untuk menentukan kategori berdasarkan judul.
4. Jika kategori perlu dikoreksi, buka halaman edit dan sesuaikan kategori. Centang **Jadikan Data Latih** jika berita akan digunakan sebagai dataset pelatihan.
5. Buka **Laporan Data > Kliping berita** untuk memfilter dan mengekspor laporan.

Penandaan **Jadikan Data Latih** menyimpan status berita untuk laporan dan ekspor dataset. Proyek ini menyertakan skrip prediksi dan model tersimpan, tetapi belum menyertakan skrip pelatihan ulang. Perubahan data latih tidak otomatis memperbarui berkas model.

### Iklan koran dan spanduk

- Gunakan **Menu Input > Iklan Koran** untuk mencatat iklan dan mengunggah dokumentasinya.
- Gunakan **Menu Input > Arsip Spanduk** untuk mencatat spanduk, lokasi pemasangan, dan foto.
- Buka menu **Laporan Data** sesuai jenis media untuk melihat laporan yang tersedia.

## Struktur direktori

```text
si-kabri/
├── admin/                  # Manajemen pengguna dan profil
├── build/                  # Skrip JavaScript antarmuka
├── database/
│   └── sikabri.sql         # Struktur dan data awal database
├── dist/                   # CSS, gambar, dan aset antarmuka
├── home/                   # Dashboard Admin dan Staf
├── inc/
│   └── koneksi.php         # Konfigurasi koneksi MySQL
├── partial/                # Komponen PHP bersama
├── report/                 # Ekspor dan tampilan cetak laporan iklan
├── staf/
│   ├── cetak_spanduk/      # Input dan laporan arsip spanduk
│   ├── iklan_koran/        # Input dan laporan iklan koran
│   └── kliping_berita/     # Input, model prediksi, dan laporan kliping
├── uploads/                # Berkas unggahan pengguna
├── vendor/                 # Dependensi Composer dan pustaka antarmuka
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── faq.php
├── index.php               # Halaman utama dan pemilihan halaman melalui ?page=
├── login.php
└── logout.php
```

## Pemecahan masalah

| Masalah | Pemeriksaan |
| --- | --- |
| Koneksi database gagal | Pastikan MySQL berjalan, database `simada-kp` sudah diimpor, dan konfigurasi `inc/koneksi.php` sesuai. |
| Impor SQL gagal karena collation | Gunakan MySQL 8.0 yang mendukung `utf8mb4_0900_ai_ci`. Untuk server lain, sesuaikan collation pada salinan dump sebelum diimpor. |
| Composer menolak versi PHP | Gunakan PHP 8.3 atau lebih baru, 64-bit, dan jalankan `composer check-platform-reqs`. |
| Ekstensi PHP belum tersedia | Aktifkan ekstensi yang diminta Composer pada `php.ini`, lalu mulai ulang server web. |
| Gagal memanggil model prediksi | Periksa `$pythonPath`, ketersediaan `shell_exec`, paket Python, dan ketiga berkas prediksi. Jalankan perintah uji prediksi untuk melihat error Python secara langsung. |
| Ekspor Excel gagal | Pastikan `composer install` berhasil, `vendor/autoload.php` tersedia, dan kebutuhan ekstensi PHP terpenuhi. |
| Unggah foto gagal | Periksa izin tulis `uploads/` serta pengaturan `file_uploads`, `upload_max_filesize`, dan `post_max_size` pada PHP. |
| Grafik, ikon, atau notifikasi tidak tampil | Periksa koneksi internet, pemuatan aset CDN, dan ketersediaan aset lokal di `vendor/`, `dist/`, dan `build/`. |
