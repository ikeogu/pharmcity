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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('prescription_number')->unique();
            $table->foreignUlid('patient_id')->constrained();
            $table->string('doctor_name');
            $table->string('doctor_license')->nullable();
            $table->date('prescription_date');
            $table->date('expiry_date')->nullable();
            $table->text('diagnosis')->nullable();
            $table->enum('status', ['pending', 'partially_filled', 'filled', 'expired'])->default('pending');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index('prescription_number');
            $table->index('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
