<?php

namespace App\Http\Controllers;

use App\Models\LocalSale;
use App\Models\Product;
use App\Models\StockOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockOutController extends Controller
{
    public function stockout(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Use current initial_stock as available stock (already adjusted by purchases/stockouts)
            $products = Product::select('id', 'item_name', 'unit', 'height', 'width', 'initial_stock')->get();
            foreach ($products as $product) {
                $product->available_stock = $product->initial_stock ?? 0;
            }

            // ✅ Eager load customer AND vendor
            $localSales = LocalSale::with(['customer', 'vendor'])
                ->select('id', 'invoice_number', 'customer_id', 'vendor_id', 'party_type', 'customer_shopname')
                ->orderBy('created_at', 'desc')
                ->get();

            $query = StockOut::with(['product', 'localSale.customer', 'localSale.vendor']);

            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            $stockOuts = $query->orderBy('created_at', 'desc')->get();

            return view('admin_panel.stockOut.stockout', [
                'stockOuts' => $stockOuts,
                'products' => $products,
                'localSales' => $localSales,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_stockout(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $request->validate([
                'local_sales_id' => 'required|exists:local_sales,id',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.current_stock' => 'required|numeric',
                'products.*.used_stock' => 'required|numeric', // Changed from close_stock to used_stock
            ]);

            foreach ($request->products as $product) {
                // Skip empty rows
                if (empty($product['product_id'])) {
                    continue;
                }

                $productId = intval($product['product_id']);
                $openingStock = floatval($product['current_stock']);
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
                $productModel = Product::find($productId);
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

    public function delete_stockout(Request $request)
    {
        $saleId = $request->sale_id; // 👈 JS se yehi aa rahi hai

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

    public function delete_job_stockout(Request $request)
    {
        $stockOuts = StockOut::where('local_sales_id', $request->job_id)->get();

        if ($stockOuts->count() > 0) {
            StockOut::where('local_sales_id', $request->job_id)->delete();

            return response()->json(['success' => 'All StockOut records deleted successfully']);
        }

        return response()->json(['error' => 'No records found'], 404);
    }
}
