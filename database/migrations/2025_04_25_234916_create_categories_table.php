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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    /**
     * `: void` return type agar konsisten dan kompatibel dengan transactional
     * migrasi di PHP 8.0+.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
