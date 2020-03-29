<?php

/*
  Created on : 27/Nov/2014, 14:51:45
  Author     : Ricardo Soares
 */

ob_start();
include(dirname(__FILE__) . '/dep_prazo_dados.php');
$content = ob_get_clean();
require_once(dirname(__FILE__) . '/html2pdf_v4.03/html2pdf.class.php');
try {
    $html2pdf = new HTML2PDF('P', 'A4', 'pt', true, 'UTF-8', array(15, 10, 15, 5));
    $html2pdf->pdf->setTitle('SIMEMP - Depósito a prazo');
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
    $html2pdf->Output('Dep_Prazo_Simemp.pdf', 'I');
} catch (HTML2PDF_exception $e) {
    echo $e;
    exit;
}
