# Sistem Pendukung Keputusan AHP

Aplikasi berbasis Laravel untuk membantu pengambilan keputusan menggunakan metode Analytical Hierarchy Process (AHP). Sistem ini dirancang untuk memberikan solusi terstruktur dalam mengevaluasi dan membandingkan berbagai alternatif berdasarkan kriteria yang telah ditentukan.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Cara Penggunaan](#cara-penggunaan)
- [Tentang Metode AHP](#tentang-metode-ahp)
- [Lisensi](#lisensi)

## Fitur Utama

- **Manajemen Kriteria dan Sub Kriteria**: Buat dan kelola kriteria pengambilan keputusan dengan struktur hierarki yang fleksibel
- **Input Alternatif**: Tambahkan alternatif atau opsi yang akan dievaluasi
- **Perbandingan Berpasangan**: Lakukan perbandingan berpasangan antar kriteria dan alternatif secara sistematis
- **Perhitungan Bobot Otomatis**: Sistem secara otomatis menghitung bobot prioritas berdasarkan perbandingan berpasangan
- **Uji Konsistensi (CR)**: Validasi konsistensi penilaian menggunakan Consistency Ratio untuk memastikan keandalan hasil
- **Perangkingan Hasil**: Dapatkan perangkingan final alternatif berdasarkan analisis AHP yang komprehensif

## Teknologi

- **Framework**: Laravel v13
- **Database**: MySQL
- **Frontend**: Livewire v4, Flux UI v2
- **Styling**: TailwindCSS v4
- **PHP**: v8.3
- **Testing**: PHPUnit v12
- **Development**: Laravel Sail, Vite

## Persyaratan Sistem

Sebelum memulai, pastikan sistem Anda memiliki:

- PHP >= 8.3
- Composer
- MySQL >= 8.0
- Node.js >= 18
- Docker dan Docker Compose (opsional, untuk Laravel Sail)

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/sistem-pendukung-keputusan-ahp.git
cd sistem-pendukung-keputusan-ahp
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Konfigurasi Environment

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_ahp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Jalankan Migrasi Database

```bash
php artisan migrate
```

### 6. Compile Assets

```bash
npm run build
```

Atau untuk development dengan hot reload:

```bash
npm run dev
```

### 7. Install Dependencies Tambahan (Opsional)

Untuk mengaktifkan fitur export hasil analisis:
edit resources/views/pages/perhitungan/⚡index.blade.php baris 281

```bash
composer require phpoffice/phpspreadsheet maatwebsite/excel
```

### 8. Jalankan Aplikasi

```bash
# Menggunakan development server
php artisan serve

# Atau menggunakan Laravel Sail (jika Docker tersedia)
./vendor/bin/sail up
```

Akses aplikasi di browser: `http://localhost:8000`

## Cara Penggunaan

### Langkah-Langkah Umum

1. **Tentukan Tujuan Keputusan**: Definisikan masalah atau tujuan yang akan dianalisis
2. **Buat Kriteria**: Tambahkan kriteria utama yang relevan dengan tujuan keputusan
3. **Tambahkan Sub Kriteria**: (Opsional) Buat sub kriteria untuk detail lebih lanjut
4. **Input Alternatif**: Tentukan alternatif atau opsi yang akan dibandingkan
5. **Lakukan Perbandingan**: Bandingkan kriteria dan alternatif secara berpasangan
6. **Verifikasi Konsistensi**: Periksa Consistency Ratio (CR) untuk memastikan konsistensi penilaian
7. **Analisis Hasil**: Lihat perangkingan final dan ambil keputusan

### Skala Perbandingan Berpasangan

Sistem menggunakan skala 1-9 untuk perbandingan berpasangan:

- **1**: Kedua elemen sama pentingnya
- **3**: Elemen pertama sedikit lebih penting
- **5**: Elemen pertama jelas lebih penting
- **7**: Elemen pertama sangat jelas lebih penting
- **9**: Elemen pertama mutlak lebih penting
- **2, 4, 6, 8**: Nilai-nilai antara untuk tingkat ketidaksetaraan yang lebih halus

### Interpretasi Hasil

- Bobot prioritas menunjukkan tingkat kepentingan relatif setiap elemen
- Semakin tinggi nilai bobot, semakin penting elemen tersebut
- CR (Consistency Ratio) < 0,1 menunjukkan penilaian yang konsisten
- Alternatif dengan skor akhir tertinggi adalah rekomendasi terbaik

## Tentang Metode AHP

### Analytical Hierarchy Process (AHP)

Analytical Hierarchy Process (AHP) adalah metode pengambilan keputusan multikriteria yang dikembangkan oleh Thomas L. Saaty pada tahun 1970-an. Metode ini membantu para pengambil keputusan dalam menghadapi masalah yang kompleks dengan memecahnya menjadi hierarki yang lebih sederhana.

### Prinsip Dasar AHP

1. **Dekomposisi**: Masalah diuraikan menjadi elemen-elemen yang lebih sederhana dan terstruktur dalam bentuk hierarki
2. **Penilaian Komparatif**: Elemen pada tingkat yang sama dibandingkan berpasangan untuk menentukan prioritas relatif
3. **Sintesis Prioritas**: Prioritas dari setiap tingkat hierarki disintesis untuk menghasilkan prioritas global

### Keunggulan AHP

- Fleksibel dan dapat diterapkan pada berbagai jenis masalah
- Melibatkan penilaian kualitatif yang dapat dikonversi menjadi kuantitatif
- Menyediakan metode validasi konsistensi penilaian
- Mudah dipahami dan diimplementasikan
- Transparan dalam proses pengambilan keputusan

### Aplikasi Praktis

AHP dapat digunakan untuk berbagai keperluan seperti:
- Pemilihan lokasi bisnis atau proyek
- Evaluasi dan seleksi supplier
- Pengembangan strategi bisnis
- Manajemen risiko
- Perencanaan infrastruktur
- Pemilihan teknologi atau sistem

## Lisensi

Proyek ini dilindungi oleh lisensi proprietary dengan ketentuan sebagai berikut:

**Larangan Komersial**: Proyek ini TIDAK BOLEH digunakan untuk tujuan komersial tanpa izin tertulis dari pemilik. Penggunaan komersial termasuk namun tidak terbatas pada:
- Menjual atau menyewakan aplikasi atau layanan berbasis proyek ini
- Menggunakan proyek sebagai bagian dari produk atau layanan yang dijual
- Menggunakan untuk keperluan bisnis yang menghasilkan pendapatan

**Penggunaan Non-Komersial**: Proyek ini dapat digunakan secara gratis untuk keperluan:
- Pendidikan dan pembelajaran
- Riset dan pengembangan akademis
- Penggunaan pribadi dan internal organisasi
- Pengembangan dan modifikasi untuk kebutuhan internal

Untuk lisensi komersial atau pengecualian khusus, silakan hubungi pemilik proyek.

## Kontribusi

Kontribusi dan saran untuk perbaikan sangat diterima. Untuk berkontribusi:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan Anda (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## Dukungan

Jika Anda mengalami masalah atau memiliki pertanyaan, silakan buka issue di repository ini.

## Kontak

Untuk pertanyaan, atau saran tertentu:

- **Email**: gus.tom.zsh@gmail.com

---

**Terakhir diperbarui**: Mei 2026

Dibuat dengan Laravel dan semangat untuk menyediakan solusi pengambilan keputusan yang lebih baik.
