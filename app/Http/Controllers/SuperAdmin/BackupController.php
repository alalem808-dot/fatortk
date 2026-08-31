<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\BackupService;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function __construct(private BackupService $backup) {}

    public function index()
    {
        $logs = DB::table('backup_logs')
            ->leftJoin('tenants', 'backup_logs.tenant_id', '=', 'tenants.id')
            ->select('backup_logs.*', 'tenants.company_name')
            ->orderByDesc('backup_logs.created_at')
            ->paginate(20);

        $stats = [
            'total'      => DB::table('backup_logs')->count(),
            'full'       => DB::table('backup_logs')->where('type', 'full')->count(),
            'tenant'     => DB::table('backup_logs')->where('type', 'tenant')->count(),
            'total_size' => (int) DB::table('backup_logs')->sum('size'),
        ];

        $tenants = Tenant::orderBy('company_name')->get();

        return view('super_admin.backups', compact('logs', 'stats', 'tenants'));
    }

    public function storeFullBackup()
    {
        try {
            $this->backup->backupFull();
            return back()->with('success', 'تم إنشاء النسخة الاحتياطية الكاملة بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل إنشاء النسخة: ' . $e->getMessage());
        }
    }

    public function storeTenantBackup()
    {
        request()->validate(['tenant_id' => 'required|exists:tenants,id']);
        $tenant = Tenant::findOrFail(request('tenant_id'));
        try {
            $this->backup->backupTenant($tenant);
            return back()->with('success', "تم إنشاء نسخة احتياطية لـ {$tenant->company_name} بنجاح.");
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل إنشاء النسخة: ' . $e->getMessage());
        }
    }

    public function download(int $id)
    {
        $log  = DB::table('backup_logs')->where('id', $id)->first();
        abort_if(!$log, 404, 'النسخة غير موجودة.');

        $full = storage_path("app/{$log->path}");
        abort_unless(file_exists($full), 404, 'الملف غير موجود.');

        return response()->download($full, $log->filename);
    }

    public function destroy(int $id)
    {
        $log  = DB::table('backup_logs')->where('id', $id)->first();
        abort_if(!$log, 404, 'النسخة غير موجودة.');
        $full = storage_path("app/{$log->path}");
        if (file_exists($full)) {
            @unlink($full);
        }
        DB::table('backup_logs')->where('id', $id)->delete();

        return back()->with('success', 'تم حذف النسخة الاحتياطية.');
    }

    public function prune()
    {
        $pruned = $this->backup->pruneOld(30);
        return back()->with('success', "تم حذف {$pruned} نسخة قديمة (أكثر من 30 يوم).");
    }
}
