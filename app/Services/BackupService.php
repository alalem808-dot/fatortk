<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupService
{
    private string $basePath = 'backups';

    /**
     * مسار mysqldump — يُقرأ من .env أو يُستخدم الافتراضي
     */
    private function getMysqldumpPath(): string
    {
        return env('MYSQLDUMP_PATH', 'C:\\xampp8.2\\mysql\\bin\\mysqldump.exe');
    }

    /**
     * نسخ احتياطي لمشترك محدد (بياناته فقط)
     */
    public function backupTenant(Tenant $tenant): string
    {
        $slug      = $tenant->subdomain;
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = "tenant_{$slug}_{$timestamp}.sql";
        $zipName   = "tenant_{$slug}_{$timestamp}.zip";
        $tmpSql    = storage_path("app/{$this->basePath}/tmp_{$filename}");
        $zipPath   = "{$this->basePath}/{$zipName}";
        $zipFull   = storage_path("app/{$zipPath}");

        $this->ensureDir(storage_path("app/{$this->basePath}"));

        $sql = $this->generateTenantSql($tenant);
        file_put_contents($tmpSql, $sql);

        $this->zipFile($tmpSql, $filename, $zipFull);
        @unlink($tmpSql);

        DB::table('backup_logs')->insert([
            'tenant_id'  => $tenant->id,
            'type'       => 'tenant',
            'filename'   => $zipName,
            'path'       => $zipPath,
            'size'       => file_exists($zipFull) ? filesize($zipFull) : 0,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $zipPath;
    }

    /**
     * نسخ احتياطي كامل للنظام (mysqldump)
     * كلمة المرور تُمرَّر عبر MYSQL_PWD لتجنب ظهورها في ps/logs
     */
    public function backupFull(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = "full_backup_{$timestamp}.sql";
        $zipName   = "full_backup_{$timestamp}.zip";
        $tmpSql    = storage_path("app/{$this->basePath}/tmp_{$filename}");
        $zipPath   = "{$this->basePath}/{$zipName}";
        $zipFull   = storage_path("app/{$zipPath}");

        $this->ensureDir(storage_path("app/{$this->basePath}"));

        $cfg       = config('database.connections.mysql');
        $mysqldump = $this->getMysqldumpPath();

        // تعيين MYSQL_PWD مؤقتاً بدلاً من وضع كلمة المرور في command line
        if (!empty($cfg['password'])) {
            putenv("MYSQL_PWD={$cfg['password']}");
        }

        $cmd = sprintf(
            '"%s" -h%s -P%s -u%s --single-transaction --routines --triggers %s > "%s" 2>&1',
            $mysqldump,
            $cfg['host'],
            $cfg['port'],
            $cfg['username'],
            $cfg['database'],
            $tmpSql
        );

        exec($cmd, $output, $code);

        // مسح كلمة المرور فوراً
        putenv('MYSQL_PWD');

        if ($code !== 0 || !file_exists($tmpSql)) {
            throw new \RuntimeException('فشل mysqldump: ' . implode("\n", $output));
        }

        $this->zipFile($tmpSql, $filename, $zipFull);
        @unlink($tmpSql);

        DB::table('backup_logs')->insert([
            'tenant_id'  => null,
            'type'       => 'full',
            'filename'   => $zipName,
            'path'       => $zipPath,
            'size'       => file_exists($zipFull) ? filesize($zipFull) : 0,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $zipPath;
    }

    /**
     * توليد SQL لبيانات مشترك محدد فقط
     */
    private function generateTenantSql(Tenant $tenant): string
    {
        $id  = $tenant->id;
        $sql = "-- Backup for tenant: {$tenant->company_name} (ID: {$id})\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tenantTables = [
            'users'                => 'tenant_id',
            'customers'            => 'tenant_id',
            'suppliers'            => 'tenant_id',
            'products'             => 'tenant_id',
            'invoices'             => 'tenant_id',
            'purchases'            => 'tenant_id',
            'expenses'             => 'tenant_id',
            'settings'             => 'tenant_id',
            'invoice_templates'    => 'tenant_id',
            'payment_methods'      => 'tenant_id',
            'categories'           => 'tenant_id',
            'units'                => 'tenant_id',
            'currencies'           => 'tenant_id',
            'warehouses'           => 'tenant_id',
            'stock_movements'      => 'tenant_id',
            'stocktaking_sessions' => 'tenant_id',
        ];

        foreach ($tenantTables as $table => $col) {
            if (!$this->tableExists($table)) continue;
            $rows = DB::table($table)->where($col, $id)->get();
            if ($rows->isEmpty()) continue;
            $sql .= $this->rowsToInsert($table, $rows);
        }

        $invoiceIds   = DB::table('invoices')->where('tenant_id', $id)->pluck('id');
        $purchaseIds  = DB::table('purchases')->where('tenant_id', $id)->pluck('id');
        $supplierIds  = DB::table('suppliers')->where('tenant_id', $id)->pluck('id');
        $warehouseIds = DB::table('warehouses')->where('tenant_id', $id)->pluck('id');
        $sessionIds   = DB::table('stocktaking_sessions')->where('tenant_id', $id)->pluck('id');

        $related = [
            ['invoice_items',     'invoice_id',   $invoiceIds],
            ['payments',          'invoice_id',   $invoiceIds],
            ['invoice_returns',   'invoice_id',   $invoiceIds],
            ['purchase_items',    'purchase_id',  $purchaseIds],
            ['purchase_returns',  'purchase_id',  $purchaseIds],
            ['supplier_payments', 'supplier_id',  $supplierIds],
            ['warehouse_stocks',  'warehouse_id', $warehouseIds],
            ['stocktaking_items', 'session_id',   $sessionIds],
        ];

        foreach ($related as [$table, $col, $ids]) {
            if (!$this->tableExists($table) || $ids->isEmpty()) continue;
            $rows = DB::table($table)->whereIn($col, $ids)->get();
            if ($rows->isEmpty()) continue;
            $sql .= $this->rowsToInsert($table, $rows);
        }

        $returnIds = DB::table('invoice_returns')->whereIn('invoice_id', $invoiceIds)->pluck('id');
        if ($returnIds->isNotEmpty() && $this->tableExists('invoice_return_items')) {
            $rows = DB::table('invoice_return_items')->whereIn('return_id', $returnIds)->get();
            if ($rows->isNotEmpty()) $sql .= $this->rowsToInsert('invoice_return_items', $rows);
        }

        $purchaseReturnIds = DB::table('purchase_returns')->whereIn('purchase_id', $purchaseIds)->pluck('id');
        if ($purchaseReturnIds->isNotEmpty() && $this->tableExists('purchase_return_items')) {
            $rows = DB::table('purchase_return_items')->whereIn('purchase_return_id', $purchaseReturnIds)->get();
            if ($rows->isNotEmpty()) $sql .= $this->rowsToInsert('purchase_return_items', $rows);
        }

        $tenantRow = DB::table('tenants')->where('id', $id)->get();
        $sql .= $this->rowsToInsert('tenants', $tenantRow);

        $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }

    private function rowsToInsert(string $table, $rows): string
    {
        if ($rows->isEmpty()) return '';
        $sql = "-- Table: {$table}\n";
        foreach ($rows as $row) {
            $row  = (array) $row;
            $cols = implode('`, `', array_keys($row));
            $vals = implode(', ', array_map(
                fn($v) => $v === null ? 'NULL' : "'" . addslashes((string) $v) . "'",
                array_values($row)
            ));
            $sql .= "INSERT IGNORE INTO `{$table}` (`{$cols}`) VALUES ({$vals});\n";
        }
        return $sql . "\n";
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    private function zipFile(string $srcFile, string $nameInZip, string $destZip): void
    {
        $zip = new ZipArchive();
        if ($zip->open($destZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("لا يمكن إنشاء ملف ZIP: {$destZip}");
        }
        $zip->addFile($srcFile, $nameInZip);
        $zip->close();
    }

    private function ensureDir(string $path): void
    {
        if (!is_dir($path)) mkdir($path, 0755, true);
    }

    /**
     * حذف النسخ القديمة (أكثر من X يوم)
     */
    public function pruneOld(int $days = 30): int
    {
        $old = DB::table('backup_logs')
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        $count = 0;
        foreach ($old as $log) {
            $full = storage_path("app/{$log->path}");
            if (file_exists($full)) @unlink($full);
            DB::table('backup_logs')->where('id', $log->id)->delete();
            $count++;
        }
        return $count;
    }
}
