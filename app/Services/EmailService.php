<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SentLog;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendInvoice(Invoice $invoice, string $email = null): bool
    {
        $recipient = $email ?? $invoice->customer->email;

        try {
            Mail::send('emails.invoice', ['invoice' => $invoice], function ($mail) use ($invoice, $recipient) {
                $mail->to($recipient)
                     ->subject('فاتورة رقم ' . $invoice->invoice_number . ' من ' . $invoice->tenant->company_name);
            });

            SentLog::create([
                'tenant_id'  => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'channel'    => 'email',
                'recipient'  => $recipient,
                'status'     => 'success',
                'sent_at'    => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            SentLog::create([
                'tenant_id'     => $invoice->tenant_id,
                'invoice_id'    => $invoice->id,
                'channel'       => 'email',
                'recipient'     => $recipient,
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at'       => now(),
            ]);

            return false;
        }
    }
}
