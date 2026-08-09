<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $customers = Customer::where('tenant_id', $tenantId)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"))
            ->withCount('invoices')
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function create() { return view('customers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Customer::create(array_merge($request->all(), ['tenant_id' => auth()->user()->tenant_id]));
        return redirect()->route('customers.index')->with('success', 'تم إضافة العميل.');
    }

    public function show(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        $customer->load(['invoices' => fn($q) => $q->latest()->take(10)]);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['name' => 'required|string|max:255']);
        $customer->update($request->all());
        return redirect()->route('customers.index')->with('success', 'تم تحديث العميل.');
    }

    public function destroy(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'تم حذف العميل.');
    }
}
