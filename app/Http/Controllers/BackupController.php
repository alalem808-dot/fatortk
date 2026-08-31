<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function __construct(private BackupService $backup) {}

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $logs = DB::table('backup_logs')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('backups.index', compact('logs'));
    }

    public function store()
    {
        try {
            $path = $this->backup->backupTenant(auth()->user()->tenant);
            return back()->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل إنشاء النسخة: ' . $e->getMessage());
        }
    }

    public function download(int $id)
    {
        $log = DB::table('backup_logs')
            ->where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        abort_if(!$log, 404, 'النسخة غير موجودة.');

        // حماية من Path Traversal: التحقق أن المسار داخل مجلد النسخ فقط
        $basePath = realpath(storage_path('app/backups'));
        $full     = realpath(storage_path("app/{$log->path}"));

        abort_unless(
            $full !== false && $basePath !== false && str_starts_with($full, $basePath . DIRECTORY_SEPARATOR),
            403,
            'مسار الملف غير مسموح.'
        );

        abort_unless(file_exists($full), 404, 'الملف غير موجود.');

        return response()->download($full, $log->filename);
    }

    public function destroy(int $id)
    {
        $log = DB::table('backup_logs')
            ->where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        abort_if(!$log, 404, 'النسخة غير موجودة.');

        // حماية من Path Traversal قبل الحذف
        $basePath = realpath(storage_path('app/backups'));
        $full     = realpath(storage_path("app/{$log->path}"));

        if ($full !== false && $basePath !== false && str_starts_with($full, $basePath . DIRECTORY_SEPARATOR)) {
            if (file_exists($full)) {
                if (!unlink($full)) {
                    return back()->with('error', 'تعذّر حذف الملف من القرص، يرجى التحقق من الصلاحيات.');
                }
            }
        }

        DB::table('backup_logs')->where('id', $id)->delete();

        return back()->with('success', 'تم حذف النسخة الاحتياطية.');
    }
}
