<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.company');
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_address_2' => 'nullable|string',
            'company_website' => 'nullable|string|max:255',
            'company_social' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'secondary_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'invoice_terms' => 'nullable|string',
        ]);

        Setting::set('company_name', $request->company_name);
        Setting::set('company_phone', $request->company_phone);
        Setting::set('company_address', $request->company_address);
        Setting::set('company_address_2', $request->company_address_2);
        Setting::set('company_website', $request->company_website);
        Setting::set('company_social', $request->company_social);
        Setting::set('invoice_terms', $request->invoice_terms);



        if ($request->hasFile('company_logo')) {
            $oldLogo = Setting::get('company_logo');
            
            // Delete old logo if it exists
            if ($oldLogo) {
                if (file_exists(public_path('storage/' . $oldLogo))) {
                    @unlink(public_path('storage/' . $oldLogo));
                }
                if (Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }
            
            $file = $request->file('company_logo');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());
            
            // Move directly to public/storage/logos to bypass symlink issues on shared hosting
            $file->move(public_path('storage/logos'), $filename);
            
            Setting::set('company_logo', 'logos/' . $filename);
        }

        if ($request->hasFile('secondary_logo')) {
            $oldSecLogo = Setting::get('secondary_logo');
            
            if ($oldSecLogo) {
                if (file_exists(public_path('storage/' . $oldSecLogo))) {
                    @unlink(public_path('storage/' . $oldSecLogo));
                }
                if (Storage::disk('public')->exists($oldSecLogo)) {
                    Storage::disk('public')->delete($oldSecLogo);
                }
            }
            
            $fileSec = $request->file('secondary_logo');
            $filenameSec = time() . '_sec_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $fileSec->getClientOriginalName());
            
            $fileSec->move(public_path('storage/logos'), $filenameSec);
            
            Setting::set('secondary_logo', 'logos/' . $filenameSec);
        }

        return redirect()->route('settings.company.edit')
            ->with('success', 'Company settings saved successfully.');
    }
}
