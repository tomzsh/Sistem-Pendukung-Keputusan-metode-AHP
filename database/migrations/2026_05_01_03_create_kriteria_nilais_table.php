<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria_nilai', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kriteria_id_dari')
                ->constrained('kriteria')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('kriteria_id_tujuan')
                ->constrained('kriteria')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->integer('nilai');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria_nilai');
    }
};
