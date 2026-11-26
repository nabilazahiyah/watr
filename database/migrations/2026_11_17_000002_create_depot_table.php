<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelurahan_id')->constrained('kelurahan')->restrictOnDelete();
            $table->string('nama_depot', 150);
            $table->text('alamat');
            $table->text('link_google_maps');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('harga_per_galon');
            $table->time('jam_buka');
            $table->time('jam_tutup');
            $table->boolean('buka_setiap_hari');
            $table->boolean('is_delivery');
            $table->string('telepon', 20)->nullable();
            $table->timestamps();

            $table->index('kelurahan_id');
        });

        DB::statement('ALTER TABLE depot ADD CONSTRAINT depot_latitude_range_check CHECK (latitude BETWEEN -90 AND 90)');
        DB::statement('ALTER TABLE depot ADD CONSTRAINT depot_longitude_range_check CHECK (longitude BETWEEN -180 AND 180)');
        DB::statement('ALTER TABLE depot ADD CONSTRAINT depot_harga_nonnegative_check CHECK (harga_per_galon >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('depot');
    }
};
