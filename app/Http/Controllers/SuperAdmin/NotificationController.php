<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function index()
    {
        // المشتركون الذين تنتهي اشتراكاتهم خلال 7 أيام
        $expiringIn7 = Tenant::where('status', 'active')
            ->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [now(), now()->addDays(7)])
            ->get();
        
        // المشتركون الذين تنتهي اشتراكاتهم خلال 30 يوم
        $expiringIn30 = Tenant::where('status', 'active')
            ->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [now()->addDays(8), now()->addDays(30)])
            ->get();
        
        // الحسابات المنتهية (expired)
        $expired = Tenant::where('status', 'active')
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<', now())
            ->get();
        
        // الحسابات التجريبية التي مرّ عليها أكثر من 14 يوم
        $oldTrials = Tenant::where('status', 'trial')
            ->where('created_at', '<', now()->subDays(14))
            ->get();
        
        // الحسابات غير النشطة (لم يدخل أحد منها خلال 60 يوم)
        $inactiveAccounts = Tenant::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('last_login_at')
                  ->orWhere('last_login_at', '<', now()->subDays(60));
            })
            ->get();
        
        $tenants = Tenant::where('status', '!=', 'suspended')->orderBy('company_name')->get();
        
        return view('super_admin.notifications', compact(
            'expiringIn7', 'expiringIn30', 'expired', 'oldTrials', 'inactiveAccounts', 'tenants'
        ));
    }
    
    public function send(Request $request)
    {
        $request->validate([
            'recipient'  => 'required|in:single,all,active,trial',
            'tenant_id'  => 'nullable|exists:tenants,id',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);
        
        $tenants = collect();
        
        if ($request->recipient === 'single') {
            $tenants = Tenant::where('id', $request->tenant_id)->get();
        } elseif ($request->recipient === 'all') {
            $tenants = Tenant::where('status', '!=', 'suspended')->get();
        } elseif ($request->recipient === 'active') {
            $tenants = Tenant::where('status', 'active')->get();
        } elseif ($request->recipient === 'trial') {
            $tenants = Tenant::where('status', 'trial')->get();
        }
        
        $sent = 0;
        $failed = 0;
        
        foreach ($tenants as $tenant) {
            try {
                Mail::raw($request->message, function ($mail) use ($tenant, $request) {
                    $mail->to($tenant->email)
                         ->subject($request->subject);
                });
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }
        
        \App\Models\ActivityLog::log(
            'notification_sent',
            "تم إرسال إشعار '{$request->subject}' لـ {$sent} مشترك" . ($failed ? " (فشل: {$failed})" : '')
        );
        
        $msg = "تم إرسال الإشعار لـ {$sent} مشترك بنجاح.";
        if ($failed) $msg .= " فشل الإرسال لـ {$failed}.";
        
        return back()->with('success', $msg);
    }
    
    public function extend(Request $request, Tenant $tenant)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:36',
        ]);
        
        $from = $tenant->subscription_expires_at && $tenant->subscription_expires_at->gt(now())
            ? $tenant->subscription_expires_at
            : now();
        
        $newExpiry = $from->addMonths($request->months);
        
        $old = $tenant->subscription_expires_at?->format('Y-m-d') ?? 'غير محدد';
        $tenant->update([
            'subscription_expires_at' => $newExpiry,
            'status' => 'active',
        ]);
        
        \App\Models\ActivityLog::log(
            'subscription_extended',
            "تم تمديد اشتراك {$tenant->company_name} من {$old} إلى {$newExpiry->format('Y-m-d')}",
            $tenant,
            ['from' => $old, 'to' => $newExpiry->format('Y-m-d')]
        );
        
        return back()->with('success', "تم تمديد اشتراك {$tenant->company_name} حتى {$newExpiry->format('Y-m-d')}.");
    }
}
