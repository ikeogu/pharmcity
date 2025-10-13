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
        Schema::create('drug_batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('drug_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->unique();
            $table->foreignUlid('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->integer('initial_quantity');
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->date('manufacture_date');
            $table->date('expiry_date');
            $table->date('received_date');
            $table->string('supplier_batch_number')->nullable()->comment('Supplier\'s own batch reference');
            $table->enum('status', ['active', 'expired', 'recalled', 'depleted'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['drug_id', 'status']);
            $table->index('batch_number');
            $table->index('expiry_date');
            $table->index(['drug_id', 'expiry_date', 'quantity']);
        });

        // Add alert threshold for expiring batches
        Schema::create('batch_alerts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('batch_id')->constrained('drug_batches')->cascadeOnDelete();
            $table->enum('alert_type', ['expiring_soon', 'expired', 'low_stock', 'recalled']);
            $table->text('message');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignUlid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['batch_id', 'is_resolved']);
            $table->index('alert_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_batches');
        Schema::dropIfExists('drug_batches');
    }
};
