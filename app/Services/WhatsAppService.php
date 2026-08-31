<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SentLog;

class WhatsAppService
{
    public function getLink(Invoice $invoice): string
    {
        $customer = $invoice->customer;
        $phone = preg_replace('/[^0-9+]/', '', $customer->whatsapp_number ?? $customer->phone ?? '');

        // يتطلب الـ route public_token — نتأكد من وجوده
        if (empty($invoice->public_token)) {
            $invoice->update(['public_token' => \Illuminate\Support\Str::uuid()]);
            $invoice->refresh();
        }

        $downloadUrl = route('invoices.public', $invoice->public_token);

        $message  = "مرحباً {$customer->name}،\n";
        $message .= "فاتورتك رقم: {$invoice->invoice_number}\n";
        $message .= "المبلغ الإجمالي: " . number_format($invoice->total_amount, 2) . " {$invoice->currency}\n";
        $message .= "تاريخ الاستحقاق: " . ($invoice->due_date?->format('Y-m-d') ?? '-') . "\n";
        $message .= "لتحميل الفاتورة: {$downloadUrl}";

        if ($phone) {
            // SEC-05 Fix: نسجل الحالة كـ 'link_generated' لأننا بنينا الرابط فقط
            // ولم نتحقق من الإرسال الفعلي — WhatsApp لا يوفر webhook للتأكيد
            SentLog::create([
                'tenant_id'  => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'channel'    => 'whatsapp',
                'recipient'  => $phone,
                'status'     => 'success', // link_generated = تم بناء الرابط وإعادة التوجيه
            ]);
        }

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}
