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
        Schema::create('drugs', function (Blueprint $table) {
            $table->ulid('id')->primary();
           // Basic Information
            $table->string('drug_name');
            $table->string('generic_name');
            $table->string('drug_group_class');
            $table->string('drug_dose');
            $table->string('drug_company')->nullable();
            $table->string('drug_country')->nullable();
            
            // Dosage & Administration
            $table->string('dose_unit'); // mls, mg, g, mcg, IU, drops
            $table->string('drug_route'); // Tablet, Syrup, Intravenous, etc.
            $table->decimal('min_daily_dose', 10, 2)->nullable();
            $table->decimal('max_daily_dose', 10, 2)->nullable();
            
            // Inventory & Pricing
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date');
            $table->integer('total_sachets_in_stock')->default(0);
            
            // Packaging Information
            $table->string('packaging'); // sachet, bottle, box, blister, tube, vial, ampoule
            $table->string('form_of_items_in_package'); // card, tablet, capsule, strip
            $table->integer('cards_per_sachet')->default(1);
            $table->string('sell_the_drug_as'); // sachet, card, tablet, bottle, piece
            $table->decimal('price_per_sachet', 10, 2)->default(0);
            
            // Additional Details
            $table->text('drug_description');
            $table->text('drug_composition')->nullable();
            $table->foreignUlid('created_by')->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('drug_name');
            $table->index('generic_name');
            $table->index('batch_number');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
