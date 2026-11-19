# Library Management System

Aplikasi manajemen perpustakaan dengan PHP dan Laravel, menggunakan Microsoft SQL Server.

## Fitur

- Melihat koleksi buku dan menghitung eksemplar yang tersedia.
- Menambahkan buku dan anggota.
- Mencatat peminjaman, mencegah peminjaman saat stok habis, dan mencatat pengembalian.
- Melihat peminjaman aktif dan buku yang terlambat.
- Mencatat denda untuk peminjaman yang terlambat dan menandai pembayaran.
- Melihat jumlah denda yang belum dibayar.

## Persyaratan

- PHP 8.3 atau lebih baru dan Composer.
- SQL Server dan Microsoft ODBC Driver for SQL Server.
- Ekstensi PHP `sqlsrv` dan `pdo_sqlsrv` yang cocok dengan versi, arsitektur, dan jenis build PHP.

Periksa ekstensi dengan:

```powershell
php -m | Select-String "sqlsrv|pdo_sqlsrv"
```

Driver resmi dapat diunduh dari [Microsoft Drivers for PHP for SQL Server](https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server). Jika ekstensi belum muncul, pasang driver yang sesuai dan aktifkan keduanya di `php.ini`, lalu mulai ulang terminal atau web server.

## Menyiapkan database

1. Pastikan SQL Server menerima koneksi TCP pada port `1433` atau sesuaikan konfigurasi koneksi.
2. Dari SQL Server Management Studio (SSMS), hubungkan ke instance SQL Server Anda.
3. Jalankan `database/01_create_schema.sql`, lalu `database/02_seed_data.sql`. Jalankan seed hanya sekali.
4. Opsional: jalankan `database/03_practice_queries.sql` untuk berlatih query join dan tanggal jatuh tempo.

## Menjalankan aplikasi

Jalankan di PowerShell dari folder proyek ini:

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Atur `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` sesuai SQL Server Anda. Kosongkan username dan password untuk Windows Integrated Authentication; akun Windows yang menjalankan PHP harus memiliki akses database. Isi keduanya untuk SQL Server Authentication. Konfigurasi komputer pengembang menggunakan TCP pada `localhost:1433`. Untuk SQL Server yang tidak menerima TCP, isi `DB_PORT=null` dan gunakan host yang sesuai untuk koneksi lokal.

Setelah SQL Server dan ekstensi PHP siap, jalankan:

```powershell
php artisan key:generate
php artisan serve --host=127.0.0.1 --port=8000
```

Buka `http://127.0.0.1:8000`. Jalankan skrip SQL untuk membuat tabel; jangan jalankan `php artisan migrate` karena skema aplikasi dibuat oleh skrip SQL tersebut.

Jalankan tes dengan `php artisan test`.

## Catatan

Aplikasi ini ditujukan untuk belajar dan penggunaan lokal. Aplikasi belum memiliki autentikasi pengguna; jangan publikasikan ke internet sebelum menambahkan autentikasi dan pengamanan akses.
