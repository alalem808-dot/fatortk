<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SentLog;

class WhatsAppService
{
    public function getLink(Invoice $invoice): string
    {
        $customer = $invoice->customer;
        $phone = preg_replace('/[^0-9]/', '', $customer->whatsapp_number ?? $customer->phone);

        $downloadUrl = route('invoices.public', $invoice->download_token ?? $invoice->id);

        $message = "مرحباً {$customer->name}،\n";
        $message .= "فاتورتك رقم: {$invoice->invoice_number}\n";
        $message .= "المبلغ الإجمالي: {$invoice->total_amount} {$invoice->currency}\n";
        $message .= "تاريخ الاستحقاق: " . ($invoice->due_date?->format('Y-m-d') ?? '-') . "\n";
        $message .= "لتحميل الفاتورة: {$downloadUrl}";

        SentLog::create([
            'tenant_id'  => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'channel'    => 'whatsapp',
            'recipient'  => $phone,
            'status'     => 'success',
            'sent_at'    => now(),
        ]);

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}
