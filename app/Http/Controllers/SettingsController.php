<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        return view('settings.index', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'company_name'  => 'required|string|max:255',
            'email'         => 'required|email',
            'phone'         => 'nullable|string',
            'address'       => 'nullable|string',
            'tax_number'    => 'nullable|string',
            'signer_name'   => 'nullable|string|max:255',
            'signer_title'  => 'nullable|string|max:255',
            'stamp_image'   => 'nullable|image|max:2048',
            'signature_image' => 'nullable|string',
        ]);

        $data = $request->except(['logo', 'stamp_image', 'signature_image', '_token', '_method',
            'invoice_prefix', 'default_currency', 'default_tax_rate', 'terms_conditions']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('stamp_image')) {
            $data['stamp_image'] = $request->file('stamp_image')->store('stamps', 'public');
        }

        if ($request->filled('signature_image')) {
            $data['signature_image'] = $request->signature_image;
        }

        $tenant->update($data);

        foreach (['invoice_prefix', 'default_currency', 'default_tax_rate', 'terms_conditions'] as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'key' => $key],
                    ['value' => $request->$key]
                );
            }
        }

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
