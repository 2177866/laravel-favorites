<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('favorite_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id')->index();
            $table->string('name');
            $table->timestamps();

            $table->unique(['owner_id', 'name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('favorite_folders');
    }
};
