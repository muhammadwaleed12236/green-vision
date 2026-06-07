<?php

namespace App\Http\Controllers;

use App\Models\PriceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceListController extends Controller
{
    // Main Page
    public function index()
    {
        $priceLists = PriceList::where('admin_or_user_id', Auth::id())
            ->orderBy('product_name')
            ->paginate(15);

        return view('admin_panel.price_list.index', compact('priceLists'));
    }

    // Get all items for AJAX
    public function getAll()
    {
        $items = PriceList::where('admin_or_user_id', Auth::id())
            ->orderBy('header')
            ->orderBy('sort_order')
            ->get();

        $grouped = $items->groupBy('header')->map(function($group, $header) {
            return [
                'header' => $header ?: 'General',
                'items' => $group->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'description' => $item->description,
                        'rate' => $item->rate,
                        'unit' => $item->unit,
                        'is_active' => $item->is_active,
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'total' => $items->count()
        ]);
    }

    // Store new item
    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'rate' => 'required|numeric|min:0',
            ]);

            $maxOrder = PriceList::where('admin_or_user_id', Auth::id())
                ->where('header', $request->header)
                ->max('sort_order') ?? 0;

            $item = PriceList::create([
                'admin_or_user_id' => Auth::id(),
                'header' => $request->header ?: null,
                'product_name' => $request->product_name,
                'description' => $request->description,
                'rate' => $request->rate,
                'unit' => $request->unit ?: 'per sqft',
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item added successfully!',
                'item' => $item
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Price List Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get single item
    public function show($id)
    {
        $item = PriceList::where('admin_or_user_id', Auth::id())->findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    // Update item
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'rate' => 'required|numeric|min:0',
            ]);

            $item = PriceList::where('admin_or_user_id', Auth::id())->findOrFail($id);

            $item->update([
                'header' => $request->header ?: null,
                'product_name' => $request->product_name,
                'description' => $request->description,
                'rate' => $request->rate,
                'unit' => $request->unit ?: 'per sqft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully!',
                'item' => $item
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Price List Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete item
    public function destroy($id)
    {
        $item = PriceList::where('admin_or_user_id', Auth::id())->findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully!'
        ]);
    }

    // Toggle active status
    public function toggleStatus($id)
    {
        $item = PriceList::where('admin_or_user_id', Auth::id())->findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => $item->is_active ? 'Item activated!' : 'Item deactivated!',
            'is_active' => $item->is_active
        ]);
    }

    // Quick View - Public rate list
    public function quickView()
    {
        $priceLists = PriceList::where('admin_or_user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('header')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('header');

        return view('admin_panel.price_list.quick_view', compact('priceLists'));
    }

    // Get unique headers for dropdown
    public function getHeaders()
    {
        $headers = PriceList::where('admin_or_user_id', Auth::id())
            ->whereNotNull('header')
            ->distinct()
            ->pluck('header');

        return response()->json(['headers' => $headers]);
    }
}
