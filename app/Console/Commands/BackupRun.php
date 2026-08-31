<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupRun extends Command
{
    protected $signature   = 'backup:run {--type=full : full|tenants|tenant} {--tenant= : tenant id for single tenant backup} {--prune=30 : حذف النسخ أقدم من X يوم}';
    protected $description = 'تشغيل النسخ الاحتياطي الروتيني';

    public function handle(BackupService $backup): int
    {
        $type = $this->option('type');

        if ($type === 'full') {
            $this->info('جاري إنشاء نسخة احتياطية كاملة...');
            try {
                $path = $backup->backupFull();
                $this->info("✓ تم: {$path}");
            } catch (\Throwable $e) {
                $this->error('فشل: ' . $e->getMessage());
                return self::FAILURE;
            }
        } elseif ($type === 'tenant') {
            $tenantId = $this->option('tenant');
            if (!$tenantId) {
                $this->error('يجب تحديد --tenant=ID');
                return self::FAILURE;
            }
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("لم يُعثر على مشترك بالرقم: {$tenantId}");
                return self::FAILURE;
            }
            $this->info("جاري نسخ بيانات: {$tenant->company_name}");
            try {
                $path = $backup->backupTenant($tenant);
                $this->info("✓ تم: {$path}");
            } catch (\Throwable $e) {
                $this->error('فشل: ' . $e->getMessage());
                return self::FAILURE;
            }
        } elseif ($type === 'tenants') {
            $tenants = Tenant::where('status', '!=', 'suspended')->get();
            $this->info("جاري نسخ {$tenants->count()} مشترك...");
            $bar = $this->output->createProgressBar($tenants->count());
            foreach ($tenants as $tenant) {
                try {
                    $backup->backupTenant($tenant);
                } catch (\Throwable $e) {
                    $this->warn("فشل {$tenant->subdomain}: " . $e->getMessage());
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        // حذف النسخ القديمة
        $days    = (int) $this->option('prune');
        $pruned  = $backup->pruneOld($days);
        if ($pruned > 0) $this->info("تم حذف {$pruned} نسخة قديمة (أكثر من {$days} يوم).");

        return self::SUCCESS;
    }
}
