<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_kriteria_nilai', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('id_kriteria')
                ->constrained('kriteria', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('sub_kriteria_id_dari')
                ->constrained('sub_kriteria', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('sub_kriteria_id_tujuan')
                ->constrained('sub_kriteria', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->integer('nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_kriteria_nilai');
    }
};
