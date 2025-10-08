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
        Schema::create('countries', function (Blueprint $table) {
           $table->ulid('id')->primary();
            $table->string('name');
            $table->string('region')->default('not-set');
            $table->string('phone_code')->nullable();
             $table->string('api_name')->nullable();
            $table->string('flag_url')->nullable();
            $table->boolean('status')->default(0);
            $table->string('iso_code')->nullable();
            $table->string('iso3_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
