<?php $__env->startSection('title', 'لوحة التحكم'); ?>
<?php $__env->startSection('page-title'); ?>
<span>لوحة التحكم</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div class="row g-3 mb-4">

    
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card blue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">إجمالي الفواتير</div>
                    <div class="stat-value"><?php echo e($stats['total_invoices']); ?></div>
                    <div class="stat-sub">
                        <?php if($stats['total_invoices'] > 0): ?>
                            <?php echo e(round($stats['paid_invoices'] / $stats['total_invoices'] * 100)); ?>% مسددة
                        <?php else: ?>
                            لا توجد فواتير
                        <?php endif; ?>
                    </div>
                </div>
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb"><i class="fas fa-file-invoice"></i></div>
            </div>
            <?php if($stats['total_invoices'] > 0): ?>
            <div class="progress mt-2" style="height:3px">
                <div class="progress-bar bg-primary" style="width:<?php echo e(round($stats['paid_invoices'] / $stats['total_invoices'] * 100)); ?>%"></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card <?php echo e($stats['overdue_invoices'] > 0 ? 'red' : 'green'); ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">متأخرة</div>
                    <div class="stat-value"><?php echo e($stats['overdue_invoices']); ?></div>
                    <div class="stat-sub"><?php echo e($stats['overdue_invoices'] > 0 ? 'تحتاج متابعة' : 'لا متأخرات'); ?></div>
                </div>
                <div class="stat-icon" style="background:<?php echo e($stats['overdue_invoices'] > 0 ? '#fee2e2;color:#dc2626' : '#dcfce7;color:#16a34a'); ?>">
                    <i class="fas fa-<?php echo e($stats['overdue_invoices'] > 0 ? 'clock' : 'circle-check'); ?>"></i>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card yellow">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><?php echo e($isFinancial ? 'إيرادات الشهر' : 'إيراداتي الشهر'); ?></div>
                    <div class="stat-value" style="font-size:1.1rem"><?php echo e(number_format($stats['total_revenue'], 0)); ?></div>
                    <div class="stat-sub">محصّل</div>
                </div>
                <div class="stat-icon" style="background:#fef3c7;color:#d97706"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>

    
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card <?php echo e($stats['unpaid_amount'] > 0 ? 'red' : 'green'); ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><?php echo e($isFinancial ? 'إجمالي المستحقات' : 'مستحقاتي'); ?></div>
                    <div class="stat-value" style="font-size:1.05rem"><?php echo e(number_format($stats['unpaid_amount'], 0)); ?></div>
                    <div class="stat-sub">غير محصّل</div>
                </div>
                <div class="stat-icon" style="background:<?php echo e($stats['unpaid_amount'] > 0 ? '#fee2e2;color:#dc2626' : '#dcfce7;color:#16a34a'); ?>">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    
    <?php if($canViewProfit): ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card purple">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">مصروفات الشهر</div>
                    <div class="stat-value" style="font-size:1.1rem"><?php echo e(number_format($stats['total_expenses'], 0)); ?></div>
                    <div class="stat-sub">تشغيلية</div>
                </div>
                <div class="stat-icon" style="background:#ede9fe;color:#7c3aed"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card <?php echo e(($stats['net_profit'] ?? 0) >= 0 ? 'teal' : 'red'); ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">صافي الربح</div>
                    <div class="stat-value <?php echo e(($stats['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger'); ?>" style="font-size:1.05rem">
                        <?php echo e(($stats['net_profit'] ?? 0) >= 0 ? '' : '-'); ?><?php echo e(number_format(abs($stats['net_profit'] ?? 0), 0)); ?>

                    </div>
                    <div class="stat-sub">هذا الشهر</div>
                </div>
                <div class="stat-icon" style="background:<?php echo e(($stats['net_profit'] ?? 0) >= 0 ? '#cffafe' : '#fee2e2'); ?>;color:<?php echo e(($stats['net_profit'] ?? 0) >= 0 ? '#0891b2' : '#dc2626'); ?>">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>


<div class="row g-3 mb-3">

    
    <?php if($canViewSales): ?>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">الإيرادات الشهرية</h6>
                    <div class="text-muted" style="font-size:.75rem">آخر 6 أشهر</div>
                </div>
                <a href="<?php echo e(route('reports.sales')); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i>تقرير كامل
                </a>
            </div>
            <div class="card-body pt-2">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.view')): ?>
    <div class="<?php echo e($canViewSales ? 'col-lg-4' : 'col-lg-6'); ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">تنبيهات المخزون</h6>
                    <div class="text-muted" style="font-size:.75rem"><?php echo e($stats['low_stock_count']); ?> منتج منخفض</div>
                </div>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reports.view_stock')): ?>
                <a href="<?php echo e(route('reports.stock')); ?>" class="btn btn-xs btn-outline-warning">عرض الكل</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php $__empty_1 = true; $__currentLoopData = $lowStockProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('products.show', $product)); ?>" class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom text-decoration-none"
                   onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:6px;height:6px;border-radius:50%;background:#dc2626;flex-shrink:0;"></div>
                        <span class="small fw-600 text-dark"><?php echo e($product->name); ?></span>
                    </div>
                    <span class="badge badge-status" style="background:#fee2e2;color:#dc2626;">
                        <?php echo e($product->stock_quantity); ?> <?php echo e($product->unit); ?>

                    </span>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state py-4">
                    <div class="empty-icon mx-auto mb-2" style="width:48px;height:48px;font-size:1.2rem;background:#dcfce7;color:#16a34a;">
                        <i class="fas fa-check"></i>
                    </div>
                    <p class="small mb-0 text-success fw-600">المخزون بحالة ممتازة</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($canViewProfit && $topCustomers->count()): ?>
    <div class="<?php echo e($canViewSales ? 'col-lg-4' : 'col-lg-6'); ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">أعلى العملاء</h6>
                    <div class="text-muted" style="font-size:.75rem">هذا الشهر</div>
                </div>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reports.view_sales')): ?>
                <a href="<?php echo e(route('reports.customers')); ?>" class="btn btn-xs btn-outline-primary">عرض الكل</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php $__currentLoopData = $topCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $amount = $customer->invoices_sum_paid_amount ?? 0; ?>
                <?php if($amount > 0): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:28px;height:28px;border-radius:7px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0;">
                            <?php echo e(Str::upper(Str::substr($customer->name, 0, 1))); ?>

                        </div>
                        <span class="small fw-600 text-dark"><?php echo e(Str::limit($customer->name, 18)); ?></span>
                    </div>
                    <span class="small fw-700 text-success"><?php echo e(number_format($amount, 0)); ?></span>
                </div>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>


<?php if($canViewProfit): ?>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-600 text-muted">إيرادات الشهر</span>
                    <i class="fas fa-arrow-trend-up text-success"></i>
                </div>
                <div class="fw-800" style="font-size:1.4rem;color:#16a34a"><?php echo e(number_format($stats['total_revenue'], 0)); ?></div>
                <div class="progress mt-2" style="height:4px">
                    <?php $max = max($stats['total_revenue'], $stats['purchases_month'] ?? 0, 1); ?>
                    <div class="progress-bar bg-success" style="width:<?php echo e(round($stats['total_revenue'] / $max * 100)); ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-600 text-muted">مشتريات الشهر</span>
                    <i class="fas fa-arrow-trend-down text-danger"></i>
                </div>
                <div class="fw-800" style="font-size:1.4rem;color:#dc2626"><?php echo e(number_format($stats['purchases_month'] ?? 0, 0)); ?></div>
                <div class="progress mt-2" style="height:4px">
                    <div class="progress-bar bg-danger" style="width:<?php echo e(round(($stats['purchases_month'] ?? 0) / $max * 100)); ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-600 text-muted">مصروفات الشهر</span>
                    <i class="fas fa-receipt text-purple" style="color:#7c3aed"></i>
                </div>
                <div class="fw-800" style="font-size:1.4rem;color:#7c3aed"><?php echo e(number_format($stats['total_expenses'], 0)); ?></div>
                <div class="progress mt-2" style="height:4px">
                    <div class="progress-bar" style="background:#7c3aed;width:<?php echo e(round($stats['total_expenses'] / $max * 100)); ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">آخر الفواتير</h6>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('invoices.create')): ?>
        <a href="<?php echo e(route('invoices.create')); ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> فاتورة جديدة
        </a>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <?php if($canViewSales || !$isFinancial): ?>
                    <th>المبلغ</th>
                    <?php endif; ?>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                $statusLabels = ['draft'=>'مسودة','sent'=>'مرسلة','paid'=>'مدفوعة','partially_paid'=>'جزئي','overdue'=>'متأخرة','cancelled'=>'ملغاة','returned'=>'مرتجعة'];
                ?>
                <tr>
                    <td>
                        <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="fw-700 text-decoration-none" style="color:var(--primary)">
                            <?php echo e($invoice->invoice_number); ?>

                        </a>
                    </td>
                    <td><span class="fw-600"><?php echo e($invoice->customer->name); ?></span></td>
                    <td class="text-muted small"><?php echo e($invoice->invoice_date->format('Y-m-d')); ?></td>
                    <?php if($canViewSales || !$isFinancial): ?>
                    <td class="fw-700">
                        <?php echo e(number_format($invoice->total_amount, 2)); ?>

                        <span class="text-muted fw-400 small"><?php echo e($invoice->currency); ?></span>
                    </td>
                    <?php endif; ?>
                    <td><span class="badge badge-status badge-<?php echo e($invoice->status); ?>"><?php echo e($statusLabels[$invoice->status] ?? $invoice->status); ?></span></td>
                    <td>
                        <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="btn btn-xs btn-outline-secondary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-file-invoice"></i></div>
                            <h5>لا توجد فواتير بعد</h5>
                            <p>ابدأ بإنشاء أول فاتورة لك</p>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('invoices.create')): ?>
                            <a href="<?php echo e(route('invoices.create')); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> فاتورة جديدة
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php if($canViewSales): ?>
<?php $__env->startPush('scripts'); ?>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 200);
gradient.addColorStop(0, 'rgba(37,99,235,.25)');
gradient.addColorStop(1, 'rgba(37,99,235,.02)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartData->pluck('month')); ?>,
        datasets: [{
            label: 'الإيرادات',
            data: <?php echo json_encode($chartData->pluck('revenue')); ?>,
            borderColor: '#2563eb',
            backgroundColor: gradient,
            borderWidth: 2.5,
            pointBackgroundColor: '#2563eb',
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: .4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                titleFont: { family: 'Cairo', size: 12 },
                bodyFont:  { family: 'Cairo', size: 13 },
                padding: 10, cornerRadius: 8,
                callbacks: {
                    label: ctx => ' ' + Number(ctx.raw).toLocaleString('ar') + ' '
                }
            }
        },
        scales: {
            y: {
                grid: { color: '#f1f5f9', drawBorder: false },
                ticks: { font: { family: 'Cairo', size: 11 }, color: '#94a3b8' }
            },
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Cairo', size: 11 }, color: '#94a3b8' }
            }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp8.2\htdocs\fatortk\resources\views/dashboard/index.blade.php ENDPATH**/ ?>