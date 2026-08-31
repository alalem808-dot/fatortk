<?php

namespace App\Services;

use App\Models\Invoice;
use Mpdf\Mpdf;

class PDFService
{
    private function getLogo(Invoice $invoice): string
    {
        if (!$invoice->tenant->logo) return '';

        // المسار الحقيقي للملف على السيرفر
        $abs = storage_path('app/public/' . ltrim($invoice->tenant->logo, '/'));

        // fallback للمسار القديم
        if (!file_exists($abs)) {
            $abs = public_path('storage/' . ltrim($invoice->tenant->logo, '/'));
        }

        if (!file_exists($abs)) return '';

        try {
            $data = file_get_contents($abs);
            if (!$data) return '';

            $info = getimagesize($abs);
            $mime = $info ? $info['mime'] : 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode($data);

        } catch (\Throwable $e) {
            return '';
        }
    }

    public function generateInvoice(Invoice $invoice): string
    {
        ini_set('memory_limit', '512M');

        $logo = $this->getLogo($invoice);

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'logo'    => $logo,
        ])->render();

        $fontDir = base_path('vendor/mpdf/mpdf/ttfonts');

        $mpdf = new Mpdf([
            'mode'                   => 'utf-8',
            'format'                 => 'A4',
            'tempDir'                => storage_path('app/mpdf_tmp'),
            'allow_output_buffering' => true,
            'fontDir'                => [$fontDir],
            'fontdata'               => [
                'xbriyaz' => [
                    'R'  => 'XB Riyaz.ttf',
                    'B'  => 'XB RiyazBd.ttf',
                    'I'  => 'XB RiyazIt.ttf',
                    'BI' => 'XB RiyazBdIt.ttf',
                    'useOTL'     => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'xbriyaz',
        ]);

        // رفع pcre.backtrack_limit لتجنب خطأ HTML كبير
        $old = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '5000000');

        // تقسيم CSS عن Body لتجنب مشكلة الحجم
        preg_match('/<style[^>]*>(.*?)<\/style>/si', $html, $cssMatch);
        $css  = $cssMatch[1] ?? '';
        $body = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);

        if ($css) {
            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        }
        $mpdf->WriteHTML($body, \Mpdf\HTMLParserMode::HTML_BODY);

        ini_set('pcre.backtrack_limit', $old);

        return $mpdf->Output('', 'S');
    }

    public function download(Invoice $invoice)
    {
        $content  = $this->generateInvoice($invoice);
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function stream(Invoice $invoice)
    {
        $content  = $this->generateInvoice($invoice);
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
