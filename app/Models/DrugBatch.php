<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrugBatch extends Model
{
    protected $fillable = [
        'drug_id',              // Foreign key to drugs table
        'batch_number',         // Unique identifier (auto-generated)
        'supplier_id',          // Foreign key to suppliers table
        'quantity',             // Current quantity in stock
        'initial_quantity',     // Original quantity received
        'cost_price',           // Purchase price per unit
        'selling_price',        // Retail price per unit
        'manufacture_date',     // Date manufactured
        'expiry_date',          // Date batch expires
        'received_date',        // Date received in pharmacy
        'supplier_batch_number', // Supplier's batch reference
        'status',               // active, expired, recalled, depleted
        'notes',                // Additional information
    ];



    protected $casts = [
        'quantity' => 'integer',
        'initial_quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
    ];


    protected $appends = [
        'is_expired',        // Boolean: Has it expired?
        'is_expiring_soon',  // Boolean: Expires within 90 days?
        'days_to_expiry',    // Integer: Days until expiry
        'quantity_sold',     // Integer: initial_quantity - quantity
    ];


    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    /**
     * Get the supplier who provided this batch
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get all sale items that used this batch
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'batch_id');
    }

    /**
     * Get all alerts for this batch
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(BatchAlert::class);
    }
}
