<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 10:57:22
*/

include('../conf/check_pastas.php');

$query_dados_ad = $connection->prepare("SELECT f.id AS f_id, f.nome AS f_nome, f.morada AS f_morada, f.cp4 AS f_cp4, f.cp3 AS f_cp3, f.localidade AS f_localidade, f.nipc AS f_nipc, f.cap_soc AS f_capsoc, f.logo AS f_logo, emp.nipc AS c_nipc, emp.nome AS c_nome, emp.morada AS c_morada, emp.cod_postal AS c_codpostal, emp.localidade AS c_localidade, emp.pais AS c_pais, a.id_adiantamento AS a_id, DATE_FORMAT(a.data_virt, '%d-%m-%Y') AS data_ad, a.valor-a.iva AS valor_s_iva, a.tx_iva, a.iva, a.valor, m.nome, m.simbolo FROM adiantamento a INNER JOIN empresa emp ON a.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON f.id=a.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND a.id_adiantamento=:id LIMIT 1");
$query_dados_ad->execute(array(':id' => $_GET['id']));
$linha_dados_ad = $query_dados_ad->fetch(PDO::FETCH_ASSOC);

//$query_num_ad = $connection->prepare("SELECT @row_num:=@row_num+1 AS rank, T1.* FROM (SELECT a.id_adiantamento AS a_id FROM adiantamento a INNER JOIN fornecedor f ON f.id=a.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r"); // estava esta (problemas mariadb para mysql)
$query_num_ad = $connection->prepare("SELECT @row_num:=@row_num+1 AS 'rank', T1.* FROM (SELECT a.id_adiantamento AS a_id FROM adiantamento a INNER JOIN fornecedor f ON f.id=a.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r");// meti esta
$query_num_ad->execute(array(':fornecedor' => $linha_dados_ad['f_id']));

while ($linha_num_ad = $query_num_ad->fetch(PDO::FETCH_ASSOC)) {
    if($linha_dados_ad['a_id'] == $linha_num_ad['a_id']) {
        $num_ad = $linha_num_ad['rank'];
    }
}

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
                <td style="width: 15%; vertical-align: middle; text-align: center;"><img src="<?php echo '../' . $linha_dados_ad['f_logo']; ?>" style="height:80px; width:80px;"></td>
                <td style="width: 35%;">
                    <div style="line-height:1.25;"><p style="margin: 0;"><span style="font-weight: bold;"><?php echo $linha_dados_ad['f_nome']; ?></span></p>
                        <p style="margin: 0;">Sede Fiscal: <?php echo $linha_dados_ad['f_morada']; ?></p>
                        <p style="margin: 0 0 0.25em;"><?php echo $linha_dados_ad['f_cp4']; ?> - <?php echo $linha_dados_ad['f_cp3']; ?> <?php echo $linha_dados_ad['f_localidade']; ?></p>
                        <p style="margin: 0">NIPC: <?php echo $pais.$linha_dados_ad['f_nipc']; ?> | Cap. Social: <?php echo number_format($linha_dados_ad['f_capsoc'], 0, ",", "."); ?> <?php echo $linha_dados_ad['nome']; ?></p>
                    </div>
                </td>
                <td style="width: 5%;">&nbsp;</td>
                <td style="width: 45%; vertical-align: top;">
                    <div style="border: 1px solid;line-height: 1.25; width: 80%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span style="font-weight: bold;">Adiantamento:</span>  <?php echo $num_ad; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Data do adiantamento: <?php echo $linha_dados_ad['data_ad']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">NIF: <?php echo $linha_dados_ad['c_nipc']; ?></p>
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
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_codpostal']; ?> <?php echo $linha_dados_ad['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_ad['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Morada faturação:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_codpostal']; ?> <?php echo $linha_dados_ad['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_ad['c_pais']; ?></p>
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
                <td style="height: 30px; width:20%;"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Descrição Artigo</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">PVP Uni.</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Qtd.</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">IVA</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Valor IVA</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Total</td>
            </tr>
            <tr>
                <td style="border-color: #DDD;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Adiantamento a fornecedor</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['valor_s_iva'], 2, ",", ".").' '.$linha_dados_ad['simbolo']; ?></td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;">1,00</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['tx_iva'], 2, ",", ".").' %'; ?></td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['iva'], 0, ",", ".").' '.$linha_dados_ad['simbolo']; ?></td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['valor'], 2, ",", ".").' '.$linha_dados_ad['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="height: 30px; width:40%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:10%;"></td>
                <td style="height: 30px; width:20%;"></td>
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
                <td style="width:12%;border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['valor_s_iva'], 2, ",", ".").' '.$linha_dados_ad['simbolo']; ?></td>
                <td style="width:3%;" rowspan="4"></td>
            </tr>
            <?php /* <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Valor desconto</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['e_desconto'], 2, ",", ".").' '.$linha_dados_ad['simbolo']; ?></td>
            </tr> */ ?>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Valor IVA</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['iva'], 2, ",", ".").' '.$linha_dados_ad['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Total</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['valor'], 2, ",", ".").' '.$linha_dados_ad['simbolo']; ?></td>
            </tr>
        </table>
    </page>
<?php } else { /* ?>
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
                <td style="width: 15%; vertical-align: middle; text-align: center;"><img src="<?php echo '../' . $linha_dados_ad['f_logo']; ?>" style="height:80px; width:80px;"></td>
                <td style="width: 35%;">
                    <div style="line-height:1.25;"><p style="margin: 0;"><span style="font-weight: bold;"><?php echo $linha_dados_ad['f_nome']; ?></span></p>
                        <p style="margin: 0;">Fiscal headquarters: <?php echo $linha_dados_ad['f_morada']; ?></p>
                        <p style="margin: 0 0 0.25em;"><?php echo $linha_dados_ad['f_cp4']; ?> - <?php echo $linha_dados_ad['f_cp3']; ?> <?php echo $linha_dados_ad['f_localidade']; ?></p>
                        <p style="margin: 0">NIPC: <?php if ($pais == 'EUA') echo 'EU'.$linha_dados_ad['f_nipc']; else echo $pais.$linha_dados_ad['f_nipc'];?> | Capital: <?php echo number_format($linha_dados_ad['f_capsoc'], 0, ",", "."); ?> <?php echo $linha_dados_ad['nome']; ?></p>
                    </div>
                </td>
                <td style="width: 5%;">&nbsp;</td>
                <td style="width: 45%; vertical-align: top;">
                    <div style="border: 1px solid;line-height: 1.25; width: 80%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span style="font-weight: bold;">Invoice:</span>  <?php echo $num_fat; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Order reference: <?php echo $linha_dados_ad['ref']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Order date: <?php echo $linha_dados_ad['e_data_enc']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">NIF: <?php echo $linha_dados_ad['c_nipc']; ?></p>
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
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_codpostal']; ?> <?php echo $linha_dados_ad['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_ad['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Billing address:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_ad['c_codpostal']; ?> <?php echo $linha_dados_ad['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_ad['c_pais']; ?></p>
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
            <?php while ($linha_dados_ad = $query_enc_linhas->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="border-color: #DDD;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;"><?php echo $linha_dados_ad['p_nome']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['de_preco'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['de_qtd'], 2, ",", "."); ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['de_iva'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['valor'], 0, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['de_desc'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['de_tot'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
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
                <td style="width:12%;border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['tot_sdesc'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
                <td style="width:3%;" rowspan="4"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Discount Value</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['e_desconto'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">VAT Value</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['e_iva'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Total</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_ad['e_total'], 2, ",", "."); ?><?php echo $linha_dados_ad['simbolo']; ?></td>
            </tr>
        </table>
    </page>  
<?php */ } ?>
