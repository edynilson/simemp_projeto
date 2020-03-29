<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 10:53:21
*/

include('../conf/check_pastas.php');

$query_dados_logo = $connection->prepare("SELECT b.path_imagem_print, b.print_height, b.print_width FROM banco b INNER JOIN conta c ON b.id=c.id_banco INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id");
$query_dados_logo->execute(array(':id' => $_SESSION['id_empresa']));
$linha_dados_logo = $query_dados_logo->fetch(PDO::FETCH_ASSOC);

$query_saldo = $connection->prepare("SELECT c.num_conta, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id = m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa = emp.id_empresa INNER JOIN utilizador u ON u.id_empresa = emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
$query_saldo->execute(array('id_empresa' => $_SESSION['id_empresa']));
$linha_saldo = $query_saldo->fetch(PDO::FETCH_ASSOC);

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$data_i_tmp = explode(" ", date('Y-m-d H:i:s', strtotime($_GET['data_inicial'])));
$data_inicial = date('Y-m-d H:i:s', strtotime($data_i_tmp[0] . " " . "00:00:00"));
$data_f_tmp = explode(" ", date('Y-m-d H:i:s', strtotime($_GET['data_final'])));
$data_final = date('Y-m-d H:i:s', strtotime($data_f_tmp[0] . " " . "23:59:59"));
$query_movimentos = $connection->prepare("SELECT m.id, date_format(m.data_op,'%d-%m-%Y') as data_op, m.tipo, m.descricao, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' AND m.data_op BETWEEN :data_inicial AND :data_final ORDER BY m.id ASC, m.date_reg ASC");
$query_movimentos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':data_inicial' => $data_inicial, ':data_final' => $data_final));

$datetime = new DateTime();
?>
<page backtop="10mm" backbottom="10mm">
    <page_footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left; width: 20%">Simemp <?php echo $datetime->format('Y'); ?></td>
                <td style="text-align: center; width: 60%">Documento válido apenas no Simemp &copy; &reg;</td>
                <td style="text-align: right; width: 20%">Pág. [[page_cu]]/[[page_nb]]</td>
            </tr>
        </table>
    </page_footer>
    <table cellspacing="0" style="width: 100%;font-size: 80%;">
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="width: 100%; vertical-align: middle; height: <?php echo $linha_dados_logo['print_height'] ?>;"><img src="<?php echo '../' . $linha_dados_logo['path_imagem_print']; ?>" style="height: <?php echo $linha_dados_logo['print_height']?>; width: <?php echo $linha_dados_logo['print_width']?>;"></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 16pt;">
        <tr>
            <td style="height: 40px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px;">Resumo de conta</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%;">
        <tr>
            <td style="border-bottom: 2px #EEE solid;width:100%;"></td>
        </tr>
        <tr>
            <td style="height: 30px; width:100%;"></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">Saldo contabilístico</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;"><?php echo number_format($linha_saldo['saldo_contab'], 2, ",", "."); ?> <?php echo $linha_moeda['simbolo']; ?></td>
        </tr>
        <tr>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">Saldo autorizado</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;"><?php echo number_format($linha_saldo['saldo_controlo'], 2, ",", "."); ?> <?php echo $linha_moeda['simbolo']; ?></td>
        </tr>
        <tr>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">Saldo disponível</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;"><?php echo number_format($linha_saldo['saldo_disp'], 2, ",", "."); ?> <?php echo $linha_moeda['simbolo']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 16pt;">
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px;">Movimentos - EUR</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%;">
        <tr>
            <td style="border-bottom: 2px #EEE solid;width:100%;"></td>
        </tr>
        <tr>
            <td style="height: 30px; width:100%;"></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 12pt;">
        <tr>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">Conta</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;"><?php echo $linha_saldo['num_conta'] ?></td>
        </tr>
        <tr>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">Data</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 20px; height: 30px; vertical-align: top;"><?php echo $_GET['data_virtual']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 12pt; border: 1px; border-style: solid; border-color: black;">
        <tr>
            <td colspan="5" style="width: 100%; height: 35px; padding-left: 10px;">Conta <?php echo $linha_saldo['num_conta'] ?> (<?php echo $_GET['data_virtual']; ?>)</td>
        </tr>
        <tr>
            <td style="width: 15%; border-top: 1px; border-style: solid; border-color: black; vertical-align: middle; text-align: center;">DATA</td>
            <td style="width: 45%; border-top: 1px; border-left: 1px; border-style: solid; border-color: black; vertical-align: middle; text-align: center;">DESCRIÇÃO</td>
            <td style="width: 20%; border-top: 1px; border-left: 1px; border-style: solid; border-color: black; vertical-align: middle; text-align: center;">MOVIMENTO</td>
            <td style="width: 20%; border-top: 1px; border-left: 1px; border-style: solid; border-color: black; vertical-align: middle; text-align: center;">SALDO CONTROLO</td>
        </tr>
        <?php while ($linha_mov = $query_movimentos->fetch(PDO::FETCH_ASSOC)) { ?>
            <?php
            if ($linha_mov['debito'] == 0) {
                $sinal = "+";
                $valor = $linha_mov['credito'];
            } else {
                $sinal = "-";
                $valor = $linha_mov['debito'];
            }
            ?>
            <?php
            if ($linha_mov['saldo_disp'] >= 0) {
                $sinal_saldo = "+";
            } else {
                $sinal_saldo = "-";
            }
            ?>
            <tr>
                <td style="width: 15%; border-top: 1px; border-style: solid; border-color: black; vertical-align: middle; text-align: center;"><?php echo $linha_mov['data_op']; ?></td>
                <td style="width: 45%; padding-left: 12px; border-left: 1px; border-top: 1px; border-style: solid; border-color: black; vertical-align: middle;"><?php echo $linha_mov['descricao']; ?></td>
                <td style="width: 20%; padding-right: 12px; border-left: 1px; border-top: 1px; border-style: solid; border-color: black; vertical-align: middle; text-align: right;"><?php echo $sinal . " " . number_format($valor, 2, ",", "."); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                <td style="width: 20%; padding-right: 12px; border-left: 1px; border-top: 1px; border-style: solid; border-color: black; vertical-align: middle; text-align: right;"><?php echo $sinal_saldo; ?> <?php echo number_format($linha_mov['saldo_disp'], 2, ",", "."); ?> <?php echo $linha_moeda['simbolo']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <table cellspacing="0" style="font-size: 11pt;">
        <tr>
            <td style="width: 100%; padding-top: 5px; padding-left: 10px;">Data/Hora do pedido <?php echo $_GET['data_virtual']; ?></td>
        </tr>
    </table>
</page>