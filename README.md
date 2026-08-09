# SIAGA Version 3.0

## Sistem Informasi Alert & Monitoring Kinerja Perkara ##

[CHANGELOGS](https://github.com/chakoochandra/siaga/blob/main/changelogs.md)

## DAFTAR MENU
- **Dashboard** — Menampilkan rasio perkara, statistik, grafik, dan daftar aplikasi terintegrasi
- **Monitoring E-Doc** — Monitoring progress unggah dokumen E-doc
- **Kinerja Hakim** - Laporan kegiatan hakim dan rekap perkara diterima bulanan (LIPA 6)
- **Rekapitulasi Keadaan Perkara**
- **Rekapitulasi Ecourt**
- **Rekapitulasi Mediasi** — Rekapitulasi data mediasi perkara (LIPA 12)
- **Rekapitulasi Perkara Cerai**
	* Ringkasan Perkara CG, CT, Usia < 19 Tahun, Cerai Dengan DK
	* Demografi Agama, Pendidikan, Pekerjaan, Warga Negara
	* Daftar Perkara Cerai
- **Rekapitulasi Dispensasi Kawin**
- **Rekapitulasi Perkara Banding**
- **Rekapitulasi Perkara Kasasi**
- **Rekapitulasi Perkara PK**
- **Monitoring Jadwal Sidang** — Monitoring jadwal dan ruang sidang harian
- **Kontrol BHT** — Pengingat input BHT
- **Monitoring Relaas**
- **Manajemen Blangko ABT** — Manajemen file blangko ABT tanpa melalui Filezilla dan sejenisnya, langsung dari aplikasi
- **Daftar Pegawai** — Manajemen data pegawai untuk pengiriman notifikasi per-pegawai
- **Hari Libur** — Pengaturan hari libur
- **Daftar Web** — Pengelolaan daftar web/socmed yang ditampilkan pada halaman dashboard
- **Antrian Notifikasi** — Monitoring antrian notifikasi yang akan dikirimkan
- **Log Notifikasi** — Daftar notifikasi terkirim
- **Pengelolaan Otomatisasi** — Penjadwalan pengiriman notifikasi secara otomatis menggunakan cron expression
- **Konfigurasi Aplikasi** — Pengaturan aplikasi


## DAFTAR NOTIFIKASI WHATSAPP
- Notifikasi Kinerja Penyelesaian Perkara
- Notifikasi E-doc
- Notifikasi BAS — Thanks to [@faizmuchazmi-chazz](https://github.com/faizmuchazmi-chazz)
- Notifikasi Rencana BHT
- Notifikasi Relaas
- Notifikasi Sidang PP & Hakim
- Notifikasi Sidang Pihak
- Notifikasi Putus Belum Setor
- Notifikasi Rekapitulasi Mediasi
- Notifikasi Presensi SIKEP


## SCREENSHOT 

### Dashboard
![Rasio Perkara](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/7.png?raw=true)
![Statistik](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/8.png?raw=true)
![Grafik](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/9.png?raw=true)
![Daftar Aplikasi](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/10.png?raw=true)

#### Notifikasi Jadwal Sidang Hakim/PP
![Notifikasi Jadwal Sidang Hakim/PP](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/12.jpeg?raw=true)

#### Notifikasi Kinerja Penyelesaian Perkara
![Notifikasi Kinerja Penyelesaian Perkara](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/6.jpeg?raw=true)

#### Notifikasi Kontrol BHT
![Notifikasi Kontrol BHT](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/4.jpeg?raw=true)

#### Notifikasi Rekap Kinerja BAS
![Notifikasi Rekap Kinerja BAS](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/15.jpeg?raw=true)

#### Notifikasi Monitoring BAS PP
![Notifikasi Rekap Kinerja BAS](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/16.jpeg?raw=true)

#### Menu Jadwal Sidang
![Menu Jadwal Sidang](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/13.png?raw=true)

#### Menu Kontrol BHT
![Menu Kontrol BHT](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/1.png?raw=true)
![Menu Kontrol BHT](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/2.png?raw=true)
![Kirim Notifikasi BHT Secara Manual](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/3.png?raw=true)

#### Menu Monitoring Dokumen PMH, PPP, PJS, PHS
![Menu Jadwal Sidang](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/19.png?raw=true)

#### Menu Rekapitulasi Mediasi
![Menu Rekapitulasi Mediasi](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/11.png?raw=true)

#### Menu Hari Libur
![Menu Hari Libur](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/14.png?raw=true)

#### Menu Pengelolaan Otomatisasi
![Menu Pengelolaan Otomatisasi](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/18.png?raw=true)

#### Pengelolaan Daftar Aplikasi
![Pengelolaan Daftar Aplikasi](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/17.png?raw=true)

## INSTALASI
1. Duplikasi file index.example.php dan rename file duplikat menjadi index.php
2. Masuk ke folder `application/config`
3. Duplikasi file `database.example.php` dan rename file duplikat menjadi `database.php`
4. Buka file `database.php` yang baru dibuat dan sesuaikan konfigurasi database (baris 4-12)
5. Pada terminal jalankan command berikut (SESUAIKAN PATH DAN NAMA FOLDER APLIKASI):
   ```bash
   chown apache:apache -R /var/www/html/siaga
   ```
6. Tes jalankan aplikasi.

## TEMPORARY ADMIN CREDENTIAL
> [!CAUTION]
>
> Login admin sementara
> ```php
> 	Usename: admin
> 	Password: 12345678
>   ```
>   
>	SEGERA UBAH KATA SANDI ADMIN SETELAH ANDA BERHASIL LOGIN

## DEPLOY PRODUCTION
> [!CAUTION]
> STEP INI HANYA DILAKUKAN BILA:
> 1. Fungsi pengiriman notifikasi berhasil dilakukan
> 2. Notifikasi yang terkirim data dan teks sudah benar
> 3. Pengujian sistem notifikasi sudah dilakukan secara menyeluruh dan sesuai harapan
>
> Setelah aplikasi siap untuk digunakan LIVE, ubah environment ke production:
> 1. Buka file `index.php`
> 2. Pada baris 57, ubah:
>   ```php
>   define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
>   ```
>   menjadi:
>   ```php
>   define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
>   ```

## OTOMATISASI NOTIFIKASI
Untuk mengelola dan mengatur otomatisasi notifikasi, buka menu **Pengelolaan Otomatisasi** melalui menu settings pojok kanan bawah.

![Pengelolaan Daftar Aplikasi](https://github.com/chakoochandra/siaga/blob/main/assets/images/ss/0.png?raw=true)

## KONTAK
https://dialogwa.com/wa
