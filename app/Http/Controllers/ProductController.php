<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function product()
    {
        if (! Auth::check()) {
            return redirect()->back();
        }
        $user = Auth::user();
        if ($user->usertype === 'admin') {
            $products = Product::where('admin_or_user_id', $user->id)
                              ->whereNull('deleted_at')
                              ->get();
            $units = \App\Models\Unit::all();

            return view('admin_panel.product.add_product', compact('products', 'units'));
        } elseif ($user->usertype === 'distributor') {
            $products = \App\Models\DistributorProduct::where('distributor_id', $user->user_id)->get();
            $units = \App\Models\Unit::all();

            return view('admin_panel.product.distributor_product_stock', compact('products', 'units'));
        }

        return redirect()->back();
    }

   

    public function store_product(Request $request)
    {
        // Validate the request
        $request->validate([
            'item_name' => 'required|string|max:255',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Generate item code
        $itemCode = Product::generateItemcodeNo();
        
        // Check if a soft-deleted product with this code exists
        $existingProduct = Product::withTrashed()->where('item_code', $itemCode)->first();
        
        $data = [
            'admin_or_user_id' => Auth::id(),
            'item_name' => $request->item_name,
            'unit' => $request->unit,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
            'initial_stock' => $request->stock,
        ];

        // If soft-deleted product exists, restore and update it
        if ($existingProduct && $existingProduct->trashed()) {
            $existingProduct->restore();
            $existingProduct->update($data);
        } else {
            // Create new product with item code
            $data['item_code'] = $itemCode;
            Product::create($data);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Product added successfully'
            ]);
        }

        return back()->with('success', 'Product added successfully');
    }

    public function update_product(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'item_name' => 'required|string|max:255',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Get product ID from request
        $id = $request->product_id;

        $data = [
            'item_name' => $request->item_name,
            'unit' => $request->unit,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
            'initial_stock' => $request->stock,
        ];

        Product::where('id', $id)->update($data);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Product updated successfully');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $products = Product::all();

        return view('admin_panel.product.edit_product', compact('product', 'products'));
    }

    public function delete($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['status' => 'error', 'message' => 'Customer not found.']);
        }

        $product->delete();

        return response()->json(['status' => 'success', 'message' => 'Product deleted successfully.']);
    }

    public function store_unit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $unit = \App\Models\Unit::create([
            'name' => $request->name,
            'admin_or_user_id' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'success',
            'unit' => $unit
        ]);
    }
}
