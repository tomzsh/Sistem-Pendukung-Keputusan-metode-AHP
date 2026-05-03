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
        Schema::create('sub_kriteria_hasil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kriteria')
                    ->constrained('kriteria','id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

            $table->foreignId('id_sub_kriteria')
                    ->constrained('sub_kriteria','id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

            $table->float('nilai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_kriteria_hasil');
    }
};
