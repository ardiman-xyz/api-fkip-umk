<?php

namespace App\Libraries;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator {
    public static function Generate($html, $filename='', $paper = '', $orientation = '', $stream=TRUE)
    {
        $dompdf = new Dompdf();

        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();
        if ($stream) {
            $dompdf->stream($filename.".pdf", array("Attachment" => 1));
        } else {
            return $dompdf->output();
        }
    }
}