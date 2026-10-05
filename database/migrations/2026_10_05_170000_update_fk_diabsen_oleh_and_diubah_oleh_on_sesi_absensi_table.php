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
        Schema::table('sesi_absensi', function (Blueprint $table) {
            $table->dropForeign(['diabsen_oleh']);
            $table->dropForeign(['diubah_oleh']);

            $table->foreign('diabsen_oleh')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('diubah_oleh')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_absensi', function (Blueprint $table) {
            $table->dropForeign(['diabsen_oleh']);
            $table->dropForeign(['diubah_oleh']);

            $table->foreign('diabsen_oleh')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('diubah_oleh')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
