<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('test_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nickname')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('test_profiles');
    }
};
