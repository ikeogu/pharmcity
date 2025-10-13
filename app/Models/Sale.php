<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Sale extends Model
{
    use HasUlids;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'prescription_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'status',
        'notes',
        'completed_at',
        'cashier_user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

     public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ✅ Optionally, a weekly scope
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ]);
    }

    // ✅ Monthly scope
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month);
    }

    protected static function booted()
    {
        static::creating(function ($sale) {
            if (empty($sale->invoice_number)) {
                // Count how many sales exist today
                $todayCount = self::whereDate('created_at', now()->toDateString())->count() + 1;

                // Generate something like: INV-20251006-0001
                $sale->invoice_number = 'INV-' . now()->format('Ymd') . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
            }

            // Ensure the ULID is set if not already
            if (empty($sale->id)) {
                $sale->id = (string) Str::ulid();
            }
        });
    }
}
