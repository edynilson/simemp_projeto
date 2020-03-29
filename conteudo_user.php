<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-21 11:12:19
*/

include('./conf/check.php');
include_once('./functions/functions.php');
include_once('./conf/common.php');

$datetime = new DateTime();

$query_tipo = $connection->prepare("SELECT id, designacao FROM tipo_entrega WHERE id<>11 ORDER BY designacao");
$query_tipo->execute();

$query_encomendas = $connection->prepare("SELECT en.id, en.ref, date_format(en.data,'%d-%m-%Y') AS data, IF(en.pago=0, 'Não', 'Sim') AS pago, p.nome_pais, p.nome_abrev AS abrev_pais, f.nome_abrev, en.total, m.simbolo FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id ORDER BY en.`data` ASC");
$query_encomendas->execute(array(':id' => $_SESSION['id_empresa']));

$query_plafond_fatura = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_plafond_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (faturas)"));
$linha_plafond_fatura = $query_plafond_fatura->fetch(PDO::FETCH_ASSOC);

$query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.para=:para AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
$query_email_recebido->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':para' => $_SESSION['id_empresa'], ':lixo' => 0, ':elim' => 0));

$query_rubricas = $connection->prepare("SELECT cod.id, cod.rubrica, cod.designacao FROM codigo cod");
$query_rubricas->execute();

$query_dados_empresa = $connection->prepare("SELECT emp.nome, emp.nipc, c.num_conta FROM empresa emp INNER JOIN conta c ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.tipo_conta='ordem' AND emp.id_empresa=:id_empresa LIMIT 1");
$query_dados_empresa->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$linha_dados_empresa = $query_dados_empresa->fetch(PDO::FETCH_ASSOC);

$query_entregas = $connection->prepare("SELECT en.id, date_format(en.data,'%d-%m-%Y') AS data, en.valor, IF(en.f_prazo='N', 'Não', 'Sim') AS prazo, IF(en.pago=0, 'Não', 'Sim') AS pago, te.designacao, en.mes, en.ano, en.ficheiro FROM entrega en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON en.id_tipo_entrega=te.id WHERE emp.ativo='1' AND emp.id_empresa=:id");
$query_entregas->execute(array(':id' => $_SESSION['id_empresa']));

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_mes_ano = $connection->prepare("SELECT c.mes, c.ano FROM calendario c INNER JOIN grupo g ON c.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND NOW() BETWEEN CONCAT(data_inicio, ' ', hora_inicio) AND CONCAT(data_fim, ' ', hora_fim) LIMIT 1");
$query_mes_ano->execute(array(':id_empresa' => $_SESSION['id_empresa']));
if ($query_mes_ano->rowCount() > 0) {
    $linha_mes_ano = $query_mes_ano->fetch(PDO::FETCH_ASSOC);
    $linha_mes_ano['dia'] = "01";
} else {
    $linha_mes_ano['mes'] = $datetime->format('m');
    $linha_mes_ano['ano'] = $datetime->format('Y');
    $linha_mes_ano['dia'] = $datetime->format('d');
}

$query_fornecedor = $connection->prepare("SELECT DISTINCT f.id, f.nome_abrev FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY f.nome_abrev ASC");
$query_fornecedor->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$arr_fornecedores = $query_fornecedor->fetchAll();

$mes_app = str_pad(($linha_mes_ano['mes'] - 1), 2, '0', STR_PAD_LEFT);
$dia_app = str_pad($linha_mes_ano['dia'], 2, '0', STR_PAD_LEFT);

//-- Adiantamento de faturas
$query_adiant_fat = $connection->prepare("SELECT a.id_adiantamento, a.nome_cliente FROM adiantamento a WHERE a.id_empresa=:id_empresa AND a.pago='0' AND a.id_fornecedor IS NULL");
$query_adiant_fat->execute(array(':id_empresa' => $_SESSION['id_empresa']));

//-- Notas de crédito
$query_enc_nota_credito = $connection->prepare("SELECT en.id, en.ref, date_format(en.data,'%d-%m-%Y') AS data, IF(en.pago=0, 'Não', 'Sim') AS pago, p.nome_pais, p.nome_abrev AS abrev_pais, f.nome_abrev, en.total, m.simbolo FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id AND NOT EXISTS (SELECT 1 FROM nota_credito nc WHERE nc.id_encomenda=en.id) ORDER BY en.`data` ASC");
$query_enc_nota_credito->execute(array(':id' => $_SESSION['id_empresa']));

$query_nc = $connection->prepare("SELECT nc.id, nc.ref, f.nome_abrev, nc.iva, nc.total, m.simbolo, p.nome_pais, p.nome_abrev AS abrev_pais, IF(nc.pago=1, 'Sim', 'Não') AS pago, nc.`data` FROM nota_credito nc INNER JOIN fornecedor f ON nc.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE nc.id_empresa=:id_empresa ORDER BY nc.id ASC");
$query_nc->execute(array(':id_empresa' => $_SESSION['id_empresa']));

//-- Desconto de fornecedores
// $query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT u.id_entidade, f.nome_abrev AS fornecedor, p.nome AS produto, p.descricao, fam.designacao AS familia, fpd.desconto, fpd.prazo_pag FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN produto p ON fpd.id_produto=p.id INNER JOIN familia fam ON p.familia=fam.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id) AS descontos ON atualuser.id_entidade=descontos.id_entidade WHERE descontos.id_entidade IS NOT NULL");
//$query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', CONCAT(descontos.prazo_pag, ' dias')) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia FROM (SELECT u.id_entidade, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id WHERE fpd.active=:active) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC, info_desc.produto ASC");// estava esta (problemas ao passar do mariaDB para o mysql)
$query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', CONCAT(descontos.prazo_pag, ' dias')) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia FROM (SELECT u.id_entidade, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id WHERE fpd.active=:active) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC/*, info_desc.produto ASC*/");//meti esta (comentei o info_desc.produto ASC e funciona bem...)
$query_fp_desc->execute(array(':id_utilizador' => $_SESSION['id_utilizador'], ':active' => '1'));


/* MOVED FROM conteudo_banco */
//-- Adiantamento a Fornecedores
// $query_fornecedores = $connection->prepare("SELECT f.id, f.nome_abrev FROM fornecedor f ORDER BY f.nome_abrev ASC");
// $query_fornecedores->execute();
$query_pais_fornecedor = $connection->prepare("SELECT DISTINCT p.id_pais, p.nome_pais FROM fornecedor f INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais ORDER BY p.nome_pais ASC");
$query_pais_fornecedor->execute();
$pais_fornecedor = $query_pais_fornecedor->fetchAll();

$query_tx_iva = $connection->prepare("SELECT re.id_regra, re.valor FROM regra r LEFT JOIN (SELECT * FROM regra_empresa ORDER BY id_regra DESC, date_reg DESC) AS re ON r.id_regra=re.id_regra WHERE r.nome_regra LIKE 'Taxa de IVA %' AND re.id_empresa=:id_empresa GROUP BY re.id_regra ORDER BY re.valor ASC");
$query_tx_iva->execute(array('id_empresa' => $_SESSION['id_empresa']));
$txs_iva = $query_tx_iva->fetchAll();

//-- Adiantamentos efetuados
$query_carrega_adiant_efet = $connection->prepare("SELECT a.id_adiantamento, f.nome_abrev, a.data_virt, a.valor-a.iva AS valor_s_iva, a.tx_iva, a.iva, a.valor FROM adiantamento a INNER JOIN fornecedor f ON a.id_fornecedor=f.id WHERE a.id_empresa=:id_empresa AND a.pago=0 AND a.id_fornecedor IS NOT NULL ORDER BY f.nome_abrev ASC");
$query_carrega_adiant_efet->execute(array(':id_empresa' => $_SESSION['id_empresa']));
/* */
?>

<div id="f_entregas">
    <div class="linha">
        <div class="left-column">
            <h3>Fazer entregas</h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
        <div class="right-column">&nbsp;</div>
        <?php
            /* if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) { ?>
                <!-- <span><label style="font-size: 8pt;"> Para ativar o JAVA, aceda ao endereço <label style="background-color: #2b6db9; color: white; font-size: 10pt;"><i><b> chrome://flags/#enable-npapi </b></i></label>, ative o plugin sublinhado e reinicie o navegador. <br><br></label></span> -->
				<span><label style="font-size: 10pt; font-weight: bold; color: #e52727"> Este browser já não suporta o JAVA. Algumas entregas podem não ser possiveis de efetuar. <br><br></label></span>
        <?php } */ ?>
    </div>
    <div class="linha">
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcTipoEntrega" name="slcTipoEntrega" size="1" class="select">
                    <option value="0">- Escolha um tipo -</option>
                    <?php while ($linha_tipo = $query_tipo->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value='<?php echo $linha_tipo['id']; ?>'><?php echo $linha_tipo['designacao']; ?></option>
                    <?php } ?>
                </select>
                <span class="width_tmp"></span>
            </div>
        </div>
        <div id="divNIPC">
            <label class="labelNormal" style="margin-right: 2%;">Número de Identificação Fiscal</label>
            <label class="labelNormal" style="margin-right: 0;"><?php echo $linha_dados_empresa['nipc']; ?></label>
        </div>
    </div>
    <div id="divEntDecRet" name="divEntDecRet" data-value="1">
        <div id="frmDecRet" name="frmDecRet" class="form_esq50">
            <div class="linha" style="margin-bottom: 0;">
                <div class="esq60">
                    <label class="labelNormal" style="margin-left: 5%; margin-right: 0;">Número de Identificação Fiscal</label>
                </div>
                <div class="dir40">
                    <label class="labelNormal" style="margin-right: 0;"><?php echo $linha_dados_empresa['nipc']; ?></label>
                </div>
            </div>
            <div class="linha" style="margin-bottom: 0;">
                <div class="esq60">
                    <label class="labelNormal" style="margin-left: 5%; margin-right: 0;">Período a que respeita o imposto</label>
                </div>
                <div class="dir40">
                    <div class="inputarea_col1" style="width: 80px; margin-right: 5%;">
                        <div class="styled-select">
                            <select id="slcAnoRetencao" name="slcAnoRetencao" class="select">
                                <option value="0">- Ano -</option>
                                <?php
                                $ano = $linha_mes_ano['ano'];
                                ?>
                                <option value="1"><?php echo $ano - 2; ?></option>
                                <option value="2"><?php echo $ano - 1; ?></option>
                                <option value="3" selected="selected"><?php echo $ano; ?></option>
                                <option value="4"><?php echo $ano + 1; ?></option>
                                <option value="5"><?php echo $ano + 2; ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="inputarea_col1" style="width: 80px; margin-right: 0;">
                        <div class="styled-select">
                            <select id="slcMesRetencao" name="slcMesRetencao" class="select">
                                <option value="0">- Mês -</option>
                                <?php
                                $meses = array("Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez");
                                for ($i = 1; $i <= 12; $i++) {
                                    $month_name = date('M', mktime(0, 0, 0, $i + 1, 0, 0, 0));
                                    if ($linha_mes_ano['mes'] == $i) {
                                        echo '<option value="' . $i . '" selected="selected">' . $meses[$i - 1] . '</option>';
                                        $mes = $i;
                                    } else {
                                        echo '<option value="' . $i . '">' . $meses[$i - 1] . '</option>';
                                    }
                                }
                                if ($mes == 12) {
                                    $mes = 1;
                                    $ano = $ano + 1;
                                } else {
                                    $mes = $mes + 1;
                                }
                                if ($mes < 10) {
                                    $mes = "0" . $mes;
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha" style="margin-bottom: 0;">
                <div class="esq60">
                    <label class="labelNormal" style="margin-left: 5%; margin-right: 0;">Data limite de pagamento</label>
                </div>
                <div class="dir40">
                    <div class="inputarea_col1" style="width: 80px; margin-right: 0;">
                        <input id="txtDataPagamento" name="txtDataPagamento" type="text" size="8" readonly="readonly" value="<?php echo "20-" . $mes . "-" . $ano; ?>" style="height: 100%; width: 100%; padding-left: 5%;">
                    </div>
                </div>
            </div>
            <div class="linha" style="margin-bottom: 30px;">
                <div class="esq60">
                    <label class="labelNormal" style="margin-left: 5%; margin-right: 0;">Retenções a não residentes</label>
                </div>
                <div class="dir40">
                    <div class="checkbox" style="height: 30px; width: 20%;">
                        <input id="chkResidentes" type="checkbox" name="chkResidentes" class="chk" value="1">
                        <label for="chkResidentes" class="label_chk" style="padding-left: 0; line-height: 2em;">&nbsp;</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha" style="height: 30px; float: right; width: 40%; margin-top: 14%; margin-bottom: 0;">
            <button id="btnRemDecRet" name="btnRemDecRet" class="btnNoIco" style="padding: 7px 20px; height: 30px; float: right;">Apagar</button>
        </div>
        <table id="tblDadosDecRet" name="tblDadosDecRet" data-value="1" class="tabela">
            <tr>
                <td class="td5">#</td>
                <td class="td15">Zona</td>
                <td class="td10">Rubrica</td>
                <td class="td45">Descrição</td>
                <td class="td20">Importância (<?php echo $linha_moeda['simbolo']; ?>)</td>
                <td class="td5" style="background-color: #2b6db9;">
                    <div class="checkbox">
                        <input id="chkAllDecRet" name="chkAllDecRet" type="checkbox" class="chk" value="0">
                        <label for="chkAllDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding: 4px; font-size: 85%;">1</td>
                <td style="padding: 4px;">
                    <div class="inputarea_col1" style="width: 100%; margin-right: 0; height: 20px;">
                        <div class="styled-select" style="background-size: 20px 20px; background-color: #fff;">
                            <select id="slcZona_1" name="slcZona" class="select" style="font-size: 85%;">
                                <option value="0" style="background-color: #fff;"></option>
                                <option value="1" style="background-color: #fff;">Continente</option>
                                <option value="2" style="background-color: #fff;">Açores</option>
                                <option value="3" style="background-color: #fff;">Madeira</option>
                            </select>
                        </div>
                    </div>
                </td>
                <td style="padding: 4px;">
                    <div class="inputarea_col1" style="width: 70%; margin-right: 0; height: 20px;">
                        <div class="styled-select" style="background-size: 20px 20px; background-color: #fff;">
                            <select id="slcRubrica_1" name="slcRubrica" class="select" style="font-size: 85%;" data-id="1">
                                <option value="0" style="background-color: #fff;"></option>
                                <?php while ($linha_rubrica = $query_rubricas->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $linha_rubrica['id']; ?>" style="background-color: #fff;"><?php echo $linha_rubrica['rubrica']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <a id="aVerCodigos" class="body-link" title="Consultar Códigos">[...]</a>
                </td>
                <td style="padding: 4px; font-size: 85%;">
                    <input id="txtDescRetencao_1" name="txtDescRetencao" type="text" size="60" value="" readonly="readonly">
                </td>
                <td style="padding: 4px; font-size: 85%;">
                    <div class="moneyarea_col1" style="height: 22px; width: 100%; background-color: #fff;">
                        <input id="txtImportancia_1" name="txtImportancia" type="text" class="dinheiro">
                        <div class="mnyLabel" style="width: 15px;">
                            <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                        </div>
                    </div>
                </td>
                <td style="background-color: transparent; padding: 4px;">
                    <div class="checkbox">
                        <input id="chkLinhaDecRet_1" name="chkLinhaDecRet" type="checkbox" class="chk" value="1">
                        <label for="chkLinhaDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                        <input id="hddIdLinhaDecRet" name="hddIdLinhaDecRet" type="hidden" value="1">
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding: 4px; font-size: 85%; background-color: transparent;"></td>
                <td style="padding: 4px; font-size: 85%; background-color: transparent;"></td>
                <td style="padding: 4px; font-size: 85%; background-color: transparent;"></td>
                <td style="padding: 4px; font-size: 85%; background-color: transparent;"></td>
                <td style="padding: 4px; font-size: 85%; background-color: transparent; text-align: right;">
                    <a id="aAddLinhaRetencao" class="body-link" title="Adicionar Linhas">[ + linha ]</a>
                </td>
            </tr>
        </table>
        <div class="form_esq100">
            <div class="linha" style="width: 350px; float: right; margin-right: 5%; margin-top: 10px;">
                <div class="esq50">
                    <label for="txtTotalDecRet" class="labelNormal">Valor a pagar (<?php echo $linha_moeda['simbolo']; ?>)</label>
                </div>
                <div class="dir50">
                    <div class="moneyarea_col1" style="width: 170px;">
                        <input id="txtTotalDecRet" name="txtTotalDecRet" type="text" readonly="readonly" class="dinheiro" style="width: 80%;">
                        <div class="mnyLabel">
                            <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnGuardarDecRet" name="btnGuardarDecRet" class="btnNoIco" style="float: none;">Enviar</button>
        </div>
    </div>
    <div id="divEntIVA" name="divEntIVA">
        <div class="form_esq100" style="margin-bottom: 20px;">
            <span><label style="font-size: 10pt; font-weight: bold; color: #e52727"> *Para baixar as aplicações de Declarações (Autoridade Tributária), aceda ao seguinte <a href="http://www.portaldasfinancas.gov.pt/pt/menu.action?pai=348" target="#">endereço</a>.</label></span>
			<!-- <applet id="ivaApplet" codebase="https://www.portaldasfinancas.gov.pt/pf/java/" archive="DPIVA-UI-v2013-3.2.30.0088-applet.jar" code="pt.dgci.taxClient.v2013.iva.app.IVAApplet.class" name="DPIVA" width="100%" height="500" align="middle" title="Java(TM)">
                <param name="progressbar" value="true">
                <param name="OnOffLine" value="ON">
                <param name="Prod" value="true">
                <param name="codebase_lookup" value="false">
                <param name="separate_jvm" value="true">
            </applet> -->
        </div>
        <div class="form_esq100">
            <div class="linha">
                <div class="linha10" style="width: 20%;">
                    <div class="esq30">
                        <label for="txtValorEntIVA" class="labelNormal" style="margin-left: 5%;">Valor</label>
                    </div>
                    <div class="dir70">
                        <div class="moneyarea_col1" style="width: 100%;">
                            <input id="txtValorEntIVA" name="txtValorEntIVA" type="text" class="dinheiro" style="width: 80%;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcMesEntIVA" class="labelNormal">Mês</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcMesEntIVA" name="slcMesEntIVA" class="select">
                                    <option value="1">JANEIRO</option>
                                    <option value="2">FEVEREIRO</option>
                                    <option value="3">MARÇO</option>
                                    <option value="4">ABRIL</option>
                                    <option value="5">MAIO</option>
                                    <option value="6">JUNHO</option>
                                    <option value="7">JULHO</option>
                                    <option value="8">AGOSTO</option>
                                    <option value="9">SETEMBRO</option>
                                    <option value="10">OUTUBRO</option>
                                    <option value="11">NOVEMBRO</option>
                                    <option value="12">DEZEMBRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcAnoEntIVA" class="labelNormal">Ano</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcAnoEntIVA" name="slcAnoEntIVA" size="1" class="select">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label class="labelNormal">Prazo</label>
                    </div>
                    <div class="dir70">
                        <div class="checkbox" style="margin-top: 5px;">
                            <input id="chkFPrazoEntIVA" name="chkFPrazoEntIVA" type="checkbox">
                            <label for="chkFPrazoEntIVA" class="label_chk">Fora de prazo</label>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 16%;">
                    <button id="btnAnexarEntIVA" name="btnAnexarEntIVA" class="btnNoIco" style="padding: 7px 25px; height: 30px; margin-left: 5%;">Anexar</button>
                    <input id="fileAnexarEntIVA" name="fileAnexarEntIVA" type="file">
                </div>
            </div>
            <div class="linha" style="text-align: center; margin-bottom: 0;">
                <button id="btnEnviarEntIVA" name="btnEnviarEntIVA" class="btnNoIco" style="float: none;">Enviar</button>
            </div>
        </div>
    </div>
    <div id="divEntOutrasDec" name="divEntOutrasDec" class="form_esq50" style="float: none; margin: 0 auto;">
        <div class="linha10">
            <div class="esq40">
                <label class="labelNormal" style="margin-left: 5%;">Prazo</label>
            </div>
            <div class="dir60">
                <div class="checkbox">
                    <input id="chkFPrazoEntOutrasDec" name="chkFPrazoEntOutrasDec" type="checkbox">
                    <label for="chkFPrazoEntOutrasDec" class="label_chk">Fora de prazo</label>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq40">
                <label for="txtValorOutrasDec" class="labelNormal" style="margin-left: 5%;">Valor da entrega</label>
            </div>
            <div class="dir60">
                <div class="moneyarea_col1">
                    <input id="txtValorEntOutrasDec" name="txtValorEntOutrasDec" type="text" class="dinheiro">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq40">
                <button id="btnAnexarEntOutrasDec" name="btnAnexarEntOutrasDec" class="btnNoIco" style="padding: 7px 25px; height: 30px; margin-left: 5%;">Anexar</button>
                <input id="fileAnexarEntOutrasDec" name="fileAnexarEntOutrasDec" type="file">
            </div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <input id="txtPathEntOutrasDec" name="txtPath" type="text" readonly="readonly" class="inputNoBackground" style="font-size: 9pt; height: 28px;">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq40">
                <label for="slcMesEntOutrasDec" class="labelNormal" style="margin-left: 5%;">Mês</label>
            </div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcMesEntOutrasDec" name="slcMesEntOutrasDec" size="1" class="select">
                            <option value="1">JANEIRO</option>
                            <option value="2">FEVEREIRO</option>
                            <option value="3">MARÇO</option>
                            <option value="4">ABRIL</option>
                            <option value="5">MAIO</option>
                            <option value="6">JUNHO</option>
                            <option value="7">JULHO</option>
                            <option value="8">AGOSTO</option>
                            <option value="9">SETEMBRO</option>
                            <option value="10">OUTUBRO</option>
                            <option value="11">NOVEMBRO</option>
                            <option value="12">DEZEMBRO</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha">
            <div class="esq40">
                <label for="slcAnoEntOutrasDec" class="labelNormal" style="margin-left: 5%;">Ano</label>
            </div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcAnoEntOutrasDec" name="slcAnoEntOutrasDec" size="1" class="select">
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnEnviarEntOutrasDec" name="btnEnviarEntOutrasDec" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 10px;">Enviar</button>
            <button id="btnLimparEntOutrasDec" name="btnLimparEntOutrasDec" class="btn btn-3 btn-3a icon-clean" style="float: none; margin-top: 10px;">Limpar</button>
        </div>
    </div>
    <div id="divEntModelo10" name="divEntModelo10">
        <div class="form_esq100" style="margin-bottom: 20px;">
            <span><label style="font-size: 10pt; font-weight: bold; color: #e52727"> *Para baixar as aplicações de Declarações (Autoridade Tributária), aceda ao seguinte <a href="http://www.portaldasfinancas.gov.pt/pt/menu.action?pai=348" target="#">endereço</a>.</label></span>
			<!-- <applet codebase="https://www.portaldasfinancas.gov.pt/de/java/" archive="modelo10v2011-applet-5.3.5.jar,plastic-1.2.0.jar" code="pt.dgci.taxClient.v2011.modelo10.app.Modelo10Applet.class" name="M10" width="100%" height="500" align="center">
                <param name="progressbar" value="true">
                <param name="Applet" value="ON">
                <param name="Nif" value="<?php echo $linha_dados_empresa['nipc']; ?>">
                <param name="Nome" value="<?php echo $linha_dados_empresa['nome']; ?>">
                <param name="RepFin" value="0485">
                <param name="Exerc" value="<?php echo $linha_mes_ano['ano'] - 1; ?>">
                <param name="ReadWrite" value="Y">
            </applet> -->
        </div>
        <div class="form_esq100">
            <div class="linha">
                <div class="linha10" style="width: 20%;">
                    <div class="esq30">
                        <label for="txtValorEntModelo10" class="labelNormal" style="margin-left: 5%;">Valor</label>
                    </div>
                    <div class="dir70">
                        <div class="moneyarea_col1" style="width: 100%;">
                            <input id="txtValorEntModelo10" name="txtValorEntModelo10" type="text" class="dinheiro" style="width: 80%;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcMesEntModelo10" class="labelNormal">Mês</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcMesEntModelo10" name="slcMesEntModelo10" class="select">
                                    <option value="1">JANEIRO</option>
                                    <option value="2">FEVEREIRO</option>
                                    <option value="3">MARÇO</option>
                                    <option value="4">ABRIL</option>
                                    <option value="5">MAIO</option>
                                    <option value="6">JUNHO</option>
                                    <option value="7">JULHO</option>
                                    <option value="8">AGOSTO</option>
                                    <option value="9">SETEMBRO</option>
                                    <option value="10">OUTUBRO</option>
                                    <option value="11">NOVEMBRO</option>
                                    <option value="12">DEZEMBRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcAnoEntModelo10" class="labelNormal">Ano</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcAnoEntModelo10" name="slcAnoEntModelo10" size="1" class="select">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label class="labelNormal">Prazo</label>
                    </div>
                    <div class="dir70">
                        <div class="checkbox" style="margin-top: 5px;">
                            <input id="chkFPrazoEntModelo10" name="chkFPrazoEntModelo10" type="checkbox">
                            <label for="chkFPrazoEntModelo10" class="label_chk">Fora de prazo</label>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 16%;">
                    <button id="btnAnexarEntModelo10" name="btnAnexarEntModelo10" class="btnNoIco" style="padding: 7px 25px; height: 30px; margin-left: 5%;">Anexar</button>
                    <input id="fileAnexarEntModelo10" name="fileAnexarEntModelo10" type="file">
                </div>
            </div>
            <div class="linha" style="text-align: center; margin-bottom: 0;">
                <button id="btnEnviarEntModelo10" name="btnEnviarEntModelo10" class="btnNoIco" style="float: none;">Enviar</button>
            </div>
        </div>
    </div>
    <div id="divEntIRC" name="divEntIRC">
        <div class="form_esq100" style="margin-bottom: 20px;">
            <span><label style="font-size: 10pt; font-weight: bold; color: #e52727"> *Para baixar as aplicações de Declarações (Autoridade Tributária), aceda ao seguinte <a href="http://www.portaldasfinancas.gov.pt/pt/menu.action?pai=348" target="#">endereço</a>.</label></span>
			<!-- <applet codebase="https://www.portaldasfinancas.gov.pt/de/java/" archive="dav2006-applet-2.7.0.jar,plastic-1.2.0.jar" code="pt.dgci.taxClient.v2006.da.app.DAApplet.class" name="DA" width="100%" height="500" align="center">
                <param name="progressbar" value="true">
                <param name="Applet" value="ON">
                <param name="Nif" value="<?php echo $linha_dados_empresa['nipc']; ?>">
                <param name="Nome" value="<?php echo $linha_dados_empresa['nome']; ?>">
                <param name="RepFin" value="0485">
                <param name="Exerc" value="2005">
                <param name="ReadWrite" value="Y">
            </applet> -->
        </div>
        <div class="form_esq100">
            <div class="linha">
                <div class="linha10" style="width: 20%;">
                    <div class="esq30">
                        <label for="txtValorEntIRC" class="labelNormal" style="margin-left: 5%;">Valor</label>
                    </div>
                    <div class="dir70">
                        <div class="moneyarea_col1" style="width: 100%;">
                            <input id="txtValorEntIRC" name="txtValorEntIRC" type="text" class="dinheiro" style="width: 80%;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcMesEntIRC" class="labelNormal">Mês</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcMesEntIRC" name="slcMesEntIRC" class="select">
                                    <option value="1">JANEIRO</option>
                                    <option value="2">FEVEREIRO</option>
                                    <option value="3">MARÇO</option>
                                    <option value="4">ABRIL</option>
                                    <option value="5">MAIO</option>
                                    <option value="6">JUNHO</option>
                                    <option value="7">JULHO</option>
                                    <option value="8">AGOSTO</option>
                                    <option value="9">SETEMBRO</option>
                                    <option value="10">OUTUBRO</option>
                                    <option value="11">NOVEMBRO</option>
                                    <option value="12">DEZEMBRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcAnoEntIRC" class="labelNormal">Ano</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcAnoEntIRC" name="slcAnoEntIRC" size="1" class="select">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label class="labelNormal">Prazo</label>
                    </div>
                    <div class="dir70">
                        <div class="checkbox" style="margin-top: 5px;">
                            <input id="chkFPrazoEntIRC" name="chkFPrazoEntIRC" type="checkbox">
                            <label for="chkFPrazoEntIRC" class="label_chk">Fora de prazo</label>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 16%;">
                    <button id="btnAnexarEntIRC" name="btnAnexarEntIRC" class="btnNoIco" style="padding: 7px 25px; height: 30px; margin-left: 5%;">Anexar</button>
                    <input id="fileAnexarEntIRC" name="fileAnexarEntIRC" type="file">
                </div>
            </div>
            <div class="linha" style="text-align: center; margin-bottom: 0;">
                <button id="btnEnviarEntIRC" name="btnEnviarEntIRC" class="btnNoIco" style="float: none;">Enviar</button>
            </div>
        </div>
    </div>
    <div id="divEntRecap" name="divEntRecap">
        <div class="form_esq100" style="margin-bottom: 20px;">
            <span><label style="font-size: 10pt; font-weight: bold; color: #e52727"> *Para baixar as aplicações de Declarações (Autoridade Tributária), aceda ao seguinte <a href="http://www.portaldasfinancas.gov.pt/pt/menu.action?pai=348" target="#">endereço</a>.</label></span>
			<!-- <applet codebase="https://www.portaldasfinancas.gov.pt/de/java/" archive="declRecapitulativaV2010-applet.jar,/de/java/plastic-1.2.0.jar" code="pt.dgci.taxClient.v2010.declRecapitulativa.app.DeclRecapitulativaApplet.class" name="DR" width="100%" height="500" align="center">
                <param name="progressbar" value="true">
                <param name="OnOffline" value="ON">
                <param name="Nif" value="<?php echo $linha_dados_empresa['nipc']; ?>">
                <param name="Periodo" value="<?php echo $linha_mes_ano['ano'] . $mes_app; ?> ">
                <param name="DataPresente" value="<?php echo $linha_mes_ano['ano'] . str_pad(($mes_app + 1), 2, '0', STR_PAD_LEFT) . $dia_app; ?>">
                <param name="TOC" value="">
                <param name="ContOrg" value="N">
                <param name="Versao" value="02">
            </applet> -->
        </div>
        <div class="form_esq100">
            <div class="linha">
                <div class="linha10" style="width: 20%;">
                    <div class="esq30">
                        <label for="txtValorEntRecap" class="labelNormal" style="margin-left: 5%;">Valor</label>
                    </div>
                    <div class="dir70">
                        <div class="moneyarea_col1" style="width: 100%;">
                            <input id="txtValorEntRecap" name="txtValorEntRecap" type="text" class="dinheiro" style="width: 80%;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcMesEntRecap" class="labelNormal">Mês</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcMesEntRecap" name="slcMesEntRecap" class="select">
                                    <option value="1">JANEIRO</option>
                                    <option value="2">FEVEREIRO</option>
                                    <option value="3">MARÇO</option>
                                    <option value="4">ABRIL</option>
                                    <option value="5">MAIO</option>
                                    <option value="6">JUNHO</option>
                                    <option value="7">JULHO</option>
                                    <option value="8">AGOSTO</option>
                                    <option value="9">SETEMBRO</option>
                                    <option value="10">OUTUBRO</option>
                                    <option value="11">NOVEMBRO</option>
                                    <option value="12">DEZEMBRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcAnoEntRecap" class="labelNormal">Ano</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcAnoEntRecap" name="slcAnoEntRecap" size="1" class="select">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label class="labelNormal">Prazo</label>
                    </div>
                    <div class="dir70">
                        <div class="checkbox" style="margin-top: 5px;">
                            <input id="chkFPrazoEntRecap" name="chkFPrazoEntRecap" type="checkbox">
                            <label for="chkFPrazoEntRecap" class="label_chk">Fora de prazo</label>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 16%;">
                    <button id="btnAnexarEntRecap" name="btnAnexarEntRecap" class="btnNoIco" style="padding: 7px 25px; height: 30px; margin-left: 5%;">Anexar</button>
                    <input id="fileAnexarEntRecap" name="fileAnexarEntRecap" type="file">
                </div>
            </div>
            <div class="linha" style="text-align: center; margin-bottom: 0;">
                <button id="btnEnviarEntRecap" name="btnEnviarEntRecap" class="btnNoIco" style="float: none;">Enviar</button>
            </div>
        </div>
    </div>
    <div id="divEntModelo25" name="divEntModelo25">
        <div class="form_esq100" style="margin-bottom: 20px;">
		<span><label style="font-size: 10pt; font-weight: bold; color: #e52727"> *Para baixar as aplicações de Declarações (Autoridade Tributária), aceda ao seguinte <a href="http://www.portaldasfinancas.gov.pt/pt/menu.action?pai=348" target="#">endereço</a>.</label></span>
            <!-- <applet codebase="https://www.portaldasfinancas.gov.pt/de/java/" archive="modelo25v2008-applet-1.13.8.jar,plastic-1.2.0.jar" code="pt.dgci.taxClient.v2008.modelo25.app.Modelo25Applet.class" name="Modelo25" width="100%" height="500" align="center">
                <param name="progressbar" value="true">
                <param name="nome" value="<?php echo $linha_dados_empresa['nome']; ?>">
                <param name="nif" value="<?php echo $linha_dados_empresa['nipc']; ?>">
                <param name="ano" value="<?php echo $linha_mes_ano['ano'] - 1; ?>">
                <param name="repfin" value="0485">
            </applet> -->
        </div>
        <div class="form_esq100">
            <div class="linha">
                <div class="linha10" style="width: 20%;">
                    <div class="esq30">
                        <label for="txtValorEntModelo25" class="labelNormal" style="margin-left: 5%;">Valor</label>
                    </div>
                    <div class="dir70">
                        <div class="moneyarea_col1" style="width: 100%;">
                            <input id="txtValorEntModelo25" name="txtValorEntModelo25" type="text" class="dinheiro" style="width: 80%;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcMesEntModelo25" class="labelNormal">Mês</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcMesEntModelo25" name="slcMesEntModelo25" class="select">
                                    <option value="1">JANEIRO</option>
                                    <option value="2">FEVEREIRO</option>
                                    <option value="3">MARÇO</option>
                                    <option value="4">ABRIL</option>
                                    <option value="5">MAIO</option>
                                    <option value="6">JUNHO</option>
                                    <option value="7">JULHO</option>
                                    <option value="8">AGOSTO</option>
                                    <option value="9">SETEMBRO</option>
                                    <option value="10">OUTUBRO</option>
                                    <option value="11">NOVEMBRO</option>
                                    <option value="12">DEZEMBRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcAnoEntModelo25" class="labelNormal">Ano</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcAnoEntModelo25" name="slcAnoEntModelo25" size="1" class="select">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label class="labelNormal">Prazo</label>
                    </div>
                    <div class="dir70">
                        <div class="checkbox" style="margin-top: 5px;">
                            <input id="chkFPrazoEntModelo25" name="chkFPrazoEntModelo25" type="checkbox">
                            <label for="chkFPrazoEntModelo25" class="label_chk">Fora de prazo</label>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 16%;">
                    <button id="btnAnexarEntModelo25" name="btnAnexarEntModelo25" class="btnNoIco" style="padding: 7px 25px; height: 30px; margin-left: 5%;">Anexar</button>
                    <input id="fileAnexarEntModelo25" name="fileAnexarEntModelo25" type="file">
                </div>
            </div>
            <div class="linha" style="text-align: center; margin-bottom: 0;">
                <button id="btnEnviarEntModelo25" name="btnEnviarEntModelo25" class="btnNoIco" style="float: none;">Enviar</button>
            </div>
        </div>
    </div>
    <div id="divEntIES" name="divEntIES">
        <div class="form_esq100" style="margin-bottom: 20px;">
            <span><label style="font-size: 10pt; font-weight: bold; color: #e52727"> *Para baixar as aplicações de Declarações (Autoridade Tributária), aceda ao seguinte <a href="http://www.portaldasfinancas.gov.pt/pt/menu.action?pai=348" target="#">endereço</a>.</label></span>
			<!-- <applet id="iesApplet" codebase="https://www.portaldasfinancas.gov.pt/de/java/" archive="iesv2015-appletV2015.1.12.0143.jar,plastic-1.2.0.jar,xercesImpl.jar" code="pt.dgci.taxClient.v2015.ies.app.IESApplet.class" name="IES" width="100%" height="500" align="middle">
                <param name="progressbar" value="true">
                <param name="Applet" value="ON">
                <param name="java_arguments" value="-Xms256m -Xmx512m -Xss1m">
                <param name="ReadWrite" value="Y">
                <param name="appletOffline" value="true">
            </applet> -->
        </div>
        <div class="form_esq100">
            <div class="linha">
                <div class="linha10" style="width: 20%;">
                    <div class="esq30">
                        <label for="txtValorEntIES" class="labelNormal" style="margin-left: 5%;">Valor</label>
                    </div>
                    <div class="dir70">
                        <div class="moneyarea_col1" style="width: 100%;">
                            <input id="txtValorEntIES" name="txtValorEntIES" type="text" class="dinheiro" style="width: 80%;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcMesEntIES" class="labelNormal">Mês</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcMesEntIES" name="slcMesEntIES" class="select">
                                    <option value="1">JANEIRO</option>
                                    <option value="2">FEVEREIRO</option>
                                    <option value="3">MARÇO</option>
                                    <option value="4">ABRIL</option>
                                    <option value="5">MAIO</option>
                                    <option value="6">JUNHO</option>
                                    <option value="7">JULHO</option>
                                    <option value="8">AGOSTO</option>
                                    <option value="9">SETEMBRO</option>
                                    <option value="10">OUTUBRO</option>
                                    <option value="11">NOVEMBRO</option>
                                    <option value="12">DEZEMBRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label for="slcAnoEntIES" class="labelNormal">Ano</label>
                    </div>
                    <div class="dir70">
                        <div class="inputarea_col1" style="width: 100%; margin-right: 0;">
                            <div class="styled-select">
                                <select id="slcAnoEntIES" name="slcAnoEntIES" size="1" class="select">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10" style="width: 20%; margin-left: 1%;">
                    <div class="esq30">
                        <label class="labelNormal">Prazo</label>
                    </div>
                    <div class="dir70">
                        <div class="checkbox" style="margin-top: 5px;">
                            <input id="chkFPrazoEntIES" name="chkFPrazoEntIES" type="checkbox">
                            <label for="chkFPrazoEntIES" class="label_chk">Fora de prazo</label>
                        </div>
                    </div>
                </div>
                <!-- <div class="linha10" style="width: 16%;">
                    <button id="btnAnexarEntIES" name="btnAnexarEntIES" class="btnNoIco" style="padding: 7px 25px; height: 30px; margin-left: 5%;">Anexar</button>
                    <input id="fileAnexarEntIES" name="fileAnexarEntIES" type="file">
                </div> -->
                <div class="linha">&nbsp;</div>
                <div class="linha10">
                    <button id="btnAnexarEntIES" name="btnAnexarEntIES" class="btn btn-3 btn-3a icon-anexo" style="float: left;">Anexar</button>
                    <input id="fileAnexarEntIES" name="fileAnexarEntIES" type="file">
                    <div class="inputarea_38">
                        <input id="txtPathEntIES" name="txtPathEntIES" type="text" readonly="readonly" class="inputNoBackground" style="font-size: 9pt;">
                    </div>
                </div>
            </div>
            <div class="linha" style="text-align: center; margin-bottom: 0;">
                <button id="btnEnviarEntIES" name="btnEnviarEntIES" class="btnNoIco" style="float: none;">Enviar</button>
            </div>
        </div>
    </div>
</div>
<div id="ver_encomendas">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['V_ORDER']; ?></h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
        <div class="right-column">&nbsp;</div>
    </div>
    <div class="linha">
        <div class="loading" style="width: 150px;">
            <div class="linha10">
                <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
            </div>
            <div class="linha" style="text-align: center;">A carregar...</div>
        </div>
    </div>
    <div class="linha">
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcFiltrarEnc" name="slcFiltrarEnc" size="1" class="select">
                    <option selected="selected" value="0">- Filtrar por -</option>
                    <?php foreach ($arr_fornecedores as $linha_fornecedor) { ?>
                        <option value="<?php echo $linha_fornecedor['id']; ?>"><?php echo $linha_fornecedor['nome_abrev']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    <table id="tblEncomendas" name="tblEncomendas" class="tabela">
        <tr>
            <td class="td25">Nº</td>
            <td class="td10">Data</td>
            <td class="td5">Pago</td>
            <td class="td20">Fornecedor</td>
            <td class="td20">País</td>
            <td class="td25">Valor</td>
            <td class="td5" style="background-color: transparent;">&nbsp;</td>
        </tr>
        <?php while ($linha_encomendas = $query_encomendas->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td style="padding: 4px;"><?php echo $linha_encomendas['ref']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_encomendas['data']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_encomendas['pago']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_encomendas['nome_abrev']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_encomendas['nome_pais']; ?></td>
                <td style="padding: 4px;"><?php echo number_format($linha_encomendas['total'], 2, ',', '.'); ?> <?php echo $linha_encomendas['simbolo']; ?></td>
                <td style="background-color: transparent; padding: 0; cursor: pointer;">
                    <a href="./impressao/fatura.php?id=<?php echo $linha_encomendas['id']; ?>&p=<?php echo $linha_encomendas['abrev_pais'] ?>" target="_blank">
                        <img width="33" height="33" src="images/adobe_logo.png">
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblEncVazia" name="tblEncVazia" class="tabela">
        <tr>
            <td>Não existem encomendas</td>
        </tr>
    </table>
</div>
<div id="ver_entregas">
    <div class="linha">
        <div class="left-column">
            <h3>Ver entregas</h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
        <div class="right-column">&nbsp;</div>
    </div>
    <div id="radVOthersGroup" class="linha" style="height: 30px; text-align: center;">
        <div class="radio">
            <input id="radVOutros" name="outros" type="radio" value="1" checked="checked">
            <label for="radVOutros" class="btnRadio">Outros</label>
            <input id="radVDecRet" name="outros" type="radio" value="2">
            <label for="radVDecRet" class="btnRadio">Declarações de retenções</label>
        </div>
    </div>
    <table id="tblVDiversos" name="tblVDiversos" data-value="1" class="tabela">
        <tr>
            <td class="td10">Data</td>
            <td class="td10">Valor</td>
            <td class="td10">F. prazo</td>
            <td class="td10">Pago</td>
            <!-- <td class="td35">Tipo</td> -->
            <td class="td30">Tipo</td>
            <td class="td10">Mês</td>
            <td class="td10">Ano</td>
            <!-- <td class="td5" style="background-color: transparent;">&nbsp;</td> -->
            <td class="td5">Anexo</td>
            <td class="td5">Guia</td>
        </tr>
        <?php while ($linha_entregas = $query_entregas->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td style="padding: 4px;"><?php echo $linha_entregas['data']; ?></td>
                <td style="padding: 4px;"><?php echo number_format($linha_entregas['valor'], 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_entregas['prazo']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_entregas['pago']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_entregas['designacao']; ?></td>
                <td style="padding: 4px;"><?php echo conv_mes($linha_entregas['mes']); ?></td>
                <td style="padding: 4px;"><?php echo $linha_entregas['ano']; ?></td>
                <td style="background-color: transparent; padding: 0; cursor: pointer;">
                    <a href="<?php echo $linha_entregas['ficheiro']; ?>" target="_blank">
                        <img width="28" height="28" src="images/adobe_logo.png">
                    </a>
                </td>
				<td style="background-color: transparent; padding: 0; cursor: pointer;">
                    <?php if ($linha_entregas['designacao'] == 'Fundo de Compensação do Trabalho') { ?>
                        <a href="./impressao/fct.php?id=<?php echo $linha_entregas['id']; ?>" target="_blank">
                            <img width="28" height="28" src="images/adobe_logo.png">
                        </a>
                    <?php } else if ($linha_entregas['designacao'] == 'Declaração de Remunerações (AT)') { ?>
                        <a href="./impressao/irs_dec_remuner.php?id=<?php echo $linha_entregas['id']; ?>" target="_blank">
                            <img width="28" height="28" src="images/adobe_logo.png">
                        </a>
                    <?php } else if ($linha_entregas['designacao'] == 'Declaração de Remunerações (SS)') { ?>
                        <a href="./impressao/dec_remuner.php?id=<?php echo $linha_entregas['id']; ?>" target="_blank">
                            <img width="28" height="28" src="images/adobe_logo.png">
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblVDiversosVazia" name="tblVDiversosVazia" class="tabela">
        <tr>
            <td>Não existem entregas</td>
        </tr>
    </table>
    <table id="tblVDecRet" name="tblVDecRet" data-value="0" class="tabela">
        <tr>
            <td class="td25">Data</td>
            <td class="td15">Residentes</td>
            <td class="td10">Pago</td>
            <td class="td45">Total</td>
            <td class="td5" style="background-color: transparent;"></td>
        </tr>
    </table>
    <table id="tblVDecRetVazia" name="tblVDecRetVazia" class="tabela">
        <tr>
            <td>Não existem entregas</td>
        </tr>
    </table>
    <div class="linha">
        <div class="linha10">
            <label id="lblDecRet" name="lblDecRet" style="font-size: 18px; color: #2b6db9; font-style: italic;">Declaração de retenções</label>
        </div>
        <table id="tblEntDecRetDetalhes" name="tblEntDecRetDetalhes" class="tabela form_esq50">
            <tr>
                <td class="td10">Rubrica</td>
                <td class="td15">Zona</td>
                <td class="td25">Valor</td>
            </tr>
        </table>
        <div id="frmVDecRet" name="frmVDecRet" class="form_esq35">
            <div class="linha10">
                <div class="esq30" style="padding-left: 5%;">
                    <label for="txtDataLimDecRet" class="labelNormal">Data</label>
                </div>
                <div class="dir65">
                    <div class="inputarea_col1">
                        <input name="txtDataLimDecRet" type="text" readonly="readonly" value="">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq30" style="padding-left: 5%;">
                    <label for="txtResidentes" class="labelNormal">Residentes</label>
                </div>
                <div class="dir65">
                    <div class="inputarea_col1">
                        <input name="txtResidentes" type="text" readonly="readonly" value="">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq30" style="padding-left: 5%;">
                    <label for="txtPago" class="labelNormal">Pago</label>
                </div>
                <div class="dir65">
                    <div class="inputarea_col1">
                        <input name="txtPago" type="text" readonly="readonly" value="">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq30" style="padding-left: 5%;">
                    <label for="txtTotal" class="labelNormal">Total</label>
                </div>
                <div class="dir65">
                    <div class="moneyarea_col1">
                        <input name="txtTotal" type="text" readonly="readonly" value="">
                        <div class="mnyLabel">
                            <input name="txtMoeda" type="text" readonly="readonly" value="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq30" style="padding-left: 5%;">
                    <label for="txtDocDecRet" class="labelNormal">Documento</label>
                </div>
                <div class="dir65">
                    <a href="" target="_blank">
                        <img name="txtDocDecRet" width="30" height="30" src="images/adobe_logo.png">
                    </a>
                </div>
            </div>
        </div>
        <div class="linha" style="width: 15%;">
            <button id="btnVoltarDecRet" name="btnVoltarDecRet" class="btnNoIco" style="float: right; margin-right: 5%;">Voltar</button>
        </div>
    </div>
</div>
<div id="ins_fat">
    <div class="linha">
        <div class="left-column">
            <h3>Registar Fatura</h3>
        </div>
        <div class="center-right-column">
            <div class="error"></div>
        </div>
    </div>
    <div id="divVerFat" name="divVerFat" class="form_esq50">
        <?php
        $query_faturas = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $query_faturas->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        ?>
        <table id="tblFaturas" name="tblFaturas" class="tabela">
            <tr>
                <td class="td25" style="padding: 5px;">Nº fatura</td>
                <td class="td45" style="padding: 5px;">Cliente</td>
                <td class="td25" style="padding: 5px;">Valor</td>
                <td class="td5" style="background-color: transparent;"></td>
            </tr>
            <?php while ($linha_fatura = $query_faturas->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="padding: 2px;"><?php echo $linha_fatura['num_fatura']; ?></td>
                    <td style="padding: 2px;"><?php echo $linha_fatura['cliente']; ?></td>
                    <td style="padding: 2px;"><?php echo number_format($linha_fatura['valor'], 2, ',', '.'); ?></td>
                    <td style="background-color: #77a4d7; padding: 0.1px; cursor: pointer;">
                        <input name="hddIdFatura" type="hidden" value="<?php echo $linha_fatura['id_fatura']; ?>">
                        <div id="btnVerFatura_<?php echo $linha_fatura['id_fatura']; ?>" name="btnVerFatura" class="labelicon icon-info"></div>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblFaturasVazia" name="tblFaturasVazia" class="tabela">
            <tr>
                <td>Não existem faturas registadas</td>
            </tr>
        </table>
        <div id="divFaturaDetail" name="divFaturaDetail" class="form_esq100">
            <div class="linha10">
                <button id="btnVoltarFat" name="btnVoltarFat" class="btnNoIco voltarDir" value="">Voltar</button>
            </div>
            <div class="linha10">
                <div class="esq50">
                    <label for="txtNFat" class="labelNormal"><?php echo $lingua['BILL_NUMBER']; ?></label>
                </div>
                <div class="dir50">
                    <div class="inputarea_col1">
                        <input id="txtNFat" name="txtNFat" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50">
                    <label for="txtClient" class="labelNormal"><?php echo $lingua['CLIENT']; ?></label>
                </div>
                <div class="dir50">
                    <div class="inputarea_col1">
                        <input id="txtClient" name="txtClient" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50">
                    <label for="txtValFat" class="labelNormal"><?php echo $lingua['TOTAL_VAL']; ?></label>
                </div>
                <div class="dir50">
                    <div class="moneyarea_col1">
                        <input id="txtValFat" name="txtValFat" type="text" readonly="readonly">
                        <div class="mnyLabel">
                            <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fatura['simbolo']; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50">
                    <label for="txtDataVirtFat" class="labelNormal"><?php echo $lingua['BILL_DATE']; ?></label>
                </div>
                <div class="dir50">
                    <div class="inputarea_col1">
                        <input id="txtDataVirtFat" name="txtDataVirtFat" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50">
                    <label for="txtDataPagFat" class="labelNormal">Data de pagamento</label>
                </div>
                <div class="dir50">
                    <div class="inputarea_col1">
                        <input id="txtDataPagFat" name="txtDataPagFat" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50">
                    <label for="chkFatPaga" class="labelNormal">Pago</label>
                </div>
                <div class="esq50">
                    <div class="checkbox">
                        <input id="chkFatPaga" name="chkFatPaga" type="checkbox" class="chk">
                        <label for="chkFatPaga" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="divRegistarFat" name="divRegistarFat" class="form_esq48" style="margin-left: 2%;">
        <div class="linha">
            <div class="esq50">
                <label for="txtPlafond" class="labelNormal">Plafond disponível</label>
            </div>
            <div class="dir50">
                <div class="moneyarea_col1">
                    <input id="txtPlafond" name="txtPlafond" type="text" value="<?php echo number_format($linha_plafond_fatura['valor'], 2, ',', '.'); ?>" readonly="readonly">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fatura['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50">
                <label for="txtNumFatura" class="labelNormal"><?php echo $lingua['BILL_NUMBER']; ?></label>
            </div>
            <div class="dir50">
                <div class="inputarea_col1">
                    <input id="txtNumFatura" name="txtNumFatura" type="text" maxlength="10">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50">
                <label for="txtCliente" class="labelNormal"><?php echo $lingua['CLIENT']; ?></label>
            </div>
            <div class="dir50">
                <div class="inputarea_col1">
                    <input id="txtCliente" name="txtCliente" type="text">
                </div>
            </div>
        </div>
        <!--
        <div class="linha10">
            <div class="esq50">
                <label for="txtDataVirtualFatura" class="labelNormal"><?php echo $lingua['BILL_DATE']; ?></label>
            </div>
            <div class="dir50">
                <div class="inputarea_col1">
                    <input id="txtDataVirtualFatura" name="txtDataVirtualFatura" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50">
                <label for="txtDataVencFatura" class="labelNormal">Data de vencimento da fatura</label>
            </div>
            <div class="dir50">
                <div class="inputarea_col1">
                    <input id="txtDataVencFatura" name="txtDataVencFatura" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        -->
        <div class="linha10">
            <div class="esq50">
                <label for="txtDataVirtualFatura" class="labelNormal"><?php echo $lingua['BILL_DATE']; ?></label>
            </div>
            <div class="dir50 inputData" style="width: 34%; margin: 0; padding: 0;">
                <!-- <input id="txtDataVirtualFatura" name="txtDataVirtualFatura" type="text" readonly="readonly" class="campoData" style="height: 28px;"> -->
                <input id="txtDataVirtualFatura" name="txtDataVirtualFatura" type="text" class="campoData" style="height: 28px;">
                <div class="icon-cal" style="width: 16.5%;"></div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50">
                <label for="txtDataVencFatura" class="labelNormal">Data de vencimento da fatura</label>
            </div>
            <div class="dir50 inputData" style="width: 34%; margin: 0; padding: 0;">
                <!-- <input id="txtDataVencFatura" name="txtDataVencFatura" type="text" readonly="readonly" class="campoData" style="height: 28px;"> -->
                <input id="txtDataVencFatura" name="txtDataVencFatura" type="text" class="campoData" style="height: 28px;">
                <div class="icon-cal" style="width: 16.5%;"></div>
            </div>
        </div>
        
        <!-- Alterações: Adiantamentos -->
        <div class="linha">
            <div class="esq50">
                <label class="labelNormal">Recebeu adiantado?</label>
            </div>
            <div class="dir50">
                <div class="checkbox">
                    <input id="chkAdiantamentoFatura" name="chkAdiantamentoFatura" type="checkbox" class="chk" value="0">
                    <label for="chkAdiantamentoFatura" class="label_chk">&nbsp;</label>
                </div>
            </div>
        </div>
        
        <div id="adiantamentoFat">
            <div class="linha">
                <div class="esq50">
                    <label for="slcAdiantamentoFatura" class="labelNormal">Adiantamento</label>
                </div>
                <div class="dir50">
                    <div class="inputarea_col1">
                        <div class="styled-select">
                            <select id="slcAdiantamentoFatura" name="slcAdiantamentoFatura" size="1" class="select">
                                <option value="0" selected="selected">- Escolha o adiantamento -</option>
                                <?php while ($linha_adiant_fat = $query_adiant_fat->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $linha_adiant_fat['id_adiantamento']; ?>"><?php echo "Adiantamento de " .$linha_adiant_fat['nome_cliente']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="linha">
                <div class="esq50">
                    <label for="txtValorAdiantamentoFat" class="labelNormal">Valor adiantamento</label>
                </div>
                <div class="dir50">
                    <div class="moneyarea_col1">
                        <input id="txtValorAdiantamentoFat" name="txtValorAdiantamentoFat" type="text" readonly="readonly">
                        <div class="mnyLabel">
                            <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fatura['simbolo']; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- -->
        
        <div class="linha">
            <div class="esq50">
                <label for="txtValorFatura" class="labelNormal"><?php echo $lingua['TOTAL_VAL']; ?></label>
            </div>
            <div class="dir50">
                <div class="moneyarea_col1">
                    <input id="txtValorFatura" name="txtValorFatura" type="text" class="dinheiro">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fatura['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnRegFat" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 10px;"><?php echo $lingua['REGISTER']; ?></button>
            <button id="btnLimparFatura" class="btn btn-3 btn-3a icon-clean" style="float: none; margin-top: 10px;"><?php echo $lingua['CLEAR']; ?></button>
        </div>
    </div>
</div>
<div id="calend">
    <div id="relogio"></div>
    <div id="relogio_int"></div>
    <div id="calendario">
        <div id="calendar"></div>
    </div>
</div>
<div id="mod_pass">
    <div class="linha">
        <div class="left-column">
            <h3>Modificar palavra-passe</h3>
        </div>
        <div class="center-right-column">
            <div class="error"></div>
        </div>
    </div>
    <div class="linha">
        <div class="loading" style="width: 150px;">
            <div class="linha10">
                <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
            </div>
            <div class="linha" style="text-align: center;">A carregar...</div>
        </div>
    </div>
    <div id="divMudarPass" class="form_esq60">
        <div class="linha">
            <div class="esq40">
                <label for="txtPassOld" class="labelNormal">Palavra-passe antiga</label>
            </div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <input id="txtPassOld" name="txtPassOld" type="password">
                </div>
            </div>
        </div>
        <div class="linha">
            <div class="esq40">
                <label for="txtPassNew" class="labelNormal">Palavra-passe nova</label>
            </div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <input id="txtPassNew" name="txtPassNew" type="password">
                </div>
            </div>
        </div>
        <div class="linha">
            <div class="esq40">
                <label for="txtPassRep" class="labelNormal">Confirmação da palavra-passe</label>
            </div>
            <div class="inputarea_col1">
                <input id="txtPassRep" name="txtPassRep" type="password">
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnModify" name="btnModify" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 10px;"><?php echo $lingua['SAVE'] ?></button>
        </div>
    </div>
</div>
<div id="email" data-value="1">
    <div class="linha">
        <div class="loading" style="width: 150px;">
            <div class="linha10">
                <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
            </div>
            <div class="linha" style="text-align: center;">A carregar...</div>
        </div>
    </div>
    <input id="hddHistorical_flag" name="hddHistorical_flag" type="hidden" value="1">
    <input id="hddTipoUser" name="hddTipoUser" type="hidden" value="<?php echo $_SESSION['tipo']; ?>">
    <div id="divTitulo" class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['EML']; ?></h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
        <div class="right-column">&nbsp;</div>
    </div>
    <div id="divCabecalho" name="divCabecalho">
        <div class="linha">
            <button id="btn_CaixaEntrada" name="btn_CaixaEntrada" class="btnNoIcoActive"><?php echo $lingua['CX_E']; ?></button>
            <button id="btn_Enviados" name="btn_Enviados" class="btnNoIco"><?php echo $lingua['SNDD']; ?></button>
            <button id="btn_Eliminados" name="btn_Eliminados" class="btnNoIco"><?php echo $lingua['DLTD']; ?></button>
        </div>
        <div class="linha">
            <button id="btnNovaMsg" name="btnNovaMsg" class="btnNoIco" data-valor="1"><?php echo $lingua['NEW']; ?></button>
            <button id="btnDelEmail" name="btnDelEmail" class="botaoG icon-garbage" data-valor="1"></button>
            <div class="inputarea_col1" style="height: 38px; margin: 0 2px 0 2px;">
                <div class="styled-select" style="background-color: #77a4d7;">
                    <select id="slcFiltrarEmails" name="slcFiltrarEmails" size="1" class="select" style="color: #eaedf1; font-size: 14px;" data-flag="1">
                        <option selected="selected" value="0" style="background-color: #77a4d7; color: #eaedf1;"><?php echo $lingua['ORD_B']; ?></option>
                        <option value="1" style="background-color: #77a4d7; color: #eaedf1;"><?php echo ucfirst($lingua['FROM']); ?></option>
                        <option value="2" style="background-color: #77a4d7; color: #eaedf1;"><?php echo $lingua['DATE']; ?></option>
                    </select>
                </div>
            </div>
            <div id="divProcEmails" style="float: left; background-color: #fff; height: 38px; margin: 0 2px 0 2px;">
                <input id="txtProcEmails" name="txtProcEmails" class="txtProcEmails" type="text" placeholder="Pesquise aqui" style="float: left; height: 95%; margin: 0; padding: 0 0 0 5px; font-size: 18px; border: 1px #77a4d7 solid;">
                <label class="icon-lupa" style="float: right; font-size: 28px; border: none; position: relative; line-height: 1em; color: #eaedf1; background: #77a4d7; padding: 5px;"></label>
            </div>
        </div>
    </div>
    <table id="tblEmailsRecebidos" name="tblEmailsRecebidos" class="tab_emails">
        <tbody><?php while ($linha_email_recebido = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) { ?>
                <?php
                if ($linha_email_recebido['lido'] == 0) {
                    $lido = "nova";
                } else {
                    $lido = "lida";
                }
                if ($linha_email_recebido['anexo'] == null) {
                    $anexo = "";
                } else {
                    $anexo = "botaoAnexo icon-anexo";
                }
                ?>
                <tr>
                    <td class="chkTD td5 <?php echo $lido; ?>"><div class="checkbox"><input id="chk_<?php echo $linha_email_recebido['id']; ?>" name="chk" type="checkbox" class="chk" value="<?php echo $linha_email_recebido['id']; ?>"><label for="chk" class="label_chk" style="padding-left: 0;">&nbsp;</label></div></td>
                    <td class="td25 <?php echo $lido; ?> ler"><input name="flag" type="hidden" value="1"><?php echo $linha_email_recebido['de']; ?></td>
                    <td class="td45 <?php echo $lido; ?>"><?php echo $linha_email_recebido['assunto']; ?></td>
                    <td class="td5 <?php echo $lido; ?> <?php echo $anexo; ?>"></td>
                    <td class="td20 <?php echo $lido; ?>"><?php echo $linha_email_recebido['data']; ?></td>
                </tr>
            <?php } ?></tbody>
    </table>
    <table id="tblEmailsEnviados" name="tblEmailsEnviados" class="tab_emails">
        <tbody></tbody>
    </table>
    <table id="tblEmailsEliminados" name="tblEmailsEliminados" class="tab_emails">
        <tbody></tbody>
    </table>
    <table id="tblEmailsVazio" name="tblEmailsVazio" class="tabela">
        <tr>
            <td colspan="5" style="background-color: #2b6db9; color: #fff;">Não existem resultados</td>
        </tr>
    </table>
    <div id="divLerMail">
        <div class="linha">
            <div class="left-column">
                <h3><?php echo $lingua['READ'].' '.$lingua['MSG']; ?></h3>
            </div>
            <div class="center-column">
                <div class="error"></div>
            </div>
            <div class="right-column">&nbsp;</div>
        </div>
        <div class="linha">
            <button id="btnVoltarR" name="btnVoltarR" class="btnNoIco voltarDir" value="1"><?php echo $lingua['BCK']; ?></button>
        </div>
        <div class="linha">
            <label for="txtRemetente" class="labelEsp"><?php echo ucfirst($lingua['FROM']).': '; ?></label>
            <div class="tamanho80">
                <input id="txtRemetente" type="text" name="txtRemetente" class="inputNoBackground" readonly="readonly">
            </div>
            <input id="hddRemetente" type="hidden" name="hddRemetente" readonly="readonly">
            <input id="hddMail" type="hidden" name="hddMail" readonly="readonly">
        </div>
        <div class="linha">
            <label for="txtLerAssunto" class="labelEsp"><?php echo $lingua['SUB'].': '; ?></label>
            <div class="tamanho80">
                <input id="txtLerAssunto" name="txtLerAssunto" type="text" class="inputNoBackground" readonly="readonly">
            </div>
        </div>
        <div class="linha">
            <a id="aAnexoEmail" href="" target="_blank"></a>
        </div>
        <div class="linha">
            <textarea id="txtaMensagem" name="txtaMensagem"></textarea>
        </div>
        <div class="linha">
            <section class="left-column">&nbsp;</section>
            <section class="center-right-column">
                <button id="btnRsp" name="btnRsp" class="btn btn-3 btn-3a icon-envelope"><?php echo $lingua['ANS']; ?></button>
                <button id="btnReencaminhar" name="btnReencaminhar" class="btn btn-3 btn-3a icon-voltar"><?php echo $lingua['FWD']; ?></button>
                <button id="btnEliminar" name="btnEliminar" class="btn btn-3 btn-3a icon-garbage"><?php echo $lingua['DEL']; ?></button>
            </section>
        </div>
    </div>
    <div id="divNovaMensagem">
        <div class="linha">
            <div class="left-column">
                <h3><?php echo $lingua['NEW'].' '.$lingua['MSG']; ?></h3>
            </div>
            <div class="center-column">
                <div class="error"></div>
            </div>
            <div class="right-column">&nbsp;</div>
        </div>
        <div class="linha">
            <section class="left-column">&nbsp;</section>
            <section class="center-column">&nbsp;</section>
            <section class="right-column">
                <button id="btnVoltarA" name="btnVoltarA" class="btnNoIco voltarDir" value=""><?php echo $lingua['BCK']; ?></button>
            </section>
        </div>
        <form id="frmEmail" name="frmEmail" enctype="multipart/form-data">
            <div class="linha">
                <div id="divDadosDest">
                    <label for="txtDestinatario" class="labelEsp"><?php echo ucfirst($lingua['TO']).': '; ?></label>
                    <textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq caixaTextoPeqEd"></textarea>
                    <input id="hddDestinatario" name="hddDestinatario" type="hidden">
                </div>
            </div>
            <div class="linha">
                <label for="txtAssuntoEnviar" class="labelEsp"><?php echo $lingua['SUB'].': '; ?></label>
                <div class="inputarea_assunto">
                    <input id="txtAssuntoEnviar" name="txtAssuntoEnviar" type="text" class="inputNoBackground">
                </div>
            </div>
            <div class="linha">
                <textarea id="txtaEditor" name="txtaEditor"></textarea>
            </div>
            <div class="linha">
                <button id="btnAnexar" name="btnAnexar" class="btn btn-3 btn-3a icon-anexo" style="float: left;"><?php echo $lingua['ATT']; ?></button>
                <input id="fileAnexar" name="fileAnexar" type="file">
                <div class="inputarea_38">
                    <input id="txtPath" name="txtPath" type="text" readonly="readonly" class="inputNoBackground" style="font-size: 9pt;">
                </div>
            </div>
        </form>
        <div class="linha">
            <section class="left-column">&nbsp;</section>
            <section class="center-column">&nbsp;</section>
            <section class="right-column">
                <button id="btnEnviar" name="btnEnviar" class="btn btn-3 btn-3a icon-envelope" style="float: left;"><?php echo $lingua['SND']; ?></button>
                <button id="btnLimpar" name="btnLimpar" class="botaoG icon-garbage"></button>
            </section>
        </div>
    </div>
</div>
<!--// Notas de Crédito -->
<div id="nota_credito">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['M_C_NOTE']; ?></h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
        <div class="right-column">&nbsp;</div>
    </div>
    <div class="linha">
        <div class="loading" style="width: 150px;">
            <div class="linha10">
                <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
            </div>
            <div class="linha" style="text-align: center;">A carregar...</div>
        </div>
    </div>
    <div class="linha">
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcFiltrarNotaCredito" name="slcFiltrarNotaCredito" size="1" class="select">
                    <option selected="selected" value="0">- Filtrar por -</option>
                    <?php foreach ($arr_fornecedores as $linha_fornecedor_nc) { ?>
                        <option value="<?php echo $linha_fornecedor_nc['id']; ?>"><?php echo $linha_fornecedor_nc['nome_abrev']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    <table id="tblEncomendasEfet" name="tblEncomendasEfet" class="tabela">
        <tr>
            <td class="td25">Nº</td>
            <td class="td10">Data</td>
            <td class="td5">Pago</td>
            <td class="td30">Fornecedor</td>
            <td class="td25">Valor</td>
            <td class="td5" style="background-color: transparent;">&nbsp;</td>
        </tr>
        <?php while ($linha_enc_nota_credito = $query_enc_nota_credito->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td class="refDetFatura" style="color: #2b6db9; padding: 4px; cursor: pointer;"><b><?php echo $linha_enc_nota_credito['ref']; ?></b>
                    <input name="hddIdEnc" type="hidden" value="<?php echo $linha_enc_nota_credito['id']; ?>">
                </td>
                <td style="padding: 4px;"><?php echo $linha_enc_nota_credito['data']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_enc_nota_credito['pago']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_enc_nota_credito['nome_abrev']; ?></td>
                <td style="padding: 4px;"><?php echo number_format($linha_enc_nota_credito['total'], 2, ',', '.'); ?> <?php echo $linha_enc_nota_credito['simbolo']; ?></td>
                <td style="background-color: transparent; padding: 0; cursor: pointer;">
                    <a href="./impressao/fatura.php?id=<?php echo $linha_enc_nota_credito['id']; ?>&p=<?php echo $linha_enc_nota_credito['abrev_pais'] ?>" target="_blank">
                        <img width="33" height="33" src="images/adobe_logo.png">
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>
    
    <div class="linha">
        <div id="divFatDetail" name="divFatDetail">
            <input id="refFaturaNC" type="text" name="refFaturaNC" class="inputNoBackground" readonly="readonly">
            <input id="hddIdFatNC" type="hidden">
            <table id="tblFatDetail" name="tblFatDetail" class="tabela">
                <tr>
                    <td class="td5">Nº</td>
                    <td class="td35">Nome do item</td>
                    <td class="td25">Fornecedor</td>
                    <td class="td10">Preço</td>
                    <td class="td10">Qtd comprada</td>
                    <td class="td10">Qtd a devolver</td>
                    <td class="td10">Valor</td>
                    <td class="td5" style="background-color: transparent;">&nbsp;</td>
                </tr>
            </table>
            <table id="tblFatVazia" name="tblFatVazia" class="tabela">
                <tr>
                    <td>Já não existem produtos nesta fatura</td>
                </tr>
            </table>
            <div class="linha" style="margin-top: 20px;">
                <div id="divTotalEncomenda" name="divTotalEncomenda" align="left">
                    <input id="txtTotNCsDesc" name="txtTotNCsDesc" type="text" class="total_campo" readonly="readonly" value="">
                    <input id="txtDescontoNC" name="txtDescontoNC" type="text" class="total_campo" readonly="readonly" value="">
                    <input id="txtIvaNC" name="txtIvaNC" type="text" class="total_campo" readonly="readonly" value="">
                    <input id="txtSomaNC" name="txtSomaNC" type="text" class="total_campo tot" readonly="readonly" value="">
                </div>
                <button id="btnDevolver" name="btnDevolver" class="btn btn-3 btn-3a icon-carrinho">Devolver</button>
                <button id="btnVoltarNC" name="btnVoltarNC" class="btnNoIco">Voltar</button>
            </div>
        </div>
    </div>
</div>
<!-- Listagem de Notas de Crédito -->
<div id="ver_notas_credito">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['V_C_NOTE']; ?></h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha">
        <table id="tblNotasCredito" name="tblNotasCredito" class="tabela">
            <tr>
                <td class="td25" style="padding: 6px;">Referência encomenda</td>
                <td class="td10" style="padding: 6px;"><?php echo $lingua['DATE']; ?></td>
                <td class="td5" style="padding: 6px;">Pago</td>
                <td class="td20" style="padding: 6px;">Fornecedor</td>
                <td class="td20" style="padding: 6px;">País</td>
                <td class="td10" style="padding: 6px;">IVA</td>
                <td class="td10" style="padding: 6px;">Total</td>
                <td class="td5" style="background-color: transparent;">&nbsp;</td>
            </tr>
            <?php while ($linha_nc = $query_nc->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td class="td25" style="padding: 6px;"><?php echo $linha_nc['ref']; ?></td>
                    <td class="td10" style="padding: 6px;"><?php echo date("d-m-Y", strtotime($linha_nc['data'])); ?></td>
                    <td class="td5" style="padding: 6px;"><?php echo $linha_nc['pago']; ?></td>
                    <td class="td20" style="padding: 6px;"><?php echo $linha_nc['nome_abrev']; ?></td>
                    <td class="td20" style="padding: 6px;"><?php echo $linha_nc['nome_pais']; ?></td>
                    <td class="td10" style="padding: 6px;"><?php echo number_format($linha_nc['iva'], 2, ',', '.').' '.$linha_nc['simbolo']; ?></td>
                    <td class="td10" style="padding: 6px;"><?php echo number_format($linha_nc['total'], 2, ',', '.').' '.$linha_nc['simbolo']; ?></td>
                    <td style="background-color: transparent; padding: 0; cursor: pointer;">
                        <a href="./impressao/nota_credito.php?id=<?php echo $linha_nc['id']; ?>&p=<?php echo $linha_nc['abrev_pais'] ?>" target="_blank">
                            <img width="33" height="33" src="images/adobe_logo.png">
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblNotaCreditoVazia" name="tblNotaCreditoVazia" class="tabela">
            <tr>
                <td>Não tem, neste momento, nenhuma nota de crédito</td>
            </tr>
        </table>
    </div>
</div>

<!-- Listagem de desconto fornecedor disponiveis -->
<div id="ver_desconto">
    <div class="linha">
        <div class="left-column">
            <!-- <h3><?php echo $lingua['V_C_NOTE']; ?></h3> -->
            <h3> Consultar descontos </h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha">
        <table id="tblDescFornec" name="tblDescFornec" class="tabela">
            <tr>
                <td class="td5" style="padding: 6px;">#</td>
                <td class="td20" style="padding: 6px;">Fornecedor</td>
                <td class="td30" style="padding: 6px;">Produto</td>
                <!-- <td class="td15" style="padding: 6px;">Descrição</td> -->
                <td class="td15" style="padding: 6px;">Familia</td>
                <td class="td15" style="padding: 6px;">Desconto</td>
                <td class="td15" style="padding: 6px;">Prazo de pagamento</td>
            </tr>
            <?php $i = 1; while ($linha_fp_desc = $query_fp_desc->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $linha_fp_desc['fornecedor']; ?></td>
                    <td><?php echo $linha_fp_desc['produto']; ?></td>
                    <!-- <td><?php echo $linha_fp_desc['descricao']; ?></td> -->
                    <td><?php echo $linha_fp_desc['familia']; ?></td>
                    <td><?php echo $linha_fp_desc['desconto']; ?></td>
                    <td><?php echo $linha_fp_desc['prazo_pag']; ?></td>
                </tr>
            <?php $i++; } ?>
        </table>
        <table id="tblDescFornecVazia" name="tblDescFornecVazia" class="tabela">
            <tr>
                <td>Não existem descontos disponiveis de momento</td>
            </tr>
        </table>
    </div>
</div>


<!-- Adiantamentos a Fornecedores (MOVED FROM conteudo_banco) -->
<div id="adiant_fornec">
    <div class="linha">
        <div class="left-column">
            <h3><?php
                echo $lingua['MAKE'];
                echo " ";
                echo lcfirst($lingua['ADVANCE']);
                ?></h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>

    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>

    <div class="linha10">
        <div class="esq20">
            <label for="txtNomeEmpresaF" class="labelNormal"><?php echo $lingua['COMPANY']; ?></label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <input id="txtNomeEmpresaF" name="txtNomeEmpresaF" type="text" value="<?php echo $linha_dados_empresa['nome']; ?>" readonly="readonly">
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20">
            <label for="txtNrContaF" class="labelNormal"><?php echo $lingua['ACCOUNT']; ?></label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <input id="txtNrContaF" name="txtNrContaF" type="text" value="<?php echo '0' . $linha_dados_empresa['num_conta']; ?>" readonly="readonly">
            </div>
        </div>
    </div>

    <div class="linha">
        <div class="esq20">
            <label for="slcPaisFornecedorA" class="labelNormal"> País do fornecedor </label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <div class="styled-select">
                    <select id="slcPaisFornecedorA" name="slcPaisFornecedorA" size="1" class="select">
                        <option value="0" selected="selected">- Escolha um país -</option>
                        <?php foreach ($pais_fornecedor AS $pais) { ?>
                            <option value="<?php echo $pais['id_pais']; ?>"><?php echo $pais['nome_pais']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="linha">
        <div class="esq20">
            <label for="slcFornecedorA" class="labelNormal"> Fornecedor </label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <div class="styled-select">
                    <select id="slcFornecedorA" name="slcFornecedorA" size="1" class="select">
                        <option value="0" selected="selected">- Escolha um fornecedor -</option>
                        <?php /* while ($linha_fornecedores = $query_fornecedores->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?php echo $linha_fornecedores['id']; ?>"><?php echo $linha_fornecedores['nome_abrev']; ?></option>
                        <?php } */ ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="linha" style="display: none;">
        <div class="esq20">
            <label for="slcIvaAd" class="labelNormal">Taxa de IVA</label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <div class="styled-select">
                    <select id="slcIvaAd" name="slcIvaAd" size="1" class="select">
                        <option value="0" selected="selected">- Escolha a taxa de IVA -</option>
                        <?php foreach ($txs_iva AS $tx_iva) { ?>
                        <option value="<?php echo $tx_iva['valor']; ?>"><?php echo number_format($tx_iva['valor'], 2, ',', '.').' %'; ?></option>
                        <?php } ?>
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="linha">
        <div class="esq20">
            <label for="txtAdiantamentoFornec" class="labelNormal"><?php echo $lingua['TOTAL_VAL']; ?></label>
        </div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtAdiantamentoFornec" name="txtAdiantamentoFornec" type="text" class="dinheiro">
                <input id="ISOmoeda" name="ISOmoeda" type="hidden" value="EUR">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha" style="text-align: center;">
        <button id="btnRegAdiantamentoF" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 10px;"><?php echo $lingua['REGISTER']; ?></button>
    </div>
</div>

<div id="adiant_efet">
    <div class="linha">
        <div class="left-column">
            <h3><?php
                echo $lingua['ADVANCE'];
                echo " ";
                echo $lingua['MADE'];
                ?></h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>

    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>

    <div class="linha">
        <table id="tblAdiantEfet" name="tblAdiantEfet" class="tabela">
            <tr>
                <td class="td25" style="padding: 6px;"><?php echo /* $lingua['']; */ "Nome do fornecedor"; ?></td>
                <td class="td15" style="padding: 6px;"><?php echo /* $lingua['']; */ "Data de adiantamento"; ?></td>
                <td class="td15" style="padding: 6px;"><?php echo /* $lingua['']; */ "Valor sem IVA"; ?></td>
                <td class="td10" style="padding: 6px;"><?php echo /* $lingua['']; */ "Taxa de IVA"; ?></td>
                <td class="td15" style="padding: 6px;"><?php echo /* $lingua['']; */ "Valor de IVA"; ?></td>
                <td class="td15" style="padding: 6px;"><?php echo /* $lingua['']; */ "Total adiantado"; ?></td>
                <td class="td5" style="background-color: transparent;">&nbsp;</td>
            </tr>
            <?php while ($linha_adiant_efet = $query_carrega_adiant_efet->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="padding: 6px;"><?php echo $linha_adiant_efet['nome_abrev']; ?></td>
                    <td style="padding: 6px;"><?php echo date("d-m-Y", strtotime($linha_adiant_efet['data_virt'])); ?></td>
                    <td style="padding: 6px;"><?php echo number_format($linha_adiant_efet['valor_s_iva'], 2, ',', '.') . ' ' . $linha_moeda['simbolo']; ?></td>
                    <td style="padding: 6px;"><?php echo number_format($linha_adiant_efet['tx_iva'], 2, ',', '.') . ' %'; ?></td>
                    <td style="padding: 6px;"><?php echo number_format($linha_adiant_efet['iva'], 2, ',', '.') . ' ' . $linha_moeda['simbolo']; ?></td>
                    <td style="padding: 6px;"><?php echo number_format($linha_adiant_efet['valor'], 2, ',', '.') . ' ' . $linha_moeda['simbolo']; ?></td>
                    <td style="background-color: transparent; padding: 0; cursor: pointer;">
                        <a href="./impressao/adiantamento.php?id=<?php echo $linha_adiant_efet['id_adiantamento']; ?>&p=<?php echo /*$linha_encomendas['abrev_pais']*/ 'PT'; ?>" target="_blank">
                            <img width="33" height="33" src="images/adobe_logo.png">
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblAdiantEfetVazia" name="tblAdiantEfetVazia" class="tabela">
            <tr>
                <td>Não tem, neste momento, nenhum adiantamento</td>
            </tr>
        </table>
    </div>
</div>

<div id="down_docs">
    <div class="linha">
        <div class="left-column">
            <h3>Documentos para baixar</h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
        <div class="right-column">&nbsp;</div>
    </div>
    <div class="linha" style="width: 80%">
        <table id="downDocs" name="downDocs" data-value="1" class="tabela">
            <tbody style="padding: 4px;">
                <tr>
                    <td class="td40">Professor (a)</td>
                    <td class="td50">Documento</td>
                    <td class="td10">Baixar</td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Ata a deleberar a constituição de um emprestimo obrigacionista</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Ata_a_deleberar_a_constituição_de_um_emprestimo_obrigacionista.docx" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Contrato de compra e venda e mútuo com hipoteca</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Contrato_de_compra_e_venda_e_mutuo_com_hipoteca.doc" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Contrato de empréstimo consolidado</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Contrato+de+empréstimo+consolidado.docx" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Contrato de mútuo com hipoteca</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Contrato+de+mutuo+com+hipoteca.doc" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Contrato de suprimento</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Contrato+de+suprimento.doc" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Minuta de contrato de compra e venda de prédio rústico</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Minuta_de_contrato_de_compra_e_venda_de_prédio_rústico.doc" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Minuta de contrato de mútuo sem hipoteca</td>
                    <td style="padding: 4px;">
                        <a href="./documents/minuta_de_contrato_de_mútuo_sem_hipoteca.doc" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Minuta de contrato de arrendamento comercial</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Minuta+de+contrato+de+arrendamento+comercial.docx" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Termo de autenticação do contrato de compra e venda e mútuo com hipoteca</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Termo+de+Autenticação+do+Contrato+de+Compra+e+venda+e+mutuo+com+hipoteca.doc" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Paula Xavier</td>
                    <td style="padding: 4px;">Termo de autenticação do contrato de mútuo com hipoteca</td>
                    <td style="padding: 4px;">
                        <a href="./documents/Termo+de+autenticação+do+contrato+de+mutuo+com+hipoteca.doc" target="_blank">
                            <img width="28" height="28" src="images/word.png">
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>