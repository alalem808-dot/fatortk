<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Tenant::where('id', Auth::user()->tenant_id)->get();
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string',
        ]);

        $company = Tenant::create($request->only([
            'company_name', 'email', 'phone', 'address', 'tax_number'
        ]));

        return redirect()->route('companies.index')->with('success', 'تم إضافة الشركة بنجاح');
    }

    public function edit(Tenant $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Tenant $company)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string',
        ]);

        $company->update($request->only([
            'company_name', 'email', 'phone', 'address', 'tax_number'
        ]));

        return redirect()->route('companies.index')->with('success', 'تم تحديث الشركة بنجاح');
    }

    public function destroy(Tenant $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'تم حذف الشركة بنجاح');
    }
}
