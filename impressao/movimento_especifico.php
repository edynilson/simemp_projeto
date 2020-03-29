<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 10:54:21
*/

include('../conf/check_pastas.php');

$query_dados_logo = $connection->prepare("SELECT b.path_imagem_print, b.print_height, b.print_width FROM banco b INNER JOIN conta c ON b.id=c.id_banco INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id");
$query_dados_logo->execute(array(':id' => $_SESSION['id_empresa']));
$linha_dados_logo = $query_dados_logo->fetch(PDO::FETCH_ASSOC);

$connection->beginTransaction();
$query_movimento1 = $connection->prepare("SET @row_num=0");
$query_movimento1->execute();
//$query_movimento2 = $connection->prepare("SELECT @row_num:=@row_num+1 AS rank, T1.* FROM (SELECT b.nome AS nome_banco, m.id, date_format(m.data_op,'%d-%m-%Y %H:%i:%s') as data_op, m.tipo, m.descricao, m.debito, m.credito, m.saldo_disp, IF(m.ordenante IS NULL, NULL, (SELECT emp.nome FROM empresa emp INNER JOIN movimento m ON emp.id_empresa=m.ordenante WHERE emp.ativo='1' AND m.id=:id_mov)) AS nome, c.nib FROM movimento m INNER JOIN conta c ON m.id_conta=c.id INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY m.id ASC, m.date_reg ASC) AS T1, (SELECT @row_num:=0) r");
$query_movimento2 = $connection->prepare("SELECT @row_num:=@row_num+1 AS rank, T1.* FROM (SELECT DISTINCT m.id, b.nome AS nome_banco, date_format(m.data_op,'%d-%m-%Y %H:%i:%s') as data_op, m.tipo, m.descricao, m.debito, m.credito, m.saldo_disp, IF(m.ordenante IS NULL, NULL, (SELECT emp.nome FROM empresa emp INNER JOIN movimento m ON emp.id_empresa=m.ordenante WHERE emp.ativo='1' AND m.id=:id_mov)) AS nome, c.nib FROM movimento m INNER JOIN conta c ON m.id_conta=c.id INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY m.id ASC, m.date_reg ASC) AS T1, (SELECT @row_num:=0) r");
$query_movimento2->execute(array(':id_mov' => $_GET['id_mov'], ':id_empresa' => $_SESSION['id_empresa']));
$connection->commit();

while ($linha_mov = $query_movimento2->fetch(PDO::FETCH_ASSOC)) {
    if ($linha_mov['id'] == $_GET['id_mov']) {
        $linha_movimento = $linha_mov;
    }
}

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$nib_raw = str_split($linha_movimento['nib']);
$nib = $nib_raw[0] . $nib_raw[1] . $nib_raw[2] . $nib_raw[3] . " " . $nib_raw[4] . $nib_raw[5] . $nib_raw[6] . $nib_raw[7] . " " . $nib_raw[8] . $nib_raw[9] . $nib_raw[10] . $nib_raw[11] . $nib_raw[12] . $nib_raw[13] . $nib_raw[14] . $nib_raw[15] . $nib_raw[16] . $nib_raw[17] . $nib_raw[18] . " " . $nib_raw[19] . $nib_raw[20];

if ($linha_movimento['debito'] == 0) {
    $sinal = "+";
    $valor = number_format($linha_movimento['credito'], 2, ",", ".");
} else {
    $sinal = "-";
    $valor = number_format($linha_movimento['debito'], 2, ",", ".");
}

if ($linha_movimento['nome'] != 0) {
    $destinatario = $linha_movimento['nome'];
} else {
    $destinatario = $linha_movimento['nome_banco'];
}

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
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="width: 100%; vertical-align: middle; height: <?php echo $linha_dados_logo['print_height'] ?>;"><img src="<?php echo '../' . $linha_dados_logo['path_imagem_print']; ?>" style="height: <?php echo $linha_dados_logo['print_height'] ?>; width: <?php echo $linha_dados_logo['print_width'] ?>;"></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 15pt;">
        <tr>
            <td style="height: 50px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px;">Data/Hora: </td>
            <td style="padding-left: 10px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 10px;"><?php echo $_GET['data_virtual']; ?></td>
            <td style="padding-left: 10px; height: 30px; vertical-align: top;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding-left: 10px;">NIB: </td>
            <td style="padding-left: 10px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 10px;"><?php echo $nib; ?></td>
            <td style="padding-left: 10px; height: 30px; vertical-align: top;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding-left: 10px;">Saldo: </td>
            <td style="padding-left: 10px; height: 30px; vertical-align: top;">&nbsp;</td>
            <td style="padding-left: 10px;"><?php echo number_format($linha_movimento['saldo_disp'], 2, ",", "."); ?> <?php echo $linha_moeda['simbolo']; ?></td>
            <td style="padding-left: 10px; height: 120px; vertical-align: top;">&nbsp;</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 10px; width: 50%">Movimento</td>
            <td style="padding-right: 20px; width: 50%; text-align: right;">Nº: <?php echo $linha_movimento['rank']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%;">
        <tr>
            <td style="border-bottom: 2px #CCC solid;width:100%;"></td>
        </tr>
        <tr>
            <td style="height: 10px; width:100%;"></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 11pt;">
        <tr>
            <td style="height: 35px; width: 20%; vertical-align: middle; padding-left: 5px;">Data/Hora</td>
            <td style="height: 35px; width: 80%; vertical-align: middle; padding-left: 5px;"><?php echo $linha_movimento['data_op']; ?></td>
        </tr>
        <tr>
            <td style="height: 35px; width: 20%; vertical-align: middle; padding-left: 5px;">Nome </td>
            <td style="height: 35px; width: 80%; vertical-align: middle; padding-left: 5px;"><?php echo $destinatario; ?></td>
        </tr>
        <tr>
            <td style="height: 35px; width: 20%; vertical-align: middle; padding-left: 5px;">Tipo: </td>
            <td style="height: 35px; width: 80%; vertical-align: middle; padding-left: 5px;"><?php echo $linha_movimento['tipo'] . " " . $sinal; ?></td>
        </tr>
        <tr>
            <td style="height: 35px; width: 20%; vertical-align: middle; padding-left: 5px;">Valor: </td>
            <td style="height: 35px; width: 80%; vertical-align: middle; padding-left: 5px;"><?php echo $valor; ?> <?php echo $linha_moeda['simbolo']; ?></td>
        </tr>
        <tr>
            <td style="height: 35px; width: 20%; vertical-align: middle; padding-left: 5px;">Descritivo</td>
            <td style="height: 35px; width: 80%; vertical-align: middle; padding-left: 5px; padding-right: 15px;"><?php echo $linha_movimento['descricao']; ?></td>
        </tr>
    </table>
</page>