<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('dashboard', 'pages::dashboard.index')->name('dashboard');
    Route::livewire('kriteria', 'pages::kriteria.index')->name('kriteria');
    Route::livewire('sub-kriteria', 'pages::subkriteria.index')->name('sub-kriteria');
    Route::livewire('alternatif', 'pages::alternatif.index')->name('alternatif');
    Route::livewire('penilaian', 'pages::penilaian.index')->name('penilaian');
    Route::livewire('perhitungan', 'pages::perhitungan.index')->name('perhitungan');
});

require __DIR__.'/settings.php';
