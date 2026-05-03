## Sistem
- **Framework**: Laravel v13
- **Database**: MySQL
- **Frontend**: Livewire v4, Flux UI v2
- **Styling**: TailwindCSS v4
- **PHP**: v8.3

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
composer require phpoffice/phpspreadsheet maatwebsite/excel phpoffice/phpword barryvdh/laravel-dompdf
```
```bash
#opsional tapi recommended
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### 8. Jalankan Aplikasi

```bash
# Menggunakan development server
composer run dev
```

Akses aplikasi di browser: `http://127.0.0.1:8000/`

## Dukungan

Jika Anda mengalami masalah (error/bug) atau memiliki pertanyaan, silakan buka issue di repository ini.

## Kontak

Untuk pertanyaan, atau saran tertentu:

- **Email**: gus.tom.zsh@gmail.com

---

TIDAK BOLEH digunakan untuk tujuan komersial tanpa izin tertulis dari pemilik
