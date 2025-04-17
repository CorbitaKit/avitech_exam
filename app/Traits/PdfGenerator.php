<?php

namespace App\Traits;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Barryvdh\Snappy\PdfWrapper;

trait PdfGenerator
{
    public function generatePDF(string $content): PdfWrapper
    {


        return SnappyPdf::loadHTML($content)
        ->setPaper('a4')
        ->setOption('dpi', 96)
        ->setOption('disable-smart-shrinking', true);
    }
}
