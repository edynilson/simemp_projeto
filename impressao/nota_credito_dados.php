<?php

include('../conf/check_pastas.php');

//$query_dados_enc = $connection->prepare("SELECT f.id AS f_id, f.nome AS f_nome, f.morada AS f_morada, f.cp4 AS f_cp4, f.cp3 AS f_cp3, f.localidade AS f_localidade, f.nipc AS f_nipc, f.cap_soc AS f_capsoc, f.logo AS f_logo, emp.nipc AS c_nipc, emp.nome AS c_nome, emp.morada AS c_morada, emp.cod_postal AS c_codpostal, emp.localidade AS c_localidade, emp.pais AS c_pais, e.id AS e_id, e.ref AS ref, e.`data` AS e_data_enc, e.desconto AS e_desconto, e.iva AS e_iva, e.total AS e_total, e.total+e.desconto-e.iva AS tot_sdesc FROM encomenda e INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON f.id=e.id_fornecedor WHERE emp.ativo='1' AND e.id=:id");
$query_dados_nc = $connection->prepare("SELECT f.id AS f_id, f.nome AS f_nome, f.morada AS f_morada, f.cp4 AS f_cp4, f.cp3 AS f_cp3, f.localidade AS f_localidade, f.nipc AS f_nipc, f.cap_soc AS f_capsoc, f.logo AS f_logo, emp.nipc AS c_nipc, emp.nome AS c_nome, emp.morada AS c_morada, emp.cod_postal AS c_codpostal, emp.localidade AS c_localidade, emp.pais AS c_pais, nc.id AS nc_id, nc.ref, nc.`data` AS nc_data, nc.iva AS nc_iva, nc.total AS nc_total, m.nome, m.simbolo FROM nota_credito nc INNER JOIN empresa emp ON nc.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON f.id=nc.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND nc.id=:id");
$query_dados_nc->execute(array(':id' => $_GET['id']));
$linha_dados_nc = $query_dados_nc->fetch(PDO::FETCH_ASSOC);

//$query_num_fat = $connection->prepare("SELECT @row_num:=@row_num+1 AS rank, T1.* FROM (SELECT nc.id AS nc_id FROM nota_credito nc INNER JOIN fornecedor f ON f.id=nc.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r"); //estava esta(problemas mariadb para mysql)
$query_num_fat = $connection->prepare("SELECT @row_num:=@row_num+1 AS 'rank', T1.* FROM (SELECT nc.id AS nc_id FROM nota_credito nc INNER JOIN fornecedor f ON f.id=nc.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r");//meti esta
$query_num_fat->execute(array(':fornecedor' => $linha_dados_nc['f_id']));

while ($linha_num_fat = $query_num_fat->fetch(PDO::FETCH_ASSOC)) {
    if($linha_dados_nc['nc_id'] == $linha_num_fat['nc_id']) {
        $num_nc = $linha_num_fat['rank'];
    }
}

$query_nc_linhas = $connection->prepare("SELECT rp.valor, rp.simbolo, p.nome AS p_nome, dnc.preco AS dnc_preco, dnc.quantidade AS dnc_qtd, dnc.iva AS dnc_iva, dnc.total_linha AS dnc_tot FROM nota_credito nc INNER JOIN detalhes_nota_credito dnc ON nc.id=dnc.id_nota_credito INNER JOIN produto p ON dnc.id_produto=p.id INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE nc.id=:id");
$query_nc_linhas->execute(array(':id' => $_GET['id']));

/*$query_moeda = $connection->prepare("SELECT mo.simbolo, mo.nome FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);*/

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
                <td style="width: 15%; vertical-align: middle; text-align: center;"><img src="<?php echo '../' . $linha_dados_nc['f_logo']; ?>" style="height:80px; width:80px;"></td>
                <td style="width: 35%;">
                    <div style="line-height:1.25;"><p style="margin: 0;"><span style="font-weight: bold;"><?php echo $linha_dados_nc['f_nome']; ?></span></p>
                        <p style="margin: 0;">Sede Fiscal: <?php echo $linha_dados_nc['f_morada']; ?></p>
                        <p style="margin: 0 0 0.25em;"><?php echo $linha_dados_nc['f_cp4']; ?> - <?php echo $linha_dados_nc['f_cp3']; ?> <?php echo $linha_dados_nc['f_localidade']; ?></p>
                        <p style="margin: 0">NIPC: <?php echo $linha_dados_nc['f_nipc']; ?> | Cap. Social: <?php echo number_format($linha_dados_nc['f_capsoc'], 0, ",", "."); ?> <?php echo $linha_dados_nc['nome']; ?></p>
                    </div>
                </td>
                <td style="width: 5%;">&nbsp;</td>
                <td style="width: 45%; vertical-align: top;">
                    <div style="border: 1px solid;line-height: 1.25; width: 80%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span style="font-weight: bold;">Nota de crédito:</span>  <?php echo $num_nc; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Ref. encomenda: <?php echo $linha_dados_nc['ref']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Data da encomenda: <?php echo $linha_dados_nc['nc_data']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">NIF: <?php echo $linha_dados_nc['c_nipc']; ?></p>
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
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_codpostal']; ?> <?php echo $linha_dados_nc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_nc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Morada faturação:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_codpostal']; ?> <?php echo $linha_dados_nc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_nc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
            </tr>
        </table>
        <table cellspacing="2" style="width: 100%;font-size: 90%;padding-left: 30px;">
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
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">IVA</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Valor IVA</td>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Total</td>
            </tr>
            <?php while ($linha_nc_linhas = $query_nc_linhas->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="border-color: #DDD;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;"><?php echo $linha_nc_linhas['p_nome']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_preco'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_qtd'], 2, ",", "."); ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_iva'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['valor'], 0, ",", "."); ?><?php echo $linha_nc_linhas['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_tot'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
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
                <td style="width:15%;background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Valor IVA</td>
                <td style="width:12%;border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_nc['nc_iva'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
                <td style="width:3%;" rowspan="4"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Total</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_nc['nc_total'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
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
                <td style="width: 15%; vertical-align: middle; text-align: center;"><img src="<?php echo '../' . $linha_dados_nc['f_logo']; ?>" style="height:80px; width:80px;"></td>
                <td style="width: 35%;">
                    <div style="line-height:1.25;"><p style="margin: 0;"><span style="font-weight: bold;"><?php echo $linha_dados_nc['f_nome']; ?></span></p>
                        <p style="margin: 0;">Fiscal headquarters: <?php echo $linha_dados_nc['f_morada']; ?></p>
                        <p style="margin: 0 0 0.25em;"><?php echo $linha_dados_nc['f_cp4']; ?> - <?php echo $linha_dados_nc['f_cp3']; ?> <?php echo $linha_dados_nc['f_localidade']; ?></p>
                        <p style="margin: 0">VAT number: <?php echo $linha_dados_nc['f_nipc']; ?> | Capital: <?php echo number_format($linha_dados_nc['f_capsoc'], 0, ",", "."); ?> <?php echo $linha_dados_nc['nome']; ?></p>
                    </div>
                </td>
                <td style="width: 5%;">&nbsp;</td>
                <td style="width: 45%; vertical-align: top;">
                    <div style="border: 1px solid;line-height: 1.25; width: 80%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span style="font-weight: bold;">Credit note:</span>  <?php echo $num_nc; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Order reference: <?php echo $linha_dados_nc['ref']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">Credit note date: <?php echo $linha_dados_nc['nc_data']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">TIN: <?php echo $linha_dados_nc['c_nipc']; ?></p>
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
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_codpostal']; ?> <?php echo $linha_dados_nc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_nc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="border: 1px solid;line-height: 1.25;width:90%;">
                        <p style="margin: 0 0 0.25em;padding: 5px 0 0 10px;"><span>Billing address:</span></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_nome']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_morada']; ?></p>
                        <p style="margin: 0;padding-left: 10px;"><?php echo $linha_dados_nc['c_codpostal']; ?> <?php echo $linha_dados_nc['c_localidade']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;"><?php echo $linha_dados_nc['c_pais']; ?></p>
                        <p style="margin: 0 0 0.25em;padding-left: 10px;">&nbsp;</p>
                    </div>
                </td>
            </tr>
        </table>
        <table cellspacing="2" style="width: 100%;font-size: 90%;padding-left: 30px;">
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
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;text-align: center;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0;">Total</td>
            </tr>
            <?php while ($linha_nc_linhas = $query_nc_linhas->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="border-color: #DDD;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;"><?php echo $linha_nc_linhas['p_nome']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_preco'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_qtd'], 2, ",", "."); ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_iva'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['valor'], 0, ",", "."); ?><?php echo $linha_nc_linhas['simbolo']; ?></td>
                    <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_nc_linhas['dnc_tot'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
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
                <td style="width:15%;background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">VAT Value</td>
                <td style="width:12%;border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_nc['nc_iva'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
                <td style="width:3%;" rowspan="4"></td>
            </tr>
            <tr>
                <td style="background: #EEE;border-color: #BBB;font-weight: bold;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 0 5px 5px;">Total</td>
                <td style="border-color: #DDD;text-align: right;border-width: 1px;border-radius: 1mm;border-style: solid;padding: 5px 5px 5px;"><?php echo number_format($linha_dados_nc['nc_total'], 2, ",", "."); ?><?php echo $linha_dados_nc['simbolo']; ?></td>
            </tr>
        </table>
    </page>  
<?php } ?>
