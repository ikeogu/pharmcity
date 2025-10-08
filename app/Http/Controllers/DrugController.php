<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDrugRequest;
use App\Http\Requests\UpdateDrugRequest;
use App\Services\DrugService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DrugController extends Controller
{

    public function __construct(
        protected readonly DrugService $drugService
    ) {}

    /**
     * Display a listing of the drugs.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search',
            'stock_status',
            'expiry_status',
            'drug_route',
            'drug_group_class',
            'sort_by',
            'sort_order'
        ]);

        $perPage = $request->input('per_page', 15);

        $drugs = $this->drugService->getAllDrugs($filters, $perPage);
        $stats = $this->drugService->getInventoryStats();

        return Inertia::render('Drugs/Index', [
            'drugs' => $drugs,
            'stats' => $stats,
            'filters' => $filters,
            'drugRoutes' => $this->drugService->getDrugRoutes(),
            'drugGroups' => $this->drugService->getDrugGroups(),
        ]);
    }

    /**
     * Show the form for creating a new drug.
     */
    public function create(): Response
    {
        return Inertia::render('Drugs/Create', [
            'doseUnits' => ['mls', 'mg', 'g', 'mcg', 'IU', 'drops', 'units'],
            'drugRoutes' => ['Tablet', 'Syrup', 'Intravenous', 'Intramuscular', 'Subcutaneous', 'Topical', 'Nasal', 'Ocular', 'Vaginal', 'Rectal', 'Otic', 'PO', 'other'],
            'packaging' => ['sachet', 'bottle', 'box', 'blister', 'tube', 'vial', 'ampoule'],
            'formOfItems' => ['card', 'tablet', 'capsule', 'strip', 'piece'],
            'sellAs' => ['sachet', 'card', 'tablet', 'bottle', 'piece'],
        ]);
    }

    /**
     * Store a newly created drug in storage.
     */
    public function store(StoreDrugRequest $request)
    {
        try {
            $drug = $this->drugService->createDrug($request->validated());

            return redirect()
                ->route('drugs.index')
                ->with('success', 'Drug created successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create drug: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified drug.
     */
    public function show(int $id): Response
    {
        $drug = $this->drugService->getDrugById($id);

        if (!$drug) {
            abort(404, 'Drug not found');
        }

        return Inertia::render('Drugs/Show', [
            'drug' => $drug,
        ]);
    }

    /**
     * Show the form for editing the specified drug.
     */
    public function edit(int $id): Response
    {
        $drug = $this->drugService->getDrugById($id);

        if (!$drug) {
            abort(404, 'Drug not found');
        }

        return Inertia::render('Drugs/Edit', [
            'drug' => $drug,
            'doseUnits' => ['mls', 'mg', 'g', 'mcg', 'IU', 'drops', 'units'],
            'drugRoutes' => ['Tablet', 'Syrup', 'Intravenous', 'Intramuscular', 'Subcutaneous', 'Topical', 'Nasal', 'Ocular', 'Vaginal', 'Rectal', 'Otic', 'PO', 'other'],
            'packaging' => ['sachet', 'bottle', 'box', 'blister', 'tube', 'vial', 'ampoule'],
            'formOfItems' => ['card', 'tablet', 'capsule', 'strip', 'piece'],
            'sellAs' => ['sachet', 'card', 'tablet', 'bottle', 'piece'],
        ]);
    }

    /**
     * Update the specified drug in storage.
     */
    public function update(UpdateDrugRequest $request, int $id)
    {
        try {
            $drug = $this->drugService->updateDrug($id, $request->validated());

            return redirect()
                ->back()
                ->with('success', 'Drug updated successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update drug: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified drug from storage.
     */
    public function destroy(int $id)
    {
        try {
            $this->drugService->deleteDrug($id);

            return redirect()
                ->route('drugs.index')
                ->with('success', 'Drug deleted successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete drug: ' . $e->getMessage());
        }
    }

    /**
     * Update drug stock
     */
    public function updateStock(Request $request, int $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'operation' => 'required|in:add,subtract,set'
        ]);

        try {
            $drug = $this->drugService->updateStock(
                $id,
                $request->quantity,
                $request->operation
            );

            return redirect()
                ->back()
                ->with('success', 'Stock updated successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update stock: ' . $e->getMessage());
        }
    }

    /**
     * Get expiring drugs
     */
    public function expiring(Request $request): Response
    {
        $days = $request->input('days', 90);
        $drugs = $this->drugService->getExpiringDrugs($days);

        return Inertia::render('Drugs/Expiring', [
            'drugs' => $drugs,
            'days' => $days,
        ]);
    }

    /**
     * Get low stock drugs
     */
    public function lowStock(Request $request): Response
    {
        $threshold = $request->input('threshold', 10);
        $drugs = $this->drugService->getLowStockDrugs($threshold);

        return Inertia::render('Drugs/LowStock', [
            'drugs' => $drugs,
            'threshold' => $threshold,
        ]);
    }

    /**
     * Search drugs (API endpoint for autocomplete)
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $drugs = $this->drugService->searchDrugs($query);

        return response()->json([
            'data' => $drugs
        ]);
    }

    /**
     * Bulk update stock
     */
    public function bulkUpdateStock(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity' => 'required|integer'
        ]);

        try {
            $results = $this->drugService->bulkUpdateStock($request->items);

            return redirect()
                ->back()
                ->with('success', 'Bulk stock update completed successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update stock: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft-deleted drug
     */
    public function restore(int $id)
    {
        try {
            $this->drugService->restoreDrug($id);

            return redirect()
                ->route('drugs.index')
                ->with('success', 'Drug restored successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to restore drug: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete drug
     */
    public function forceDestroy(int $id)
    {
        try {
            $this->drugService->forceDeleteDrug($id);

            return redirect()
                ->route('drugs.index')
                ->with('success', 'Drug permanently deleted!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to permanently delete drug: ' . $e->getMessage());
        }
    }
}
