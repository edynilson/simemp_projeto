<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-06 16:08:06
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 11:07:03
*/

include('../conf/check_pastas.php');

$id_mov = $_GET['id_mov'];
$query_conta = $connection->prepare("SELECT e.nome, c.id FROM movimento m INNER JOIN conta c ON m.id_conta=c.id INNER JOIN empresa e ON c.id_empresa=e.id_empresa WHERE m.id=:id_mov LIMIT 1");
$query_conta->execute(array(':id_mov' => $id_mov));
$linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
$id_conta = $linha_conta['id'];

$query_movimentos = $connection->prepare("SELECT m.id, date_format(m.data_op,'%d-%m-%Y') as data_op, m.tipo, m.descricao, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE c.id=:id_conta ORDER BY m.id ASC, m.date_reg ASC");
$query_movimentos->execute(array(':id_conta' => $id_conta));
$num_rows = $query_movimentos->rowCount() + 1;

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
$query_user = $connection->prepare("SELECT u.nome FROM utilizador u WHERE u.id=:id_utilizador");
$query_user->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_user = $query_user->fetch(PDO::FETCH_ASSOC);
$data = gmdate('d/m/o H:i:s');
$condicoes = array("/", ":", " ");
$data_agora = str_replace($condicoes, "", $data);
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator($linha_user['nome'])
        ->setLastModifiedBy($linha_user['nome'])
        ->setTitle("Extrato" . $data_agora)
        ->setSubject("Extrato bancário SimEmp")
        ->setDescription("Extrato bancário produzido pela aplicação SimEmp")
        ->setKeywords("office 2007 excel simemp")
        ->setCategory("SimEmp - " . $data_agora);
$rowCount = 1;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . $rowCount, 'Data')
        ->setCellValue('B' . $rowCount, 'Tipo')
        ->setCellValue('C' . $rowCount, 'Descrição')
        ->setCellValue('D' . $rowCount, 'Débito')
        ->setCellValue('E' . $rowCount, 'Crédito')
        ->setCellValue('F' . $rowCount, 'Saldo');
while ($linha_movimentos = $query_movimentos->fetch(PDO::FETCH_ASSOC)) {
    $rowCount++;
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $rowCount, $linha_movimentos['data_op'])
            ->setCellValue('B' . $rowCount, $linha_movimentos['tipo'])
            ->setCellValue('C' . $rowCount, $linha_movimentos['descricao'])
            ->setCellValue('D' . $rowCount, $linha_movimentos['debito'])
            ->setCellValue('E' . $rowCount, $linha_movimentos['credito'])
            ->setCellValue('F' . $rowCount, $linha_movimentos['saldo_controlo']);
}
$objPHPExcel->getActiveSheet()
        ->setTitle('Extrato bancário');
$objPHPExcel->getActiveSheet()
        ->getStyle('A1:A' . $num_rows)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()
        ->getStyle('B1:B' . $num_rows)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()
        ->getStyle('C2:C' . $num_rows)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()
        ->getStyle('C1:F1')
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()
        ->getStyle('D2:D' . $num_rows)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()
        ->getStyle('A1:F' . $num_rows)
        ->getAlignment()
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
foreach (range('A', 'F') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
}
$objPHPExcel->getActiveSheet()
        ->getStyle('D2:F' . $num_rows)
        ->getNumberFormat()
        ->setFormatCode('#,##0.00');
$objPHPExcel->setActiveSheetIndex(0);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SimEmpExtrato_' . $linha_conta['nome'] . '_data_' . $data_agora . '.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;