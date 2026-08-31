<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Collection;

/**
 * OPS-08 Fix: Trait مشترك لتوليد PDF لكشوف الحسابات
 * يُستخدم في CustomerController و SupplierController
 */
trait GeneratesLedgerPdf
{
    private function generateLedgerPdfResponse(
        Collection $ledger,
        float $totalDebit,
        float $totalCredit,
        float $balance,
        string $entityName
    ) {
        $companyName = auth()->user()->tenant->company_name ?? 'فاتورتك';

        $html = view('ledger-pdf', compact(
            'ledger', 'totalDebit', 'totalCredit', 'balance', 'entityName', 'companyName'
        ))->render();

        $fontDir = base_path('vendor/mpdf/mpdf/ttfonts');
        $mpdf = new \Mpdf\Mpdf([
            'mode'                   => 'utf-8',
            'format'                 => 'A4',
            'tempDir'                => storage_path('app/mpdf_tmp'),
            'allow_output_buffering' => true,
            'fontDir'                => [$fontDir],
            'fontdata'               => [
                'xbriyaz' => [
                    'R'          => 'XB Riyaz.ttf',
                    'B'          => 'XB RiyazBd.ttf',
                    'useOTL'     => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'xbriyaz',
        ]);

        preg_match('/<style[^>]*>(.*?)<\/style>/si', $html, $cssMatch);
        $css  = $cssMatch[1] ?? '';
        $body = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);

        if ($css) {
            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        }
        $mpdf->WriteHTML($body, \Mpdf\HTMLParserMode::HTML_BODY);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ledger.pdf"',
        ]);
    }
}
