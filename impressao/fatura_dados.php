<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 10:57:22
*/

include('../conf/check_pastas.php');

// $query_dados_enc = $connection->prepare("SELECT f.id AS f_id, f.nome AS f_nome, f.morada AS f_morada, f.cp4 AS f_cp4, f.cp3 AS f_cp3, f.localidade AS f_localidade, f.nipc AS f_nipc, f.cap_soc AS f_capsoc, f.logo AS f_logo, emp.nipc AS c_nipc, emp.nome AS c_nome, emp.morada AS c_morada, emp.cod_postal AS c_codpostal, emp.localidade AS c_localidade, emp.pais AS c_pais, e.id AS e_id, e.ref AS ref, e.`data` AS e_data_enc, e.desconto AS e_desconto, e.iva AS e_iva, e.total AS e_total, e.total+e.desconto-e.iva AS tot_sdesc FROM encomenda e INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON f.id=e.id_fornecedor WHERE emp.ativo='1' AND e.id=:id");
$query_dados_enc = $connection->prepare("SELECT f.id AS f_id, f.nome AS f_nome, f.morada AS f_morada, f.cp4 AS f_cp4, f.cp3 AS f_cp3, f.localidade AS f_localidade, f.nipc AS f_nipc, f.cap_soc AS f_capsoc, f.logo AS f_logo, emp.nipc AS c_nipc, emp.nome AS c_nome, emp.morada AS c_morada, emp.cod_postal AS c_codpostal, emp.localidade AS c_localidade, emp.pais AS c_pais, e.id AS e_id, e.ref AS ref, e.`data` AS e_data_enc, e.desconto AS e_desconto, e.iva AS e_iva, e.irc AS e_irc, e.total AS e_total, IF(e.irc IS NOT NULL AND e.irc > 0, e.total+e.desconto+e.irc, e.total+e.desconto-e.iva) AS tot_sdesc, m.nome, m.simbolo FROM encomenda e INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON f.id=e.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND e.id=:id");
$query_dados_enc->execute(array(':id' => $_GET['id']));
$linha_dados_enc = $query_dados_enc->fetch(PDO::FETCH_ASSOC);

//$query_num_fat = $connection->prepare("SELECT @row_num:=@row_num+1 AS rank, T1.* FROM (SELECT e.id AS e_id FROM encomenda e INNER JOIN fornecedor f ON f.id=e.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r");//estava esta(problemas mariaDB para mysql)
$query_num_fat = $connection->prepare("SELECT (@row_number:=@row_number + 1) AS 'rank', T1.* FROM (SELECT e.id AS e_id FROM encomenda e INNER JOIN fornecedor f ON f.id=e.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_number:=0) r");//meti esta
$query_num_fat->execute(array(':fornecedor' => $linha_dados_enc['f_id']));

$tx_dsc = $linha_dados_enc['f_id'] == 23 ? 'IRC' : 'IVA';
$tx_key = $linha_dados_enc['f_id'] == 23 ? 'de_irc' : 'de_iva';
$tx_val = $linha_dados_enc['f_id'] == 23 ? $linha_dados_enc['e_irc'] : $linha_dados_enc['e_iva'];

while ($linha_num_fat = $query_num_fat->fetch(PDO::FETCH_ASSOC)) {
    if($linha_dados_enc['e_id'] == $linha_num_fat['e_id']) {
        $num_fat = $linha_num_fat['rank'];
    }
}

// Apresenta VÁRIAS linhas de taxas
// $query_enc_linhas = $connection->prepare("SELECT rp.valor, rp.simbolo, p.nome AS p_nome, de.preco AS de_preco, de.quantidade AS de_qtd, de.iva AS de_iva, de.irc AS de_irc, de.desconto AS de_desc, de.total_linha AS de_tot FROM encomenda e INNER JOIN detalhes_encomenda de ON e.id=de.id_encomenda INNER JOIN produto p ON de.id_produto=p.id INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE e.id=:id");

// Apresenta ÚLTIMA taxa associada ao produto // **ERRADO, prq ÚLTIMA taxa pode ser diferente de taxa no momento de Encomenda
$query_enc_linhas = $connection->prepare("SELECT rp.valor, rp.simbolo, p.nome AS p_nome, de.preco AS de_preco, de.quantidade AS de_qtd, de.iva AS de_iva, de.irc AS de_irc, de.desconto AS de_desc, de.total_linha AS de_tot FROM encomenda e INNER JOIN detalhes_encomenda de ON e.id=de.id_encomenda INNER JOIN produto p ON de.id_produto=p.id INNER JOIN (SELECT * FROM regra_produto rp ORDER BY rp.id_produto ASC, rp.date_reg DESC) AS rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE e.id=:id GROUP BY p.id");
// OU // $query_enc_linhas = $connection->prepare("SELECT rp.valor, '%' AS simbolo, p.nome AS p_nome, de.preco AS de_preco, de.quantidade AS de_qtd, de.iva AS de_iva, de.irc AS de_irc, de.desconto AS de_desc, de.total_linha AS de_tot FROM encomenda e INNER JOIN detalhes_encomenda de ON e.id=de.id_encomenda INNER JOIN produto p ON de.id_produto=p.id INNER JOIN (SELECT * FROM (SELECT r.nome_regra, rp.* FROM regra_produto rp INNER JOIN produto p ON rp.id_produto=p.id INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE r.nome_regra LIKE 'Taxa%IVA%' OR r.nome_regra LIKE 'Taxa%IRC%' ORDER BY rp.date_reg DESC) AS last_rp GROUP BY last_rp.id_produto) AS rp ON p.id=rp.id_produto WHERE e.id=:id AND rp.nome_regra LIKE IF(de.iva>0, 'Taxa%IVA%', 'Taxa%IRC%')");

// Calcula taxa apartir de valores de "detalhe_encomenda" // **ERRADO, prq, devido aos arredondamentos taxa exibida pode ser errada
// $query_enc_linhas = $connection->prepare("SELECT IF(de.iva>0, ROUND(de.iva/(de.preco*de.quantidade), 2), ROUND(de.irc/(de.preco*de.quantidade), 2))*100 AS valor, '%' AS simbolo, p.nome AS p_nome, de.preco AS de_preco, de.quantidade AS de_qtd, de.iva AS de_iva, de.irc AS de_irc, de.desconto AS de_desc, de.total_linha AS de_tot FROM encomenda e INNER JOIN detalhes_encomenda de ON e.id=de.id_encomenda INNER JOIN produto p ON de.id_produto=p.id WHERE e.id=:id");
$query_enc_linhas->execute(array(':id' => $_GET['id']));

/* $query_moeda = $connection->prepare("SELECT mo.simbolo, mo.nome FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC); */

$datetime = new DateTime();
$pais = $_GET['p'];

if ($pais == 'PT' || $pais == 'BR') { ?>
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
                <td style="height: 20px;"></td>
                <td style="height: 20px;"></td>
            </tr>
            <tr>
                <td style="width: 15%; vertical-align: middle; text-align: center;"><img src="<?php echo '../' . $linha_dados_enc['f_logo']; ?>" style="height:80px; width:80px;"></td>
                <td style="width: 35%;">
                    <div style="line-height:1.25;"><p style="margin: 0;"><span style="font-weight: bold;"><?php echo $linha_dados_enc['f_nome']; ?></span></p>
                        <p style="margin: 0;">Sede Fiscal: <?php echo $linha_dados_enc['f_morada']; ?></p>
                        <p style="margin: 0 0 0.25em;"><?php echo $linha_dados_enc['f_cp4']; ?> - <?php echo $linha_dados_enc['f_cp3']; ?> <?php echo $linha_dados_enc['f_localidade']; ?></p>
                        <p style="margin: 0">NIPC: <?php echo $pais.$linha_dados_enc['f_nipc']; ?> | Cap. Social: <?php echo number_format($linha_dados_enc['f_capsoc'], 0, ",", "."); ?> <?php echo $linha_dados_enc['nome']; ?></p>
                    </div>
                </td>
                <td style="width: 5%;">&nbsp;</td>
                <td style="width: 45%; vertical-align: top;">
                    <div style="border: 1px solid;line-height: 1.25; width: 80%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span style="font-weight: bold;">Fatura:</span>  <?php echo $num_fat; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Ref. encomenda: <?php echo $linha_dados_enc['ref']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Data da encomenda: <?php echo $linha_dados_enc['e_data_enc']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">NIF: <?php echo $linha_dados_enc['c_nipc']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
            </tr>
        </table>
        <table cellspacing="0" style="width: 100%;font-size: 85%;">
            <tr>
                <td style="height: 30px;"></td>
                <td style="height: 30px;"></td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Morada expedição:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_codpostal']; ?> <?php echo $linha_dados_enc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_enc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Morada faturação:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_codpostal']; ?> <?php echo $linha_dados_enc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_enc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
            </tr>
        </table>
        <table cellspacing="2" style="width: 100%;font-size: 90%;">
            <tr>
                <td style="height: 30px; width:40%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Descrição Artigo</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">PVP Uni.</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Qtd.</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;"><?php echo $tx_dsc; ?></td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Valor <?php echo $tx_dsc; ?></td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Desconto</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Total</td>
            </tr>
            <?php while ($linha_enc_linhas = $query_enc_linhas->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="border-color: #DDD;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;"><?php echo $linha_enc_linhas['p_nome']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_preco'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_qtd'], 2, ",", "."); ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['valor'], 0, ",", "."); ?><?php echo $linha_enc_linhas['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas[$tx_key], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_desc'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_tot'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td style="height: 30px; width:40%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
            </tr>
        </table>
        <table cellspacing="0" style="width: 100%;">
            <tr>
                <td style="border-bottom: 10px #EEE solid;width:100%;"></td>
            </tr>
            <tr>
                <td style="height: 30px; width:100%;"></td>
            </tr>
        </table>
        <table cellspacing="2" style="width: 100%;font-size: 90%;">
            <tr>
                <td style="width:15%; font-size: 90%; text-align: center; vertical-align: top;" rowspan="4"></td>
                <td style="width:55%;" rowspan="4"></td>
                <td style="width:15%;background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Subtotal</td>
                <td style="width:12%;border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_enc['tot_sdesc'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                <td style="width:3%;" rowspan="4"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Valor desconto</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_enc['e_desconto'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Valor <?php echo $tx_dsc ?></td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($tx_val, 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Total</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_enc['e_total'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
            </tr>
        </table>
    </page>
<?php } else { ?>
      <page backtop="10mm" backbottom="10mm">
        <page_footer>
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: left; width: 20%">Simemp <?php echo $datetime->format('Y'); ?></td>
                    <td style="text-align: center; width: 60%">Document valid only at Simemp &copy; &reg;</td>
                    <td style="text-align: right; width: 20%">Pág. [[page_cu]]/[[page_nb]]</td>
                </tr>
            </table>
        </page_footer>
        <table cellspacing="0" style="width: 100%;font-size: 80%;">
            <tr>
                <td style="height: 20px;"></td>
                <td style="height: 20px;"></td>
                <td style="height: 20px;"></td>
            </tr>
            <tr>
                <td style="width: 15%; vertical-align: middle; text-align: center;"><img src="<?php echo '../' . $linha_dados_enc['f_logo']; ?>" style="height:80px; width:80px;"></td>
                <td style="width: 35%;">
                    <div style="line-height:1.25;"><p style="margin: 0;"><span style="font-weight: bold;"><?php echo $linha_dados_enc['f_nome']; ?></span></p>
                        <p style="margin: 0;">Fiscal headquarters: <?php echo $linha_dados_enc['f_morada']; ?></p>
                        <p style="margin: 0 0 0.25em;"><?php echo $linha_dados_enc['f_cp4']; ?> - <?php echo $linha_dados_enc['f_cp3']; ?> <?php echo $linha_dados_enc['f_localidade']; ?></p>
                        <p style="margin: 0">NIPC: <?php if ($pais == 'EUA') echo 'EU'.$linha_dados_enc['f_nipc']; else echo $pais.$linha_dados_enc['f_nipc'];?> | Capital: <?php echo number_format($linha_dados_enc['f_capsoc'], 0, ",", "."); ?> <?php echo $linha_dados_enc['nome']; ?></p>
                    </div>
                </td>
                <td style="width: 5%;">&nbsp;</td>
                <td style="width: 45%; vertical-align: top;">
                    <div style="border: 1px solid;line-height: 1.25; width: 80%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span style="font-weight: bold;">Invoice:</span>  <?php echo $num_fat; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Order reference: <?php echo $linha_dados_enc['ref']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Order date: <?php echo $linha_dados_enc['e_data_enc']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">NIF: <?php echo $linha_dados_enc['c_nipc']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
            </tr>
        </table>
        <table cellspacing="0" style="width: 100%;font-size: 85%;">
            <tr>
                <td style="height: 30px;"></td>
                <td style="height: 30px;"></td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Shipping address:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_codpostal']; ?> <?php echo $linha_dados_enc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_enc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Billing address:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_enc['c_codpostal']; ?> <?php echo $linha_dados_enc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_enc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
            </tr>
        </table>
        <table cellspacing="2" style="width: 100%;font-size: 90%;">
            <tr>
                <td style="height: 30px; width:40%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Product description</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">PVP Uni.</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Qtd.</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">VAT</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">VAT Value</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Discount</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Total</td>
            </tr>
            <?php while ($linha_enc_linhas = $query_enc_linhas->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="border-color: #DDD;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;"><?php echo $linha_enc_linhas['p_nome']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_preco'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_qtd'], 2, ",", "."); ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_iva'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['valor'], 0, ",", "."); ?><?php echo $linha_enc_linhas['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_desc'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_enc_linhas['de_tot'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td style="height: 30px; width:40%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
            </tr>
        </table>
        <table cellspacing="0" style="width: 100%;">
            <tr>
                <td style="border-bottom: 10px #EEE solid;width:100%;"></td>
            </tr>
            <tr>
                <td style="height: 30px; width:100%;"></td>
            </tr>
        </table>
        <table cellspacing="2" style="width: 100%;font-size: 90%;">
            <tr>
                <td style="width:15%; font-size: 90%; text-align: center; vertical-align: top;" rowspan="4"></td>
                <td style="width:55%;" rowspan="4"></td>
                <td style="width:15%;background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Subtotal</td>
                <td style="width:12%;border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_enc['tot_sdesc'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
                <td style="width:3%;" rowspan="4"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Discount Value</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_enc['e_desconto'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">VAT Value</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_enc['e_iva'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Total</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_enc['e_total'], 2, ",", "."); ?><?php echo $linha_dados_enc['simbolo']; ?></td>
            </tr>
        </table>
    </page>  
<?php } ?>
