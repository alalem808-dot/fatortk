<?php
namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    private function checkEnabled(): void
    {
        $tenant = auth()->user()->load('tenant')->tenant;
        abort_if(!$tenant->currencies_enabled, 403, 'محور العملات غير مفعّل لهذا الحساب.');
    }

    public function index()
    {
        $this->checkEnabled();
        $currencies = Currency::where('tenant_id', auth()->user()->tenant_id)
            ->with(['exchangeRates' => fn($q) => $q->latest('date')->limit(1)])
            ->get();
        return view('currencies.index', compact('currencies'));
    }

    public function store(Request $request)
    {
        $this->checkEnabled();
        $tenantId = auth()->user()->tenant_id;
        $request->validate([
            'code'   => 'required|string|max:10',
            'name'   => 'required|string|max:100',
            'symbol' => 'nullable|string|max:10',
            'rate'   => 'required|numeric|min:0.000001',
        ]);

        // إذا كانت أول عملة → تصبح الأساسية
        $isFirst = Currency::where('tenant_id', $tenantId)->count() === 0;

        $currency = Currency::create([
            'tenant_id' => $tenantId,
            'code'      => strtoupper($request->code),
            'name'      => $request->name,
            'symbol'    => $request->symbol,
            'is_base'   => $isFirst || $request->boolean('is_base'),
            'is_active' => true,
        ]);

        // إذا أصبحت أساسية → ألغِ الأساسية القديمة
        if ($currency->is_base) {
            Currency::where('tenant_id', $tenantId)->where('id', '!=', $currency->id)
                ->update(['is_base' => false]);
        }

        ExchangeRate::create(['currency_id' => $currency->id, 'rate' => $request->rate, 'date' => today()]);

        return back()->with('success', 'تم إضافة العملة.');
    }

    public function setBase(Currency $currency)
    {
        $this->checkEnabled();
        abort_if($currency->tenant_id !== auth()->user()->tenant_id, 403);
        Currency::where('tenant_id', $currency->tenant_id)->update(['is_base' => false]);
        $currency->update(['is_base' => true]);
        return back()->with('success', 'تم تعيين العملة الأساسية.');
    }

    public function addRate(Request $request, Currency $currency)
    {
        $this->checkEnabled();
        abort_if($currency->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['rate' => 'required|numeric|min:0.000001', 'date' => 'required|date']);
        ExchangeRate::create(['currency_id' => $currency->id, 'rate' => $request->rate, 'date' => $request->date]);
        return back()->with('success', 'تم إضافة سعر الصرف.');
    }

    public function destroy(Currency $currency)
    {
        $this->checkEnabled();
        abort_if($currency->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($currency->is_base, 403, 'لا يمكن حذف العملة الأساسية.');
        $currency->delete();
        return back()->with('success', 'تم حذف العملة.');
    }

    // API: جلب سعر الصرف الحالي
    public function getRate(Request $request)
    {
        $currency = Currency::where('tenant_id', auth()->user()->tenant_id)
            ->where('code', $request->code)->first();
        return response()->json(['rate' => $currency ? $currency->latest_rate : 1]);
    }
}
