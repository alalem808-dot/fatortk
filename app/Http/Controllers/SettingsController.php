<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $currencies = \App\Models\GlobalCurrency::where('is_active', true)->orderBy('sort_order')->get();
        return view('settings.index', compact('tenant', 'currencies'));
    }

    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'company_name'    => 'required|string|max:255',
            'email'           => 'required|email',
            'phone'           => 'nullable|string',
            'address'         => 'nullable|string',
            'tax_number'      => 'nullable|string',
            'signer_name'     => 'nullable|string|max:255',
            'signer_title'    => 'nullable|string|max:255',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'stamp_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'signature_image' => ['nullable', 'string', 'max:2000000', function ($attribute, $value, $fail) {
                // يجب أن تكون قيمة base64 صالحة لصورة PNG أو JPEG
                if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $value)) {
                    $fail('صورة التوقيع يجب أن تكون صورة PNG أو JPEG بصيغة base64.');
                }
            }],
        ]);

        $data = $request->only([
            'company_name', 'email', 'phone', 'address', 'tax_number',
            'signer_name', 'signer_title',
        ]);

        // رفع شعار جديد
        if ($request->hasFile('logo')) {
            if ($tenant->logo) Storage::disk('public')->delete($tenant->logo);
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // حذف الشعار
        if ($request->boolean('remove_logo') && $tenant->logo) {
            Storage::disk('public')->delete($tenant->logo);
            $data['logo'] = null;
        }

        // رفع ختم جديد
        if ($request->hasFile('stamp_image')) {
            if ($tenant->stamp_image) Storage::disk('public')->delete($tenant->stamp_image);
            $data['stamp_image'] = $request->file('stamp_image')->store('stamps', 'public');
        }

        // حذف الختم
        if ($request->boolean('remove_stamp') && $tenant->stamp_image) {
            Storage::disk('public')->delete($tenant->stamp_image);
            $data['stamp_image'] = null;
        }

        // حذف التوقيع
        if ($request->boolean('remove_signature')) {
            $data['signature_image'] = null;
        } elseif ($request->filled('signature_image')) {
            $data['signature_image'] = $request->signature_image;
        }

        $tenant->update($data);

        foreach (['invoice_prefix', 'default_currency', 'default_tax_rate', 'terms_conditions', 'allow_negative_stock', 'cost_price_method'] as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'key' => $key],
                    ['value' => $request->$key]
                );
            }
        }

        // allow_negative_stock تأتي كـ checkbox فلا توجد في الطلب عند عدم التحديد
        if (!$request->has('allow_negative_stock')) {
            Setting::updateOrCreate(
                ['tenant_id' => $tenant->id, 'key' => 'allow_negative_stock'],
                ['value' => '0']
            );
        }

        return back()->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}
