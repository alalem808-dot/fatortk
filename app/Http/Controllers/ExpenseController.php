<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $expenses = Expense::where('tenant_id', $tenantId)
            ->with('category')
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->from, fn($q) => $q->where('expense_date', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->where('expense_date', '<=', $request->to))
            ->latest('expense_date')->paginate(20);

        $categories = ExpenseCategory::where('tenant_id', $tenantId)->get();
        $total = Expense::where('tenant_id', $tenantId)
            ->when($request->from, fn($q) => $q->where('expense_date', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->where('expense_date', '<=', $request->to))
            ->sum(\Illuminate\Support\Facades\DB::raw('amount * exchange_rate'));

        return view('expenses.index', compact('expenses', 'categories', 'total'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('tenant_id', auth()->user()->tenant_id)->get();
        $currencies = \App\Models\Currency::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();
        return view('expenses.create', compact('categories', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string|max:50',
        ]);

        $data = $request->only('description','amount','currency','exchange_rate','payment_method','expense_date','notes','category_id');
        $data['tenant_id']     = auth()->user()->tenant_id;
        $data['created_by']    = auth()->id();
        $data['currency']      = $request->currency ?? 'SDG';
        $data['exchange_rate'] = $request->exchange_rate ?? 1;

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', 'تم تسجيل المصروف.');
    }

    public function edit(Expense $expense)
    {
        abort_if($expense->tenant_id !== auth()->user()->tenant_id, 403);
        $categories = ExpenseCategory::where('tenant_id', auth()->user()->tenant_id)->get();
        $currencies = \App\Models\Currency::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();
        return view('expenses.edit', compact('expense', 'categories', 'currencies'));
    }

    public function update(Request $request, Expense $expense)
    {
        abort_if($expense->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate([
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string|max:50',
        ]);
        $data = $request->only('description','amount','currency','exchange_rate','payment_method','expense_date','notes','category_id');
        if ($request->hasFile('attachment')) {
            // OPS-03 Fix: حذف المرفق القديم قبل رفع الجديد
            if ($expense->attachment) {
                Storage::disk('public')->delete($expense->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }
        $expense->update($data);
        return redirect()->route('expenses.index')->with('success', 'تم تحديث المصروف.');
    }

    public function destroy(Expense $expense)
    {
        abort_if($expense->tenant_id !== auth()->user()->tenant_id, 403);
        $expense->delete();
        return back()->with('success', 'تم حذف المصروف.');
    }

    // إدارة الفئات
    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        ExpenseCategory::create(['tenant_id' => auth()->user()->tenant_id, 'name' => $request->name]);
        return back()->with('success', 'تم إضافة الفئة.');
    }

    public function destroyCategory(ExpenseCategory $category)
    {
        abort_if($category->tenant_id !== auth()->user()->tenant_id, 403);
        $category->delete();
        return back()->with('success', 'تم حذف الفئة.');
    }
}
