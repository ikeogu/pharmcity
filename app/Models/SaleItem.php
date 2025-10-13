<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'sale_id',
        'drug_id',
        'batch_id',
        'drug_name',
        'quantity',
        'unit_price',
        'discount',
        'subtotal',
        'requires_prescription',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'subtotal' => 'float',
        'requires_prescription' => 'boolean',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    /**
     * Get the batch used for this sale item
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(DrugBatch::class, 'batch_id');
    }

    /**
     * Get the drug for this sale item
     */
    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    /**
     * Get the parent sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
