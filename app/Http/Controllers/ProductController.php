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

            return view('admin_panel.product.add_product', compact('products'));
        } elseif ($user->usertype === 'distributor') {
            $products = \App\Models\DistributorProduct::where('distributor_id', $user->user_id)->get();

            return view('admin_panel.product.distributor_product_stock', compact('products'));
        }

        return redirect()->back();
    }

   

    public function store_product(Request $request)
    {
        // dd($request->toArray());
        // Validate the request
        $request->validate([
            'item_name' => 'required|string|max:255',
            'product_mode' => 'required|in:simple,measurements',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
        ]);

        // Generate item code
        $itemCode = Product::generateItemcodeNo();
        
        // Check if a soft-deleted product with this code exists
        $existingProduct = Product::withTrashed()->where('item_code', $itemCode)->first();
        
        // Prepare data based on product mode
        $data = [
            'admin_or_user_id' => Auth::id(),
            'item_name' => $request->item_name,
            'product_mode' => $request->product_mode,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
        ];

        // Set measurement fields based on product mode
        if ($request->product_mode === 'measurements') {
            $data['height'] = $request->height;
            $data['width'] = $request->width;
            $data['area'] = $request->area;
        } else {
            // For simple products, set measurement fields to null
            $data['height'] = null;
            $data['width'] = null;
            $data['area'] = null;
        }

        // If soft-deleted product exists, restore and update it
        if ($existingProduct && $existingProduct->trashed()) {
            $existingProduct->restore();
            $existingProduct->update($data);
        } else {
            // Create new product with item code
            $data['item_code'] = $itemCode;
            Product::create($data);
        }

        return back()->with('success', 'Product added successfully');
    }

    public function update_product(Request $request)
    {
        // Validate the request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'item_name' => 'required|string|max:255',
            'product_mode' => 'required|in:simple,measurements',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
        ]);

        // Get product ID from request
        $id = $request->product_id;

        // Prepare data based on product mode
        $data = [
            'item_name' => $request->item_name,
            'product_mode' => $request->product_mode,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
        ];

        // Set measurement fields based on product mode
        if ($request->product_mode === 'measurements') {
            $data['height'] = $request->height;
            $data['width'] = $request->width;
            $data['area'] = $request->area;
        } else {
            // For simple products, set measurement fields to null
            $data['height'] = null;
            $data['width'] = null;
            $data['area'] = null;
        }

        Product::where('id', $id)->update($data);

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
}