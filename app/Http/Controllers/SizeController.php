<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SizeController extends Controller
{
    public function size()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            if ($userId) {
                $Sizes = Size::all();
            }

            return view('admin_panel.size.sizes', [
                'Sizes' => $Sizes,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_size(Request $request)
    {

        $request->validate([
            'size_name' => 'required',
        ]);

        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            Size::create([
                'admin_or_user_id' => $userId,
                'size_name' => $request->size_name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Size created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {

        $request->validate([
            'size_name' => 'required',
        ]);

        $size_id = $request->input('size_id');

        Size::where('id', $size_id)->update([
            'size_name' => $request->size_name,
        ]);

        return redirect()->back()->with('success', 'Size updated successfully');
    }

    public function delete($id)
    {
        $size = Size::find($id);

        if (! $size) {
            return response()->json(['status' => false, 'msg' => 'Data not found']);
        }

        $size->delete();

        return response()->json(['status' => true, 'msg' => 'Deleted']);
    }
}
