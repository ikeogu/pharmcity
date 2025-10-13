<?php

namespace App\Services;

use App\Models\Drug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DrugService {

    /**
     * Get paginated list of drugs with optional filters
     */
    public function getAllDrugs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Drug::query();

        // Search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Stock status filter
        if (!empty($filters['stock_status'])) {
            match ($filters['stock_status']) {
                'in_stock' => $query->inStock(),
                'low_stock' => $query->lowStock(),
                'out_of_stock' => $query->where('total_sachets_in_stock', 0),
                default => null
            };
        }

        // Expiry status filter
        if (!empty($filters['expiry_status'])) {
            match ($filters['expiry_status']) {
                'expired' => $query->expired(),
                'not_expired' => $query->notExpired(),
                'expiring_soon' => $query->whereBetween('expiry_date', [now(), now()->addMonths(3)]),
                default => null
            };
        }

        // Drug route filter
        if (!empty($filters['drug_route'])) {
            $query->where('drug_route', $filters['drug_route']);
        }

        // Drug group/class filter
        if (!empty($filters['drug_group_class'])) {
            $query->where('drug_group_class', $filters['drug_group_class']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get a single drug by ID
     */
    public function getDrugById(int $id): ?Drug
    {
        return Drug::find($id);
    }

    /**
     * Create a new drug
     */
    public function createDrug(array $data): Drug
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $data['created_by'] = $user->id;
            $drug = Drug::create($data);

            Log::info('Drug created successfully', ['drug_id' => $drug->id, 'drug_name' => $drug->drug_name]);

            DB::commit();

            return $drug;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create drug', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Update an existing drug
     */
    public function updateDrug(int $id, array $data): Drug
    {
        try {
            DB::beginTransaction();

            $drug = Drug::findOrFail($id);
            $drug->update($data);

            Log::info('Drug updated successfully', ['drug_id' => $drug->id, 'drug_name' => $drug->drug_name]);

            DB::commit();

            return $drug->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update drug', ['drug_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a drug (soft delete)
     */
    public function deleteDrug(int $id): bool
    {
        try {
            DB::beginTransaction();

            $drug = Drug::findOrFail($id);
            $deleted = $drug->delete();

            Log::info('Drug deleted successfully', ['drug_id' => $id]);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete drug', ['drug_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted drug
     */
    public function restoreDrug(int $id): bool
    {
        try {
            $drug = Drug::withTrashed()->findOrFail($id);
            $restored = $drug->restore();

            Log::info('Drug restored successfully', ['drug_id' => $id]);

            return $restored;
        } catch (\Exception $e) {
            Log::error('Failed to restore drug', ['drug_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Permanently delete a drug
     */
    public function forceDeleteDrug(int $id): bool
    {
        try {
            DB::beginTransaction();

            $drug = Drug::withTrashed()->findOrFail($id);
            $deleted = $drug->forceDelete();

            Log::info('Drug permanently deleted', ['drug_id' => $id]);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to permanently delete drug', ['drug_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update drug stock
     */
    public function updateStock(int $id, int $quantity, string $operation = 'add'): Drug
    {
        try {
            DB::beginTransaction();

            $drug = Drug::findOrFail($id);

            if ($operation === 'add') {
                $drug->total_sachets_in_stock += $quantity;
            } elseif ($operation === 'subtract') {
                if ($drug->total_sachets_in_stock < $quantity) {
                    throw new \Exception('Insufficient stock');
                }
                $drug->total_sachets_in_stock -= $quantity;
            } elseif ($operation === 'set') {
                $drug->total_sachets_in_stock = $quantity;
            }

            $drug->save();

            Log::info('Drug stock updated', [
                'drug_id' => $id,
                'operation' => $operation,
                'quantity' => $quantity,
                'new_stock' => $drug->total_sachets_in_stock
            ]);

            DB::commit();

            return $drug->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update drug stock', ['drug_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get drugs expiring within specified days
     */
    public function getExpiringDrugs(int $days = 90): Collection
    {
        return Drug::whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get low stock drugs
     */
    public function getLowStockDrugs(int $threshold = 10): Collection
    {
        return Drug::where('total_sachets_in_stock', '<=', $threshold)
            ->where('total_sachets_in_stock', '>', 0)
            ->orderBy('total_sachets_in_stock', 'asc')
            ->get();
    }

    /**
     * Get out of stock drugs
     */
    public function getOutOfStockDrugs(): Collection
    {
        return Drug::where('total_sachets_in_stock', 0)->get();
    }

    /**
     * Get expired drugs
     */
    public function getExpiredDrugs(): Collection
    {
        return Drug::expired()->get();
    }

    /**
     * Get inventory statistics
     */
    public function getInventoryStats(): array
    {
        return [
            'total_drugs' => Drug::count(),
            'in_stock' => Drug::inStock()->count(),
            'low_stock' => Drug::lowStock()->count(),
            'out_of_stock' => Drug::where('total_sachets_in_stock', 0)->count(),
            'expired' => Drug::expired()->count(),
            'expiring_soon' => Drug::whereBetween('expiry_date', [now(), now()->addMonths(3)])->count(),
            'total_inventory_value' => Drug::sum(DB::raw('total_sachets_in_stock * price_per_sachet')),
        ];
    }

    /**
     * Search drugs by name or generic name
     */
    public function searchDrugs(string $query): Collection
    {
        return Drug::search($query)->limit(20)->get();
    }

    /**
     * Get unique drug routes
     */
    public function getDrugRoutes(): array
    {
        return Drug::distinct()->pluck('drug_route')->toArray();
    }

    /**
     * Get unique drug groups/classes
     */
    public function getDrugGroups(): array
    {
        return Drug::distinct()->pluck('drug_group_class')->toArray();
    }

    /**
     * Bulk update stock for multiple drugs
     */
    public function bulkUpdateStock(array $items): array
    {
        try {
            DB::beginTransaction();

            $results = [];

            foreach ($items as $item) {
                $drug = Drug::findOrFail($item['drug_id']);
                $drug->total_sachets_in_stock += $item['quantity'];
                $drug->save();

                $results[] = [
                    'drug_id' => $drug->id,
                    'drug_name' => $drug->drug_name,
                    'new_stock' => $drug->total_sachets_in_stock
                ];
            }

            Log::info('Bulk stock update completed', ['items_updated' => count($results)]);

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk stock update failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }


    

}
