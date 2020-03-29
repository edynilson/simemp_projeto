<?php

ob_start();
include(dirname(__FILE__) . '/nota_credito_dados.php');
$content = ob_get_clean();
require_once(dirname(__FILE__) . '/html2pdf_v4.03/html2pdf.class.php');
try {
    $html2pdf = new HTML2PDF('P', 'A4', 'pt', true, 'UTF-8', array(15, 10, 15, 5));
    $html2pdf->pdf->setTitle('SIMEMP - Nota de crédito');
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
    $html2pdf->Output('Nota_credito_Simemp.pdf', 'I');
} catch (HTML2PDF_exception $e) {
    echo $e;
    exit;
}

?>
