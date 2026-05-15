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
        Schema::create('patients', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('hospital_id')->unique(); // e.g., PRIV/338I
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('title'); // Mr, Mrs, Miss, Dr, etc.
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('dob'); // Date of Birth
            $table->string('gender'); // Male, Female
            $table->text('additional_details')->nullable();

            // Registration Information
            $table->unsignedBigInteger('registration_id')->nullable(); // FK to registrations
            $table->unsignedBigInteger('service_location_id')->nullable(); // FK to service_locations
            $table->unsignedBigInteger('unit_id')->nullable(); // FK to units
            $table->ulid('consultant_id')->nullable(); // FK to consultants/users
            $table->string('patient_type'); // NEW, REVIEW, etc.
            $table->string('payment_party'); // Self/General, HMO/NHIS, Employer

            // Contact Information
            $table->text('permanent_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('nationality')->nullable();
            $table->string('occupation')->nullable();
            $table->string('marital_status')->nullable(); // Single, Married, Divorced, Widowed
            $table->string('religion')->nullable();

            // Next of Kin Information
            $table->string('nok_full_name')->nullable();
            $table->string('nok_phone')->nullable();
            $table->text('nok_address')->nullable();
            $table->string('nok_relationship')->nullable();
            $table->string('nok_occupation')->nullable();
            $table->string('nok_gender')->nullable();

            // Patient Status & Metadata
            $table->string('status')->default('pending'); // pending, active, inactive, discharged
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('registered_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('hospital_id');
            $table->index('phone');
            $table->index('email');
            $table->index('status');
            $table->index('patient_type');
            $table->index('consultant_id');
            $table->index('last_seen');

            // Foreign Keys
            $table->foreign('consultant_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
