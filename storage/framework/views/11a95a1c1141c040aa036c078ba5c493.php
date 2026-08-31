<?php $__env->startSection('title', 'بيع مباشر'); ?>
<?php $__env->startSection('page-title'); ?><span>⚡ بيع مباشر</span><?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.qs-product-card {
    cursor: pointer;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: .75rem;
    transition: all .15s;
    background: #fff;
    user-select: none;
}
.qs-product-card:hover { border-color: var(--primary); background: #eff6ff; }
.qs-product-card .p-name { font-weight: 700; font-size: .85rem; color: #1e293b; }
.qs-product-card .p-price { font-size: .9rem; font-weight: 800; color: var(--primary); }
.qs-product-card .p-stock { font-size: .7rem; color: #64748b; }

.cart-row { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; padding: .6rem .75rem; margin-bottom: .4rem; }
.cart-row.low-stock { border-color: #fbbf24; background: #fffbeb; }

.pm-btn { border: 2px solid #e2e8f0; border-radius: 10px; padding: .5rem .9rem; cursor: pointer; font-weight: 600; font-size: .85rem; transition: all .15s; background: #fff; }
.pm-btn:hover { border-color: var(--primary); color: var(--primary); }
.pm-btn.selected { border-color: var(--primary); background: var(--primary); color: #fff; }

.total-box { background: linear-gradient(135deg, #1e40af, #2563eb); border-radius: 14px; color: #fff; padding: 1.25rem; }
.total-box .lbl { font-size: .78rem; opacity: .8; }
.total-box .val { font-size: 1.9rem; font-weight: 900; line-height: 1; }

#searchInput { border-radius: 12px; font-size: 1rem; padding: .65rem 1rem; }
.badge-stock-ok  { background: #dcfce7; color: #15803d; }
.badge-stock-low { background: #fef3c7; color: #b45309; }
.badge-stock-out { background: #fee2e2; color: #dc2626; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
$productsJson = $products->map(fn($p) => [
    'id'    => $p->id,
    'name'  => $p->name,
    'price' => (float)$p->unit_price,
    'tax'   => (float)$p->tax_rate,
    'unit'  => $p->unit,
    'stock' => (float)$p->stock_quantity,
    'barcode' => $p->barcode,
])->values()->toJson();

$warehouseStocksJson = collect($warehouseStocks ?? [])
    ->map(fn($stocks) => collect($stocks)->map(fn($q) => (float)$q))
    ->toJson();
?>

<form action="<?php echo e(route('quick-sale.store')); ?>" method="POST" id="qsForm">
<?php echo csrf_field(); ?>
<div class="row g-3">

    
    <div class="col-lg-8">

        
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                           placeholder="ابحث بالاسم أو الباركود أو الكود..." autocomplete="off">
                    <?php if($userWarehouses->count() > 1): ?>
                    <select id="warehouseSelect" class="form-select" style="max-width:180px" onchange="onWarehouseChange(this.value)">
                        <?php $__currentLoopData = $userWarehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wh->id); ?>" <?php echo e($userWarehouse?->id == $wh->id ? 'selected' : ''); ?>>
                            <?php echo e($wh->name); ?><?php echo e($wh->is_default ? ' ★' : ''); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php else: ?>
                    <input type="hidden" name="warehouse_id" value="<?php echo e($userWarehouse?->id); ?>">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-boxes-stacked me-2 text-primary"></i>المنتجات</h6>
                <span class="text-muted small" id="productsCount"><?php echo e($products->count()); ?> منتج</span>
            </div>
            <div class="card-body" style="max-height:340px;overflow-y:auto">
                <div class="row g-2" id="productsGrid">
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $stock = isset($warehouseStocks[$userWarehouse?->id][$p->id])
                            ? (float)$warehouseStocks[$userWarehouse?->id][$p->id]
                            : 0;
                        $stockClass = $stock <= 0 ? 'badge-stock-out' : ($stock <= $p->min_stock_alert ? 'badge-stock-low' : 'badge-stock-ok');
                    ?>
                    <div class="col-6 col-md-4 col-xl-3 product-card-wrap"
                         data-name="<?php echo e(strtolower($p->name)); ?>"
                         data-barcode="<?php echo e($p->barcode); ?>"
                         data-sku="<?php echo e($p->sku); ?>">
                        <div class="qs-product-card h-100"
                             onclick="addToCart(<?php echo e($p->id); ?>, '<?php echo e(addslashes($p->name)); ?>', <?php echo e($p->unit_price); ?>, <?php echo e($p->tax_rate); ?>, '<?php echo e($p->unit); ?>')">
                            <div class="p-name mb-1"><?php echo e($p->name); ?></div>
                            <div class="p-price"><?php echo e(number_format($p->unit_price, 2)); ?> <?php echo e($defaultCurrency); ?></div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="p-stock"><?php echo e($p->unit); ?></span>
                                <span class="badge <?php echo e($stockClass); ?>" style="font-size:.65rem">
                                    <?php echo e(number_format($stock, 2)); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($products->isEmpty()): ?>
                <div class="empty-state py-3">
                    <div class="empty-icon"><i class="fas fa-boxes-stacked"></i></div>
                    <p>لا توجد منتجات نشطة</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-cart-shopping me-2 text-primary"></i>السلة</h6>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="clearCart()">
                    <i class="fas fa-trash me-1"></i> تفريغ
                </button>
            </div>
            <div class="card-body p-2" id="cartContainer">
                <div class="empty-state py-3" id="cartEmpty">
                    <div class="empty-icon"><i class="fas fa-cart-shopping"></i></div>
                    <p>السلة فارغة — اضغط على منتج لإضافته</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-lg-4">
        <div style="position:sticky;top:80px">

            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2">
                    <label class="form-label small fw-bold mb-1">العميل <span class="text-muted fw-normal">(اختياري)</span></label>
                    <select name="customer_id" class="form-select form-select-sm mb-2" id="customerSelect" onchange="onCustomerChange(this.value)">
                        <option value="">— عميل نقدي (بدون اسم) —</option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>" <?php echo e($c->id == $cashCustomerId ? 'selected' : ''); ?>>
                            <?php echo e($c->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="text" name="walk_in_name" id="walkInName"
                           class="form-control form-control-sm"
                           placeholder="أو اكتب اسم الزبون هنا..."
                           autocomplete="off">
                    <div class="form-text">إذا كتبت اسماً سيُحفظ في ملاحظات الفاتورة</div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2">
                    <label class="form-label small fw-bold mb-1">الخصم</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="discount_amount" id="discountAmount"
                               class="form-control" min="0" step="0.01" value="0"
                               oninput="calcTotals()">
                        <select name="discount_type" id="discountType" class="form-select" style="max-width:80px" onchange="calcTotals()">
                            <option value="fixed">ثابت</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2">
                    <label class="form-label small fw-bold mb-1">ملاحظات <span class="text-muted fw-normal">(اختيارية)</span></label>
                    <textarea name="notes" class="form-control form-control-sm" rows="2"
                              placeholder="تظهر في الفاتورة..."></textarea>
                </div>
            </div>

            
            <div class="total-box mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="lbl">المجموع الفرعي</span>
                    <span id="subtotalDisp">0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="lbl">الضريبة</span>
                    <span id="taxDisp">0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="lbl">الخصم</span>
                    <span id="discDisp">0.00</span>
                </div>
                <div class="border-top border-white border-opacity-25 pt-2">
                    <div class="lbl mb-1">الإجمالي</div>
                    <div class="val" id="totalDisp">0.00 <?php echo e($defaultCurrency); ?></div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2">
                    <label class="form-label small fw-bold mb-2">طريقة الدفع <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-2" id="paymentMethodBtns">
                        <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" class="pm-btn <?php echo e($loop->first ? 'selected' : ''); ?>"
                                data-code="<?php echo e($pm->code); ?>"
                                onclick="selectPaymentMethod('<?php echo e($pm->code); ?>', this)">
                            <?php echo e($pm->name); ?>

                        </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($paymentMethods->isEmpty()): ?>
                        <button type="button" class="pm-btn selected" data-code="cash" onclick="selectPaymentMethod('cash', this)">نقدي</button>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="payment_method" id="paymentMethodInput"
                           value="<?php echo e($paymentMethods->first()?->code ?? 'cash'); ?>">

                    
                    <div id="paidAmountSection" class="mt-3">
                        <label class="form-label small fw-bold mb-1">المبلغ المستلم</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="paid_amount" id="paidAmountInput"
                                   class="form-control" min="0" step="0.01"
                                   placeholder="اتركه فارغاً للدفع الكامل"
                                   oninput="calcChange()">
                            <span class="input-group-text"><?php echo e($defaultCurrency); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="form-text">الباقي للعميل:</span>
                            <span class="form-text fw-bold text-success" id="changeDisp">0.00</span>
                        </div>
                    </div>

                    <div id="creditNote" class="alert alert-warning py-2 mt-2 d-none" style="font-size:.8rem">
                        <i class="fas fa-clock me-1"></i> سيُسجَّل كدين على العميل (حالة: مرسلة)
                    </div>
                </div>
            </div>

            
            <div id="stockWarning" class="alert alert-warning py-2 mb-3 d-none" style="font-size:.8rem">
                <i class="fas fa-triangle-exclamation me-1"></i>
                بعض البنود تتجاوز الكمية المتاحة
            </div>

            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success" id="submitBtn"
                        style="font-size:1.1rem;padding:.8rem;border-radius:12px" disabled>
                    <i class="fas fa-check-circle me-2"></i> إتمام البيع
                </button>
                <a href="<?php echo e(route('invoices.index')); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-invoice me-1"></i> الفواتير العادية
                </a>
            </div>

        </div>
    </div>
</div>


<?php if($userWarehouses->count() > 1): ?>
<input type="hidden" name="warehouse_id" id="warehouseIdInput" value="<?php echo e($userWarehouse?->id); ?>">
<?php endif; ?>

</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const PRODUCTS          = <?php echo $productsJson; ?>;
const WH_STOCKS         = <?php echo $warehouseStocksJson; ?>;
const CURRENCY          = '<?php echo e($defaultCurrency); ?>';
let currentWarehouseId  = <?php echo e($userWarehouse?->id ?? 'null'); ?>;
let cart                = {}; // { productId: { ...product, qty } }
let rowIndex            = 0;

// ===== تغيير العميل =====
function onCustomerChange(val) {
    // عند اختيار عميل من القائمة يُفرغ حقل الاسم الحر
    if (val) document.getElementById('walkInName').value = '';
}

// ===== تغيير المخزن =====
function onWarehouseChange(whId) {
    currentWarehouseId = parseInt(whId) || null;
    const hiddenWh = document.getElementById('warehouseIdInput');
    if (hiddenWh) hiddenWh.value = whId;
    renderCart();
    renderProductGrid();
}

// ===== الكمية المتاحة في المخزن المختار فقط =====
function getStock(productId) {
    if (!currentWarehouseId) return 0;
    const whStocks = WH_STOCKS[currentWarehouseId];
    if (!whStocks) return 0;
    return parseFloat(whStocks[productId] ?? 0);
}

// ===== إضافة للسلة =====
function addToCart(id, name, price, tax, unit) {
    if (cart[id]) {
        cart[id].qty += 1;
    } else {
        cart[id] = { id, name, price, tax, unit, qty: 1 };
    }
    renderCart();
    calcTotals();
}

// ===== تفريغ السلة =====
function clearCart() {
    if (Object.keys(cart).length === 0) return;
    if (!confirm('تفريغ السلة؟')) return;
    cart = {};
    renderCart();
    calcTotals();
}

// ===== رسم السلة =====
function renderCart() {
    const container = document.getElementById('cartContainer');
    const empty     = document.getElementById('cartEmpty');
    const submitBtn = document.getElementById('submitBtn');
    const items     = Object.values(cart);

    // إزالة الصفوف القديمة (غير empty-state)
    container.querySelectorAll('.cart-row').forEach(r => r.remove());
    // إزالة hidden inputs قديمة
    document.querySelectorAll('.cart-hidden').forEach(r => r.remove());

    if (items.length === 0) {
        empty.classList.remove('d-none');
        submitBtn.disabled = true;
        return;
    }
    empty.classList.add('d-none');
    submitBtn.disabled = false;

    let hasStockWarn = false;
    const form = document.getElementById('qsForm');

    items.forEach((item, i) => {
        const avail    = getStock(item.id);
        const isLow    = item.qty > avail;
        if (isLow) hasStockWarn = true;

        // صف السلة
        const row = document.createElement('div');
        row.className = 'cart-row' + (isLow ? ' low-stock' : '');
        row.id = `cart-row-${item.id}`;
        row.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                    <div class="fw-700" style="font-size:.88rem">${item.name}</div>
                    <div class="text-muted" style="font-size:.72rem">
                        ${item.price.toFixed(2)} × <span id="qty-disp-${item.id}">${item.qty}</span>
                        ${isLow ? `<span class="text-warning ms-1"><i class="fas fa-triangle-exclamation"></i> متاح: ${avail.toFixed(2)}</span>` : ''}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="changeQty(${item.id}, -1)">−</button>
                    <input type="number" class="form-control form-control-sm text-center fw-700"
                           style="width:60px;font-size:.9rem"
                           value="${item.qty}" min="0.001" step="0.001"
                           onchange="setQty(${item.id}, this.value)">
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="changeQty(${item.id}, 1)">+</button>
                </div>
                <div class="fw-800 text-primary" style="min-width:70px;text-align:left;font-size:.9rem">
                    <span id="row-total-${item.id}">${(item.qty * item.price).toFixed(2)}</span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        container.appendChild(row);

        // hidden inputs
        const addHidden = (name, value) => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = name;
            inp.value = value;
            inp.className = 'cart-hidden';
            form.appendChild(inp);
        };
        addHidden(`items[${i}][product_id]`,  item.id);
        addHidden(`items[${i}][description]`, item.name);
        addHidden(`items[${i}][quantity]`,    item.qty);
        addHidden(`items[${i}][unit_price]`,  item.price);
        addHidden(`items[${i}][tax_rate]`,    item.tax);
    });

    document.getElementById('stockWarning').classList.toggle('d-none', !hasStockWarn);
    calcTotals();
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    const newQty = Math.max(0.001, parseFloat((cart[id].qty + delta).toFixed(3)));
    cart[id].qty = newQty;
    renderCart();
    calcTotals();
}

function setQty(id, val) {
    const q = parseFloat(val);
    if (!cart[id] || isNaN(q) || q <= 0) return;
    cart[id].qty = q;
    renderCart();
    calcTotals();
}

function removeFromCart(id) {
    delete cart[id];
    renderCart();
    calcTotals();
}

// ===== حساب الإجماليات =====
function calcTotals() {
    let subtotal = 0, taxTotal = 0;
    Object.values(cart).forEach(item => {
        const base = item.qty * item.price;
        subtotal  += base;
        taxTotal  += base * (item.tax / 100);
    });

    const discAmt  = parseFloat(document.getElementById('discountAmount').value) || 0;
    const discType = document.getElementById('discountType').value;
    const discVal  = discType === 'percent' ? (subtotal * discAmt / 100) : discAmt;
    const total    = Math.max(0, subtotal + taxTotal - discVal);

    document.getElementById('subtotalDisp').textContent = subtotal.toFixed(2);
    document.getElementById('taxDisp').textContent      = taxTotal.toFixed(2);
    document.getElementById('discDisp').textContent     = discVal.toFixed(2);
    document.getElementById('totalDisp').textContent    = total.toFixed(2) + ' ' + CURRENCY;

    const paidInput = document.getElementById('paidAmountInput');
    if (paidInput && !paidInput.value) {
        document.getElementById('changeDisp').textContent = '0.00';
    } else {
        calcChange();
    }
}

function calcChange() {
    const total = parseFloat(document.getElementById('totalDisp').textContent) || 0;
    const paid  = parseFloat(document.getElementById('paidAmountInput').value) || 0;
    const change = paid - total;
    const el = document.getElementById('changeDisp');
    el.textContent = change >= 0 ? change.toFixed(2) : '0.00';
    el.className = 'form-text fw-bold ' + (change >= 0 ? 'text-success' : 'text-danger');
}

// ===== طريقة الدفع =====
function selectPaymentMethod(code, btn) {
    document.querySelectorAll('.pm-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('paymentMethodInput').value = code;

    const isCredit = code === 'credit';
    document.getElementById('paidAmountSection').classList.toggle('d-none', isCredit);
    document.getElementById('creditNote').classList.toggle('d-none', !isCredit);
}

// ===== عند كتابة اسم حر يُلغى اختيار العميل من القائمة =====
document.getElementById('walkInName').addEventListener('input', function() {
    if (this.value.trim()) {
        document.getElementById('customerSelect').value = '';
    }
});

// ===== البحث =====
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    let count = 0;
    document.querySelectorAll('.product-card-wrap').forEach(card => {
        const match = !q
            || card.dataset.name.includes(q)
            || (card.dataset.barcode && card.dataset.barcode.includes(q))
            || (card.dataset.sku && card.dataset.sku.toLowerCase().includes(q));
        card.style.display = match ? '' : 'none';
        if (match) count++;
    });
    document.getElementById('productsCount').textContent = count + ' منتج';
});

// ===== رسم شبكة المنتجات (عند تغيير المخزن) =====
function renderProductGrid() {
    document.querySelectorAll('.product-card-wrap').forEach(wrap => {
        const card  = wrap.querySelector('.qs-product-card');
        const pid   = parseInt(card.getAttribute('onclick').match(/addToCart\((\d+)/)?.[1]);
        if (!pid) return;
        const avail = getStock(pid);
        const badge = card.querySelector('.badge');
        if (badge) {
            badge.textContent = avail.toFixed(2);
            badge.className = 'badge ' + (avail <= 0 ? 'badge-stock-out' : avail <= 5 ? 'badge-stock-low' : 'badge-stock-ok');
        }
    });
}

// ===== التحقق قبل الإرسال =====
document.getElementById('qsForm').addEventListener('submit', function(e) {
    if (Object.keys(cart).length === 0) {
        e.preventDefault();
        alert('السلة فارغة، أضف منتجاً على الأقل.');
        return;
    }
    const pm = document.getElementById('paymentMethodInput').value;
    if (!pm) {
        e.preventDefault();
        alert('اختر طريقة الدفع.');
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp8.2\htdocs\fatortk\resources\views/quick-sale/index.blade.php ENDPATH**/ ?>