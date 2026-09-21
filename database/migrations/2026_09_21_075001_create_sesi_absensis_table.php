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
        Schema::create('sesi_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('diabsen_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->unique(['jadwal_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_absensi');
    }
};
