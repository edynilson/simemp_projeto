<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 11:07:43
*/

include('../conf/check_pastas.php');

$query_dados_empresa = $connection->prepare("SELECT emp.nipc, emp.nome FROM empresa emp INNER JOIN dec_retencao dr ON emp.id_empresa=dr.id_empresa WHERE emp.ativo='1' AND dr.id=:id_dec_ret");
$query_dados_empresa->execute(array(':id_dec_ret' => $_GET['id_dec_ret']));
$linha_dados = $query_dados_empresa->fetch(PDO::FETCH_ASSOC);

$query_moeda = $connection->prepare("SELECT mo.simbolo, mo.nome FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_dec_ret_detalhes = $connection->prepare("SELECT cod.rubrica, dre.zona, dre.valor FROM dec_retencao dr INNER JOIN dec_retencao_empresa dre ON dr.id=dre.id_dec_retencao INNER JOIN codigo cod ON dre.rubrica=cod.id INNER JOIN empresa emp ON emp.id_empresa=dr.id_empresa WHERE emp.ativo='1' AND dr.id=:id_dec_ret");
$query_dec_ret_detalhes->execute(array(':id_dec_ret' => $_GET['id_dec_ret']));

$query_dec_ret = $connection->prepare("SELECT date_format(dr.`data`, '%m') AS mes, date_format(dr.`data`, '%Y') AS ano, dr.`data`, dr.data_lim_pag, dr.residentes, dr.total FROM dec_retencao dr INNER JOIN empresa emp ON emp.id_empresa=dr.id_empresa WHERE emp.ativo='1' AND dr.id=:id_dec_ret");
$query_dec_ret->execute(array(':id_dec_ret' => $_GET['id_dec_ret']));
$linha_dred = $query_dec_ret->fetch(PDO::FETCH_ASSOC);

$i = 1;
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
            <td style="height: 20px;" colspan="3"></td>
        </tr>
        <tr>
            <td style="width: 10%; vertical-align: middle; text-align: right; border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">
                <img src="../images/financas_logo.gif" style="height: 45px; width: 50px;">
            </td>
            <td style="width: 35%; vertical-align: top; border-top: 1px solid; border-bottom: 1px solid;">
                <div style="margin: 0; padding: 10px 0; text-align: center;">
                    <span style="font-weight: bold; font-size: 13px;">MINISTÉRIO DAS FINANÇAS</span>
                    <br>
                    DIRECÇÃO-GERAL DOS IMPOSTOS
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; border: 1px solid;">
                <div style="margin: 0; padding: 10px 0; text-align: center;">
                    <span style="font-weight: bold;">DECLARAÇÃO DE RETENÇÕES NA FONTE<br>IRS/IRC E IMPOSTO DE SELO</span>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; vertical-align: top; border-left: 1px solid; border-bottom: 1px solid;" colspan="2" rowspan="4">
                <div style="margin: 0; padding-top: 20px; text-align: center; font-weight: bold; font-size: 12px;">RETENÇÕES A NÃO RESIDENTES</div>
                <div style="padding: 10px 15px; font-size: 10px; text-align: justify;">Se esta guia respeita a retenções feitas a sujeitos passivos considerados <span style="font-weight: bold;">não residentes em Portugal</span>, assinale com x devendo apresentar a declaração anual prevista no n.º 7 do art. 119.º do CIRS. Neste caso não deve incluir retenções efetuadas a sujeitos passivos residentes.</div>
                <div style="padding-top: 12px; padding-bottom: 25px; text-align: center;">SIM
                    <?php if ($linha_dred['residentes'] == "0") { ?>
                        <div style="border: solid 1px #000000; padding: 0 1px 1px 1px; text-align: center; width: 7px; height: 6px; margin-left: 5px;">
                            <span style="font-size: 8px; padding: 0; margin: 0;">x</span>
                        </div>
                    <?php } else { ?>
                        <div style="border: solid 1px #000000; padding: 1px; text-align: center; width: 7px; height: 7px; margin-left: 5px;"></div>
                    <?php } ?>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; border-left: 1px solid; border-bottom: 1px solid; border-right: 1px solid; background-color: #D9DBD9;">
                <div style="padding-top: 10px; padding-bottom: 2px; text-align: center;">NÚMERO DE IDENTIFICAÇÃO FISCAL</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; vertical-align: top; border-left: 1px solid; border-bottom: 1px solid; border-right: 1px solid;">
                <div style="padding-top: 10px; padding-bottom: 2px; text-align: center; font-size: 11px;"><?php echo $linha_dados['nipc']; ?></div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; vertical-align: top; border-left: 1px solid; border-bottom: 1px solid; border-right: 1px solid; background-color: #D9DBD9;">
                <div style="padding-top: 10px; padding-bottom: 2px; text-align: center;">NOME</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; vertical-align: top; border-left: 1px solid; border-bottom: 1px solid; border-right: 1px solid;">
                <div style="padding-top: 20px; padding-bottom: 5px; text-align: center; font-size: 11px;"><?php echo $linha_dados['nome']; ?></div>
            </td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="height: 3px;"></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 80%; border-top: 1px solid; border-right: 1px solid; border-left: 1px solid;">
        <tr>
            <td style="height: 10px;" colspan="4"></td>
        </tr>
        <tr style="font-weight: bold; font-size: 14px;">
            <td style="width: 10%; padding-bottom: 10px;"></td>
            <td style="width: 25%; padding-bottom: 15px; padding-left: 15px;">ZONA</td>
            <td style="width: 25%; padding-bottom: 15px; padding-left: 15px;">CÓDIGO</td>
            <td style="width: 40%; padding-bottom: 15px; text-align: center;">IMPORTÂNCIA <?php echo $linha_moeda['simbolo']; ?></td>
        </tr>
        <?php
        while ($linha_dre = $query_dec_ret_detalhes->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <tr>
                <td style="padding-left: 10px; padding-bottom: 10px; font-weight: bold;"><?php echo $i++; ?></td>
                <td style="padding-left: 15px; padding-bottom: 10px;"><?php echo $linha_dre['zona']; ?></td>
                <td style="padding-left: 15px; padding-bottom: 10px;"><?php echo $linha_dre['rubrica']; ?></td>
                <td style="text-align: center; padding-bottom: 10px;"><?php echo number_format($linha_dre['valor'], 2, ',', '.'); ?></td>
            </tr>
        <?php } ?>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="width: 50%; border-left: 1px solid; border-bottom: 1px solid; border-top: 1px solid; padding-left: 35px; padding-top: 8px; padding-bottom: 10px;"><span style="font-size: 12px;">PERÍODO</span><span style="margin-left: 52px;"><?php echo $linha_dred['mes'] . "-" . $linha_dred['ano']; ?></span></td>
            <td style="width: 50%; border: 1px solid; padding-left: 35px; padding-top: 8px; padding-bottom: 10px;"><span style="font-size: 12px;">VALOR A PAGAR</span><span style="margin-left: 38px;"><?php echo number_format($linha_dred['total'], 2, ',', '.'); ?></span></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="padding-left: 125px; padding-top: 25px; font-size: 12px;">Data e hora da receção da declaração: <?php echo $linha_dred['data']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="height: 20px;" colspan="2"></td>
        </tr>
        <tr>
            <td style="width: 40%; vertical-align: middle; border: 1px solid; background-color: #D9DBD9;">
                <div style="text-align: center; font-size: 17px; padding: 3px 0;">Importância a pagar</div>
            </td>
            <td style="width: 60%; font-size: 13px; padding-left: 15px; text-align: justify;" rowspan="4">O Pagamento pode ser efetuado através do Multibanco, da Internet, das Tesourarias das finanças, dos CTT e das Instituições de Crédito, utilizando a referência indicada.
                <br><br>
                Para efetuar o pagamento pela Internet utilize o serviço on-line do seu Banco e selecione Pagamentos ao Estado.</td>
        </tr>
        <tr>
            <td style="width: 40%; vertical-align: middle; border-left: 1px solid; border-right: 1px solid;">
                <div style="text-align: center; font-size: 17px; padding: 3px 0;"><?php echo number_format($linha_dred['total'], 2, ',', '.') . " " . $linha_moeda['simbolo']; ?></div>
            </td>
        </tr>
        <tr>
            <td style="width: 40%; vertical-align: middle; border: 1px solid; background-color: #D9DBD9;">
                <div style="text-align: center; font-size: 17px; padding: 3px 0;">Data limite de pagamento</div>
            </td>
        </tr>
        <tr>
            <td style="width: 40%; vertical-align: middle; border-left: 1px solid; border-right: 1px solid; border-bottom: 1px solid;">
                <div style="text-align: center; font-size: 17px; padding: 3px 0;"><?php echo $linha_dred['data_lim_pag']; ?></div>
            </td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="height: 100px;"></td>
        </tr>
        <tr>
            <td style="width: 40%; vertical-align: top; font-size: 13px; border: 1px solid; text-align: center; height: 140px;">Certificação do pagamento</td>
            <td style="width: 60%; height: 140px; vertical-align: top;">
                <div style="font-size: 14px; padding-top: 15px; text-align: center; padding-bottom: 70px;">Assinatura</div>
                <div style="border-bottom: solid 1px #000000; width: 320px; margin-left: 50px;"></div>
            </td>
        </tr>
    </table>
</page>