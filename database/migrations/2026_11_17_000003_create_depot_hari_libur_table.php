<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depot_hari_libur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depot')->cascadeOnDelete();
            $table->string('hari', 10);
            $table->timestamps();

            $table->unique(['depot_id', 'hari'], 'depot_hari_libur_depot_hari_unique');
        });

        DB::statement("ALTER TABLE depot_hari_libur ADD CONSTRAINT depot_hari_libur_hari_valid_check CHECK (hari IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('depot_hari_libur');
    }
};
