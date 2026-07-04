# Decision Support System - AHP Method

## Tech Stack

* Laravel 13
* MySQL
* Livewire 4
* Flux UI 2
* Tailwind CSS 4
* PHP 8.3

## Requirements

Make sure your system has:

* PHP 8.3+
* Composer
* MySQL 8.0+
* Node.js 18+
* Docker & Docker Compose (optional)

## Installation

### Clone the repository

```bash
git clone https://github.com/tomzsh/sistem-pendukung-keputusan-ahp.git
cd sistem-pendukung-keputusan-ahp
```

### Install dependencies

```bash
composer install
npm install
```

### Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update your database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_ahp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Run migrations

```bash
php artisan migrate
```

### Build assets

```bash
npm run build
```

For development mode:

```bash
npm run dev
```

### Optional packages

To enable export features (Excel, Word, PDF):

```bash
composer require phpoffice/phpspreadsheet maatwebsite/excel phpoffice/phpword barryvdh/laravel-dompdf
```

Recommended:

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

You may also need to edit:

```text
resources/views/pages/perhitungan/index.blade.php
```

### Run the application

```bash
composer run dev
```

Open:

```text
http://127.0.0.1:8000
```

## Support

If you find a bug or have questions, please open an issue in the repository.

## Contact

Email: [gus.tom.zsh@gmail.com](mailto:gus.tom.zsh@gmail.com)
