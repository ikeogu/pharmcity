<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Drug;
use App\Models\DrugBatch;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Customer;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Models\Patient;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class POSService
{
    /**
     * Process a complete sale transaction
     */
    public function processSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Validate stock and prescriptions
            $this->validateSaleItems($data['items'], $data['prescription_id'] ?? null);

            // Calculate totals
            $subtotal = $this->calculateSubtotal($data['items']);
            $discount = $data['discount'] ?? 0;
            $tax = $this->calculateTax($subtotal - $discount);
            $total = $subtotal - $discount + $tax;


            // Create sale
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'cashier_user_id' => Auth::id(),
                'prescription_id' => $data['prescription_id'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'completed_at' => now(),
            ]);


            // Create sale items and update inventory
            foreach ($data['items'] as $item) {
                $this->createSaleItem($sale, $item);
                $this->updateInventory($item['drug_id'], $item['quantity'], $item['batch_id'] ?? null);
            }


            // Update prescription status if applicable
            if ($sale->prescription_id) {
                $this->updatePrescriptionStatus($sale->prescription_id, $data['items']);
            }


            return $sale->load(['items.drug', 'customer', 'user', 'prescription']);
        });
    }

    /**
     * Validate sale items for stock availability and prescription requirements
     */
    protected function validateSaleItems(array $items, ?int $prescriptionId): void
    {
        foreach ($items as $item) {
            $drug = Drug::findOrFail($item['drug_id']);

            // Check if prescription is required
            if ($drug->requires_prescription && !$prescriptionId) {
                throw new Exception(
                    "Drug '{$drug->name}' requires a prescription"
                );
            }

            // Check stock availability
            if ($drug->total_sachets_in_stock < $item['quantity']) {
                throw new Exception(
                    "Insufficient stock for '{$drug->name}'. Available: {$drug->quantity}, Requested: {$item['quantity']}"
                );
            }

            // Check batch expiry if batch specified
            if (isset($item['batch_id'])) {
                $batch = DrugBatch::findOrFail($item['batch_id']);

                if ($batch->expiry_date < now()) {
                    throw new \Exception("Batch {$batch->batch_number} has expired");
                }

                if ($batch->quantity < $item['quantity']) {
                    throw new Exception(
                        "Insufficient stock in batch {$batch->batch_number}. Available: {$batch->quantity}, Requested: {$item['quantity']}"
                    );
                }
            }
        }
    }

    /**
     * Calculate subtotal from items
     */
    protected function calculateSubtotal(array $items): float
    {
        return collect($items)->sum(function ($item) {
            $unitPrice = $item['unit_price'] ?? Drug::find($item['drug_id'])->selling_price;
            $itemDiscount = $item['discount'] ?? 0;
            return ($unitPrice * $item['quantity']) - $itemDiscount;
        });
    }

    /**
     * Calculate tax (7.5% default)
     */
    protected function calculateTax(float $amount, float $rate = 0.075): float
    {
        return round($amount * $rate, 2);
    }

    /**
     * Create sale item record
     */
    protected function createSaleItem(Sale $sale, array $item): SaleItem
    {
        $drug = Drug::findOrFail($item['drug_id']);
        $unitPrice = $item['unit_price'] ?? $drug->selling_price;
        $itemDiscount = $item['discount'] ?? 0;
        $subtotal = ($unitPrice * $item['quantity']) - $itemDiscount;

        return SaleItem::create([
            'sale_id' => $sale->id,
            'drug_id' => $item['drug_id'],
            'batch_id' => $item['batch_id'] ?? null,
            'drug_name' => $drug->drug_name,
            'quantity' => $item['quantity'],
            'unit_price' => $unitPrice,
            'discount' => $itemDiscount,
            'subtotal' => $subtotal,
            'requires_prescription' => $drug->requires_prescription,
        ]);
    }

    /**
     * Update inventory after sale
     */
    protected function updateInventory(string $drugId, int $quantity, ?int $batchId): void
    {
        $drug = Drug::find($drugId);
        $drug->decrement('total_sachets_in_stock', $quantity);

        if ($batchId) {
            $batch = DrugBatch::findOrFail($batchId);
            $batch->decrement('quantity', $quantity);

            // Check if batch needs status update
            if ($batch->quantity <= 0) {
                $batch->update(['status' => 'depleted']);
            }
        }
    }

    /**
     * Update prescription status after dispensing
     */
    protected function updatePrescriptionStatus(int $prescriptionId, array $items): void
    {
        $prescription = Prescription::with('items')->findOrFail($prescriptionId);

        foreach ($items as $item) {
            $prescriptionItem = $prescription->items->firstWhere('drug_id', $item['drug_id']);
            if ($prescriptionItem) {
                $prescriptionItem->increment('quantity_dispensed', $item['quantity']);
            }
        }

        // Update overall prescription status
        $prescription->refresh();

        $allFilled = $prescription->items->every(function ($item) {
            return $item->quantity_dispensed >= $item->quantity_prescribed;
        });

        $anyFilled = $prescription->items->some(function ($item) {
            return $item->quantity_dispensed > 0;
        });

        if ($allFilled) {
            $prescription->update(['status' => 'filled']);
        } elseif ($anyFilled) {
            $prescription->update(['status' => 'partially_filled']);
        }
    }

    /**
     * Search drugs by name, generic name, or barcode
     */
    public function searchDrugs(string $query): Collection
    {
        return Drug::search($query)
            ->where('expiry_date', '>', now())
            ->where('total_sachets_in_stock', '>', 0)
            /*  ->with(['category', 'batches' => function ($q) {
                $q->where('quantity', '>', 0)
                    ->where('expiry_date', '>', now())
                    ->orderBy('expiry_date');
            }]) */
            ->limit(20)
            ->get()
            ->map(function ($drug) {
                return [
                    'id' => $drug->id,
                    'name' => $drug->drug_name,
                    'generic_name' => $drug->generic_name,
                    'barcode' => $drug->barcode,
                    'selling_price' => $drug->price_per_sachet,
                    'quantity' => $drug->total_sachets_in_stock,
                    'requires_prescription' => $drug->requires_prescription,
                    //'category' => $drug->category,
                    'batches' => $drug->batches->map(fn($batch) => [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity' => $batch->total_sachets_in_stock,
                        'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                    ]),
                ];
            });
    }

    /**
     * Hold a sale for later completion
     */
    public function holdSale(array $data): Sale
    {
       $sale = Sale::create([
            'patient_id' => $data['patient_id'] ?? null,
            'cashier_user_id' => Auth::id(),
            'subtotal' => $data['subtotal'],
            'tax' => $data['tax'],
            'discount' => $data['discount'] ?? 0,
            'total' => $data['total'],
            'payment_method' => 'cash', // Default, will be updated when completed
            'status' => 'pending',
            'notes' => $data['notes'] ?? 'Sale on hold',
        ]);


        foreach ($data['items'] as $item) {
            $sale->items()->create([
                'drug_id' => $item['drug_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        return $sale;
    }

    /**
     * Retrieve all held sales
     */
    public function getHeldSales(): Collection
    {
        return Sale::where('status', 'pending')
            ->with(['customer', 'user'])
            ->latest()
            ->get();
    }

    /**
     * Cancel a sale and restore inventory
     */
    public function cancelSale(int $saleId, ?string $reason = null): void
    {
        DB::transaction(function () use ($saleId, $reason) {
            $sale = Sale::with('items')->findOrFail($saleId);

            if ($sale->status === 'cancelled') {
                throw new \Exception('Sale is already cancelled');
            }

            // Restore inventory for each item
            foreach ($sale->items as $item) {
                $drug = Drug::find($item->drug_id);
                if ($drug) {
                    $drug->increment('quantity', $item->quantity);
                }

                if ($item->batch_id) {
                    $batch = DrugBatch::find($item->batch_id);
                    if ($batch) {
                        $batch->increment('quantity', $item->quantity);

                        // Update batch status if it was depleted
                        if ($batch->status === 'depleted' && $batch->quantity > 0) {
                            $batch->update(['status' => 'active']);
                        }
                    }
                }

                // Restore prescription quantities if applicable
                if ($sale->prescription_id) {
                    $prescriptionItem = PrescriptionItem::where('prescription_id', $sale->prescription_id)
                        ->where('drug_id', $item->drug_id)
                        ->first();

                    if ($prescriptionItem) {
                        $prescriptionItem->decrement('quantity_dispensed', $item->quantity);
                    }
                }
            }

            // Update prescription status back if needed
            if ($sale->prescription_id) {
                $this->recalculatePrescriptionStatus($sale->prescription_id);
            }

            // Mark sale as cancelled
            $sale->update([
                'status' => 'cancelled',
                'notes' => $sale->notes . "\n\nCancelled: " . ($reason ?? 'No reason provided') . " at " . now(),
            ]);
        });
    }

    /**
     * Recalculate prescription status after changes
     */
    protected function recalculatePrescriptionStatus(int $prescriptionId): void
    {
        $prescription = Prescription::with('items')->findOrFail($prescriptionId);

        $allFilled = $prescription->items->every(
            fn($item) =>
            $item->quantity_dispensed >= $item->quantity_prescribed
        );

        $anyFilled = $prescription->items->some(
            fn($item) =>
            $item->quantity_dispensed > 0
        );

        if ($allFilled) {
            $prescription->update(['status' => 'filled']);
        } elseif ($anyFilled) {
            $prescription->update(['status' => 'partially_filled']);
        } else {
            $prescription->update(['status' => 'pending']);
        }
    }

    /**
     * Search customers
     */
    public function searchCustomers(string $search): Collection
    {
        return Patient::search($search)
            ->limit(20)
            ->get();
    }

    /**
     * Get active prescriptions for a customer
     */
    public function getActivePrescriptions(?int $customerId = null): Collection
    {
        $query = Prescription::with('items.drug')->active();

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        return $query->latest()->limit(10)->get();
    }

    /**
     * Get today's sales statistics
     */
    public function getTodayStats(): array
    {
        $todaySales = Sale::today()->completed();

        return [
            'total_sales' => $todaySales->sum('total'),
            'total_transactions' => $todaySales->count(),
            'cash_sales' => $todaySales->where('payment_method', 'cash')->sum('total'),
            'card_sales' => $todaySales->where('payment_method', 'card')->sum('total'),
            'transfer_sales' => $todaySales->where('payment_method', 'transfer')->sum('total'),
            'insurance_sales' => $todaySales->where('payment_method', 'insurance')->sum('total'),
        ];
    }

    /**
     * Get recent sales
     */
    public function getRecentSales(int $limit = 10): Collection
    {
        return Sale::with(['customer', 'items', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Validate and allocate batch using FIFO
     */
    public function allocateBatchForItem(int $drugId, int $quantity): ?array
    {
        try {
            $allocations = DrugBatch::allocateQuantity($drugId, $quantity);
            return $allocations[0] ?? null; // Return first allocation
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get sale by invoice number
     */
    public function getSaleByInvoice(string $invoiceNumber): ?Sale
    {
        return Sale::where('invoice_number', $invoiceNumber)
            ->with(['items.drug', 'customer', 'user', 'prescription'])
            ->first();
    }

    /**
     * Generate sales report for date range
     */
    public function generateSalesReport(\DateTime $startDate, \DateTime $endDate): array
    {
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->completed()
            ->with(['items', 'user'])
            ->get();

        return [
            'total_sales' => $sales->sum('total'),
            'total_transactions' => $sales->count(),
            'average_transaction' => $sales->avg('total'),
            'total_items_sold' => $sales->sum(fn($sale) => $sale->items->sum('quantity')),
            'payment_methods' => [
                'cash' => $sales->where('payment_method', 'cash')->sum('total'),
                'card' => $sales->where('payment_method', 'card')->sum('total'),
                'transfer' => $sales->where('payment_method', 'transfer')->sum('total'),
                'insurance' => $sales->where('payment_method', 'insurance')->sum('total'),
            ],
            'top_sellers' => $this->getTopSellingDrugs($sales),
            'sales_by_cashier' => $sales->groupBy('user_id')->map(function ($userSales) {
                return [
                    'name' => $userSales->first()->user->name,
                    'transactions' => $userSales->count(),
                    'total' => $userSales->sum('total'),
                ];
            }),
        ];
    }

    /**
     * Get top selling drugs from sales collection
     */
    protected function getTopSellingDrugs(Collection $sales): array
    {
        $items = $sales->flatMap->items;

        return $items->groupBy('drug_id')
            ->map(function ($drugItems) {
                return [
                    'drug_name' => $drugItems->first()->drug_name,
                    'quantity_sold' => $drugItems->sum('quantity'),
                    'revenue' => $drugItems->sum('subtotal'),
                ];
            })
            ->sortByDesc('quantity_sold')
            ->take(10)
            ->values()
            ->toArray();
    }
}
