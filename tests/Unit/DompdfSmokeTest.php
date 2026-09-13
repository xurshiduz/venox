<?php

namespace Tests\Unit;

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPUnit\Framework\TestCase;

class DompdfSmokeTest extends TestCase
{
    public function test_it_generates_a_downloadable_pdf_document(): void
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $pdf = new Dompdf($options);
        $pdf->loadHtml('<h1>Sotuvlar hisoboti</h1><p>Agent va sana filtri</p>', 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $output = $pdf->output();

        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertGreaterThan(1000, strlen($output));
    }
}
