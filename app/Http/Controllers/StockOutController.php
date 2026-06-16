<?php

namespace App\Http\Controllers;

use App\Models\LocalSale;
use App\Models\Product;
use App\Models\StockOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    /**
     * Display the StockOut list
     */
    public function stockout(Request $request)
    {
        if (Auth::id()) {
            // Aggregate at DB level — single lightweight query
            $stockOutSummaries = StockOut::select(
                    'local_sales_id',
                    DB::raw('COUNT(*) as item_count'),
                    DB::raw('SUM(total_stock) as total_stock_out'),
                    DB::raw('MAX(created_at) as latest_date')
                )
                ->groupBy('local_sales_id')
                ->orderByDesc('latest_date')
                ->get();

            // Load only the needed LocalSale records with minimal columns
            $saleIds = $stockOutSummaries->pluck('local_sales_id');
            $localSales = LocalSale::with(['customer:id,customer_name', 'vendor:id,Party_name'])
                ->select('id', 'invoice_number', 'customer_id', 'vendor_id', 'party_type', 'customer_shopname')
                ->whereIn('id', $saleIds)
                ->get()
                ->keyBy('id');

            return view('admin_panel.stockOut.stockout', [
                'stockOutSummaries' => $stockOutSummaries,
                'localSales' => $localSales,
            ]);
        } else {
            return redirect()->back();
        }
    }

    /**
     * Store a new StockOut record
     */
    public function store_stockout(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $request->validate([
                'local_sales_id' => 'required|exists:local_sales,id',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.used_stock' => 'required|numeric',
            ]);

            foreach ($request->products as $product) {
                // Skip empty rows
                if (empty($product['product_id'])) {
                    continue;
                }

                $productId = intval($product['product_id']);
                $productModel = Product::find($productId);
                
                // Get opening stock from product's initial_stock
                $openingStock = floatval($productModel->initial_stock ?? 0);
                $usedStock = floatval($product['used_stock']);

                // ✅ CORRECT FORMULA: Opening - Used = Closing (Remaining)
                $closingStock = $openingStock - $usedStock;

                // ✅ Ensure closing stock is not negative
                if ($closingStock < 0) {
                    $closingStock = 0;
                }

                // Save stock out record
                StockOut::create([
                    'admin_or_user_id' => $userId,
                    'product_id' => $productId,
                    'local_sales_id' => $request->local_sales_id,
                    'current_stock' => $openingStock,    // Opening Stock
                    'close_stock' => $closingStock,      // Closing Stock (Remaining)
                    'total_stock' => $usedStock,         // Used Stock
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // ✅ Update product's initial_stock with CLOSING stock (remaining stock)
                if ($productModel) {
                    // Closing stock becomes the new opening stock for next time
                    $productModel->initial_stock = $closingStock;
                    $productModel->save();
                }
            }

            return redirect()->back()->with('success', 'StockOut created successfully');
        } else {
            return redirect()->back();
        }
    }

    /**
     * Update an existing StockOut record
     */
    public function update_stockout(Request $request)
    {
        $request->validate([
            'stockout_id' => 'required|exists:stock_outs,id',
            'product_id' => 'required|exists:products,id',
            'local_sales_id' => 'required|exists:local_sales,id',
            'current_stock' => 'required|numeric',
            'close_stock' => 'required|numeric',
        ]);

        $total_stock = $request->current_stock - $request->close_stock;

        StockOut::where('id', $request->stockout_id)->update([
            'product_id' => $request->product_id,
            'local_sales_id' => $request->local_sales_id,
            'current_stock' => $request->current_stock,
            'close_stock' => $request->close_stock,
            'total_stock' => $total_stock,
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'StockOut updated successfully');
    }

    /**
     * Delete a single StockOut record
     */
    public function delete_stockout(Request $request)
    {
        $saleId = $request->sale_id;

        $deleted = StockOut::where('local_sales_id', $saleId)->delete();

        if ($deleted) {
            return response()->json([
                'success' => 'StockOut deleted successfully',
            ]);
        }

        return response()->json([
            'error' => 'StockOut not found',
        ], 404);
    }

    /**
     * Display StockOut details for a specific job
     */
    public function stockout_details($jobId)
    {
        if (Auth::id()) {
            $stockOuts = StockOut::with(['product', 'localSale.customer', 'localSale.vendor'])
                ->where('local_sales_id', $jobId)
                ->get();

            $localSale = LocalSale::with(['customer', 'vendor'])->find($jobId);

            return view('admin_panel.stockOut.stockout_details', [
                'stockOuts' => $stockOuts,
                'localSale' => $localSale,
            ]);
        } else {
            return redirect()->back();
        }
    }

    /**
     * Delete all StockOut records for a job
     */
    public function delete_job_stockout(Request $request)
    {
        $stockOuts = StockOut::where('local_sales_id', $request->job_id)->get();

        if ($stockOuts->count() > 0) {
            StockOut::where('local_sales_id', $request->job_id)->delete();

            return response()->json(['success' => 'All StockOut records deleted successfully']);
        }

        return response()->json(['error' => 'No records found'], 404);
    }

    /**
     * Get invoices by date
     */
    public function getInvoicesByDate(Request $request)
    {
        $date = $request->date ?? now()->format('Y-m-d');

        $sales = LocalSale::with(['customer', 'vendor'])
            ->whereDate('created_at', $date)
            ->select('id', 'invoice_number', 'job_number', 'customer_id', 'vendor_id', 'party_type', 'customer_shopname')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'job_number' => $sale->job_number,
                    'party_type' => $sale->party_type,
                    'customer_name' => $this->getCustomerName($sale),
                ];
            });

        return response()->json($sales);
    }

    /**
     * Get products for modal dropdown via AJAX
     */
    public function getProducts()
    {
        $products = Product::select('id', 'item_name', 'unit', 'initial_stock')->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'item_name' => $product->item_name,
                    'unit' => $product->unit,
                    'available_stock' => $product->initial_stock ?? 0,
                ];
            });

        return response()->json($products);
    }

    /**
     * Helper function to get customer name based on party type
     */
    private function getCustomerName($sale)
    {
        if ($sale->party_type === 'customer') {
            return $sale->customer->customer_name ?? 'N/A';
        } elseif ($sale->party_type === 'vendor') {
            return $sale->vendor->Party_name ?? 'N/A';
        } else {
            return $sale->customer_shopname ?? 'Walk-in';
        }
    }
}