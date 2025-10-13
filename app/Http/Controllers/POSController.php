<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Services\POSService;
use App\Models\Sale;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class POSController extends Controller
{
    /**
     * Inject POSService
     */
    public function __construct(
        protected POSService $posService
    ) {}

    /**
     * Display the POS interface
     *
     * @return Response
     */
    public function index(): Response
    {
        $stats = $this->posService->getTodayStats();

        return Inertia::render('POS/Index', [
            'recentSales' => $this->posService->getRecentSales(10),
            'todaySales' => $stats['total_sales'],
            'todayTransactions' => $stats['total_transactions'],
        ]);
    }

    /**
     * Search for drugs by name, generic name, or barcode
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchDrugs(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        try {
            $drugs = $this->posService->searchDrugs($request->input('query'));

            return response()->json([
                'success' => true,
                'drugs' => $drugs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process and store a new sale transaction
     *
     * @param StoreSaleRequest $request
     * @return JsonResponse
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        //dd($request->validated());
        try {
            $sale = $this->posService->processSale($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale' => $sale,
                'invoice_number' => $sale->invoice_number,
            ], 201);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'insufficient_stock'
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'prescription_required'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Sale processing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process sale: ' . $e->getMessage(),
                'error' => 'sale_failed'
            ], 500);
        }
    }

    /**
     * Display a specific sale with full details
     *
     * @param Sale $sale
     * @return Response
     */
    public function show(Sale $sale): Response
    {
        return Inertia::render('POS/SalesShow', [
            'sale' => $sale->load([
                'items.drug',
                'items.batch',
                'customer',
                'user',
                'prescription.items'
            ])
        ]);
    }

    /**
     * Display printable receipt for a sale
     *
     * @param Sale $sale
     * @return Response
     */
    public function print(Sale $sale): Response
    {
        return Inertia::render('POS/Receipt', [
            'sale' => $sale->load([
                'items.drug',
                'items.batch',
                'customer',
                'user'
            ])
        ]);
    }

    /**
     * Hold a sale for later completion
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function holdSale(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'patient_id' => 'nullable|exists:patients,id',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $sale = $this->posService->holdSale($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Sale held successfully',
                'sale' => $sale,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hold sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve all held/pending sales
     *
     */
    public function retrieveHeldSales()
    {

        $sales = $this->posService->getHeldSales();

        return inertia('POS/HoldSales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Cancel a sale and restore inventory
     *
     * @param Sale $sale
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelSale(Sale $sale, Request $request): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->posService->cancelSale($sale->id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Sale cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for customers by name, phone, or email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function customers(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'required|string|min:2',
        ]);

        try {
            $customers = $this->posService->searchCustomers($request->input('search'));

            return response()->json([
                'success' => true,
                'customers' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active prescriptions for a customer
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function prescriptions(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'nullable|exists:patients,id',
        ]);

        try {
            $prescriptions = $this->posService->getActivePrescriptions(
                $request->input('customer_id')
            );

            return response()->json([
                'success' => true,
                'prescriptions' => $prescriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve prescriptions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a sale by invoice number
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSaleByInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_number' => 'required|string|exists:sales,invoice_number',
        ]);

        try {
            $sale = $this->posService->getSaleByInvoice($request->input('invoice_number'));

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'sale' => $sale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate sales report for a date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function salesReport(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $report = $this->posService->generateSalesReport(
                new \DateTime($request->input('start_date')),
                new \DateTime($request->input('end_date'))
            );

            return response()->json([
                'success' => true,
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's sales statistics
     *
     * @return JsonResponse
     */
    public function todayStats(): JsonResponse
    {
        try {
            $stats = $this->posService->getTodayStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Allocate optimal batch for item using FIFO
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function allocateBatch(Request $request): JsonResponse
    {
        $request->validate([
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $allocation = $this->posService->allocateBatchForItem(
                $request->input('drug_id'),
                $request->input('quantity')
            );

            if (!$allocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock or no valid batch available'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'allocation' => $allocation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch allocation failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function sales(Request $request)
    {

        $query = Sale::query();

        // Optional start_date and end_date filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $query->latest()->paginate(15)->withQueryString();

        return inertia('POS/SalesIndex', [
            'sales' => $sales,
            'filters' => $request->only('start_date', 'end_date'),
        ]);
    }

    public function resume(Sale $sale)
    {
        $sale->load('items.drug');

        return inertia('POS/ResumeSale', [
            'sale' => $sale,
        ]);
    }

    public function finalize(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'status' => 'required|string',
            'payment_method' => 'nullable|string',
            'total' => 'required|numeric',
        ]);

        $sale->update([
            'status' => $validated['status'],
            'payment_method' => $validated['payment_method'] ?? $sale->payment_method,
            'total' => $validated['total'],
        ]);

        // Optionally update items if changed
        foreach ($validated['items'] as $item) {
            $saleItem = $sale->items()->find($item['id']);
            if ($saleItem) {
                $saleItem->update([
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        }

        return redirect()->route('pos.sales.show', $sale->id)
            ->with('success', 'Sale completed successfully.');
    }
}
