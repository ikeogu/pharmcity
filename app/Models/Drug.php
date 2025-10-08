<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drug extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'drug_name',
        'generic_name',
        'drug_group_class',
        'drug_dose',
        'drug_company',
        'drug_country',
        'dose_unit',
        'drug_route',
        'min_daily_dose',
        'max_daily_dose',
        'purchase_price',
        'batch_number',
        'expiry_date',
        'total_sachets_in_stock',
        'packaging',
        'form_of_items_in_package',
        'cards_per_sachet',
        'sell_the_drug_as',
        'price_per_sachet',
        'drug_description',
        'drug_composition',
        'status',
        'created_by'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'price_per_sachet' => 'decimal:2',
        'min_daily_dose' => 'decimal:2',
        'max_daily_dose' => 'decimal:2',
        'cards_per_sachet' => 'integer',
        'total_sachets_in_stock' => 'integer',
    ];

    public function createdBy() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->total_sachets_in_stock <= 10;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->total_sachets_in_stock * $this->price_per_sachet;
    }

    // Scopes
    public function scopeInStock($query)
    {
        return $query->where('total_sachets_in_stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->where('total_sachets_in_stock', '<=', 10);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>=', now());
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('drug_name', 'like', "%{$search}%")
              ->orWhere('generic_name', 'like', "%{$search}%")
              ->orWhere('drug_group_class', 'like', "%{$search}%")
              ->orWhere('batch_number', 'like', "%{$search}%");
        });
    }
}
