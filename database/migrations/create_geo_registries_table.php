<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('georegistry.table_name', 'geo_registries'), function (Blueprint $table) {
            $table->id();
            $table->morphs('registrable');
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('device_os')->nullable();
            $table->boolean('is_robot')->default(false);
            $table->boolean('is_proxy')->default(false);
            $table->string('language')->nullable();
            $table->string('browser_language')->nullable();
            $table->string('device_fingerprint', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('georegistry.table_name', 'geo_registries'));
    }
};
