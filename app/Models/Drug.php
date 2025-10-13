<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function createdBy(): BelongsTo
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

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    /**
     * Get all batches for this drug
     */
    public function batches(): HasMany
    {
        return $this->hasMany(DrugBatch::class);
    }

    /**
     * Get only active batches (not expired, has quantity)
     */
    public function activeBatches(): HasMany
    {
        return $this->batches()
            ->where('status', 'active')
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date'); // FIFO
    }

    /**
     * Get batches expiring soon (within 90 days)
     */
    public function expiringSoonBatches(): HasMany
    {
        return $this->batches()
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date');
    }

    /**
     * Get expired batches that still have stock
     */
    public function expiredBatches(): HasMany
    {
        return $this->batches()
            ->where('expiry_date', '<=', now())
            ->where('quantity', '>', 0);
    }

    /**
     * Update total drug quantity from all active batches
     */
    public function updateTotalQuantity(): void
    {
        $this->quantity = $this->activeBatches()->sum('quantity');
        $this->save();
    }

    /**
     * Check if drug has sufficient stock across all batches
     */
    public function hasStock(int $requiredQuantity): bool
    {
        return $this->activeBatches()->sum('quantity') >= $requiredQuantity;
    }

    /**
     * Get the next expiring batch with sufficient quantity
     */
    public function getNextExpiringBatch(int $requiredQuantity = 1): ?DrugBatch
    {
        return $this->activeBatches()
            ->where('quantity', '>=', $requiredQuantity)
            ->first();
    }


    /**
     * Accessors & Computed Properties
     */

    /**
     * Get selling price based on how drug is sold
     */
    public function getSellingPriceAttribute(): float
    {
        // If selling per sachet, use price_per_sachet
        // Otherwise calculate based on packaging
        return $this->price_per_sachet ?? 0;
    }

    /**
     * Get available quantity based on sell_the_drug_as
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->total_sachets_in_stock ?? 0;
    }

    /**
     * Check if drug requires prescription (based on drug_group_class)
     */
    public function getRequiresPrescriptionAttribute(): bool
    {
        // Common controlled drug classes that require prescription
        $controlledClasses = [
            'narcotic',
            'controlled substance',
            'prescription only',
            'schedule II',
            'schedule III',
            'schedule IV',
            'antibiotic',
        ];

        $drugClass = strtolower($this->drug_group_class ?? '');

        foreach ($controlledClasses as $class) {
            if (str_contains($drugClass, $class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get days until expiry
     */
    public function getDaysToExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if expiring soon (within 90 days)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        $days = $this->days_to_expiry;
        return $days !== null && $days > 0 && $days <= 90;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('total_sachets_in_stock', '>', 0)
                    ->where(function($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>', now());
                    });
    }



    public function scopeExpiringSoon($query, $days = 90)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now())
                    ->where('total_sachets_in_stock', '>', 0);
    }

    /**
     * Decrement stock after sale
     */
    public function decrementStock(int $quantity): void
    {
        $this->decrement('total_sachets_in_stock', $quantity);
    }

    /**
     * Increment stock (for returns/cancellations)
     */
    public function incrementStock(int $quantity): void
    {
        $this->increment('total_sachets_in_stock', $quantity);
    }

    /**
     * Get display name for POS
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->drug_name;

        if ($this->drug_dose) {
            $name .= ' ' . $this->drug_dose;
        }

        if ($this->dose_unit) {
            $name .= $this->dose_unit;
        }

        return $name;
    }

    /**
     * Get full drug information for POS display
     */
    public function getFullDescriptionAttribute(): string
    {
        $parts = [$this->drug_name];

        if ($this->generic_name) {
            $parts[] = "({$this->generic_name})";
        }

        if ($this->drug_dose && $this->dose_unit) {
            $parts[] = "{$this->drug_dose}{$this->dose_unit}";
        }

        if ($this->packaging) {
            $parts[] = "- {$this->packaging}";
        }

        return implode(' ', $parts);
    }
}
