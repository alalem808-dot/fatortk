<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = InvoiceTemplate::where('tenant_id', auth()->user()->tenant_id)->get();
        return view('templates.index', compact('templates'));
    }

    public function create() { return view('templates.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $tenantId = auth()->user()->tenant_id;

        if ($request->is_default) {
            InvoiceTemplate::where('tenant_id', $tenantId)->update(['is_default' => false]);
        }

        InvoiceTemplate::create(array_merge($request->all(), ['tenant_id' => $tenantId]));
        return redirect()->route('templates.index')->with('success', 'تم إنشاء القالب.');
    }

    public function show(InvoiceTemplate $template)
    {
        abort_if($template->tenant_id !== auth()->user()->tenant_id, 403);
        return redirect()->route('templates.edit', $template);
    }

    public function edit(InvoiceTemplate $template)
    {
        abort_if($template->tenant_id !== auth()->user()->tenant_id, 403);
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, InvoiceTemplate $template)
    {
        abort_if($template->tenant_id !== auth()->user()->tenant_id, 403);
        $tenantId = auth()->user()->tenant_id;

        if ($request->is_default) {
            InvoiceTemplate::where('tenant_id', $tenantId)->where('id', '!=', $template->id)->update(['is_default' => false]);
        }

        $template->update($request->all());
        return redirect()->route('templates.index')->with('success', 'تم تحديث القالب.');
    }

    public function destroy(InvoiceTemplate $template)
    {
        abort_if($template->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($template->is_default, 403, 'لا يمكن حذف القالب الافتراضي.');
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'تم حذف القالب.');
    }
}
