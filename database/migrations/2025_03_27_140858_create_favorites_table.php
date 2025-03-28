<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('favorites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id')->index();
            $table->uuid('favoritable_id');
            $table->string('favoritable_type');
            $table->uuid('favorite_folder_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['owner_id', 'favoritable_type', 'favoritable_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('favorites');
    }
};
