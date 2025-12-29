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
    public function stockout()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Add initial_stock to the query
            $products = Product::select('id', 'item_name', 'height', 'width', 'initial_stock')
                ->get();

            $localSales = LocalSale::with('customer')
                ->select('id', 'invoice_number', 'customer_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $stockOuts = StockOut::with(['product', 'localSale.customer'])
                ->orderBy('created_at', 'desc')
                ->get();

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
                'products.*.close_stock' => 'required|numeric',
            ]);

            foreach ($request->products as $product) {
                // Skip empty rows
                if (empty($product['product_id'])) {
                    continue;
                }

                $currentStock = floatval($product['current_stock']);
                $closeStock = floatval($product['close_stock']);
                $usedStock = $currentStock - $closeStock;

                // Save stock out record
                StockOut::create([
                    'admin_or_user_id' => $userId,
                    'product_id' => $product['product_id'],
                    'local_sales_id' => $request->local_sales_id,
                    'current_stock' => $currentStock,
                    'close_stock' => $closeStock,
                    'total_stock' => $usedStock,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Update product's initial_stock with closing stock (NOT used stock)
                $productModel = Product::find($product['product_id']);
                if ($productModel) {
                    // The closing stock becomes the new initial stock for next time
                    $productModel->initial_stock = $closeStock;
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
        $stockout = StockOut::find($request->id);

        if ($stockout) {
            $stockout->delete();

            return response()->json(['success' => 'StockOut deleted successfully']);
        }

        return response()->json(['error' => 'StockOut not found'], 404);
    }

    public function stockout_details($jobId)
    {
        if (Auth::id()) {
            $stockOuts = StockOut::with(['product', 'localSale.customer'])
                ->where('local_sales_id', $jobId)
                ->get();

            $localSale = LocalSale::with('customer')->find($jobId);

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
