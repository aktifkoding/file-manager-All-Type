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
        Schema::table('files', function (Blueprint $table) {
            // Multi-user: pemilik file (nullable agar data lama tetap valid)
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            // Tipe MIME aktual (deteksi dari isi file, bukan ekstensi)
            $table->string('mime_type')->nullable()->after('path');
            // Trash / recycle bin
            $table->softDeletes()->after('updated_at');
            // Index untuk filter & sorting yang sering dipakai
            $table->index(['user_id', 'category_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'category_id', 'created_at']);
            $table->dropColumn(['user_id', 'mime_type', 'deleted_at']);
        });
    }
};
