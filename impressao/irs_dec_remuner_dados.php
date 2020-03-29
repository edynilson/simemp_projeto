<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('../conf/check_pastas.php');

$query_dec_rem_data = $connection->prepare("SELECT emp.id_empresa, UPPER(emp.nome) AS nome, UPPER(emp.morada) AS morada, UPPER(emp.localidade) AS localidade, emp.cod_postal, emp.niss, emp.nipc, DATE(e.`data`) AS data_dec, e.ficheiro, e.mes, e.ano, e.valor, m.simbolo FROM entrega e INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN empresa emp ON e.id_empresa=e.id_empresa INNER JOIN pais p ON emp.pais=p.nome_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE e.id=:dec_rem_id AND te.designacao='Declaração de Remunerações (AT)' AND emp.id_empresa=:id_empresa LIMIT 1");
$query_dec_rem_data->execute(array(':dec_rem_id' => $_GET['id'], ':id_empresa' => $_SESSION['id_empresa']));
$linha_dec_rem_data = $query_dec_rem_data->fetch(PDO::FETCH_ASSOC);

$datetime = new DateTime();
?>

<page backtop="0mm" backbottom="10mm">
    <page_footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left; width: 20%">Simemp <?php echo $datetime->format('Y'); ?></td>
                <td style="text-align: center; width: 60%">Documento válido apenas no Simemp &copy; &reg;</td>
                <td style="text-align: right; width: 20%">Pág. [[page_cu]]/[[page_nb]]</td>
            </tr>
        </table>
    </page_footer>
    
    <!-- Logo CABEÇALHO -->
    <table cellspacing="0" style="width: 100%; text-align: center; margin-left: 0px; padding-left: -20px;">
        <tr>
            <td style="width: 100%; vertical-align: middle;">
                <img width="720" height="85" src="../images/AT_irs_dec_remuner.png">
            </td>
        </tr>
    </table>
    
    <!-- 1ª Tabela -->
    <table cellspacing="0" style="margin-top: 20px; width: 103%; text-align: center; margin-left: 0px; padding-left: -20px;">
        <tr>
            <td colspan="2" style="width: 100%; vertical-align: middle; font-size: 9pt; border: 1px solid; background-color: #bbbbbb; padding: 7px 7px 7px 7px;">
                IDENTIFICAÇÃO DO SUJEITO PASSIVO
            </td>
        </tr>
        <tr>
            <td style="width: 10%; text-align: center; font-size: 7pt; border-left: 1px solid; border-bottom: 1px solid; background-color: #bbbbbb; padding: 5px 5px 5px 5px;">NOME</td>
            <td style="width: 90%; text-align: left; font-size: 9pt; border-bottom: 1px solid; border-right: 1px solid; padding: 7px 7px 7px 7px;"><?php echo $linha_dec_rem_data['nome'] ?></td>
        </tr>
        <tr>
            <td style="width: 10%; text-align: center; font-size: 7pt; border-left: 1px solid; border-bottom: 1px solid; background-color: #bbbbbb; padding: 5px 5px 5px 5px;">MORADA</td>
            <td style="width: 90%; text-align: left; font-size: 9pt; border-bottom: 1px solid; border-right: 1px solid; padding: 7px 7px 7px 7px;"><?php echo $linha_dec_rem_data['morada'] ?></td>
        </tr>
        <tr>
            <td style="width: 10%; text-align: center; font-size: 7pt; border-left: 1px solid; border-bottom: 1px solid; background-color: #bbbbbb; padding: 5px 5px 5px 5px;">LOCALIDADE</td>
            <td style="width: 90%; text-align: left; font-size: 9pt; border-bottom: 1px solid; border-right: 1px solid; padding: 0px 0px 0px 0px;">
                <table cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; text-align: left; font-size: 9pt; padding: 7px 7px 7px 7px;"><?php echo $linha_dec_rem_data['localidade'] ?></td>
                        <td style="width: 7%; text-align: center; font-size: 7pt; border-left: 1px solid; background-color: #bbbbbb;">CÓDIGO POSTAL</td>
                        <td style="width: 43%; text-align: left; font-size: 9pt; padding: 7px 7px 7px 7px;"><?php echo $linha_dec_rem_data['cod_postal'].' '.$linha_dec_rem_data['localidade']; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Linha de TABELAS -->
    <table cellspacing="0" style="margin-top: 20px; width: 103%; text-align: center; margin-left: 0px; padding-left: -20px;">
        <tr>
            <td style="width: 25%; text-align: center; font-size: 7pt; border-top: 1px solid; border-left: 1px solid; border-right: 1px solid; background-color: #bbbbbb;">
                NÚMERO DE IDENTIFICAÇÃO FISCAL
            </td>
            <td style="width: 3%"></td>
            <td style="width: 15%; text-align: center; font-size: 7pt; border-top: 1px solid; border-left: 1px solid; border-right: 1px solid; background-color: #bbbbbb;">
                PERÍODO
            </td>
            <td style="width: 3%"></td>
            <td style="width: 25%; text-align: center; font-size: 7pt; border-top: 1px solid; border-left: 1px solid; border-right: 1px solid; background-color: #bbbbbb;">
                IDENTIFICAÇÃO DA DECLARAÇÃO
            </td>
            <td style="width: 3%"></td>
            <td style="width: 26%; text-align: center; font-size: 7pt; border-top: 1px solid; border-left: 1px solid; border-right: 1px solid; background-color: #bbbbbb;">
                DATA E HORA DE RECEÇÃO DA DECLARAÇÃO
            </td>
        </tr>
        <tr>
            <td style="width: 25%; text-align: center; font-size: 9pt; border-bottom: 1px solid; border-left: 1px solid; border-right: 1px solid; padding: 7px 0px 7px 0px;">
                <?php echo $linha_dec_rem_data['nipc'] ?>
            </td>
            <td style="width: 3%"></td>
            <td style="width: 15%; text-align: center; font-size: 9pt; border-bottom: 1px solid; border-left: 1px solid; border-right: 1px solid;">
                <?php echo $linha_dec_rem_data['ano'].'/'.$linha_dec_rem_data['mes']; ?>
            </td>
            <td style="width: 3%"></td>
            <td style="width: 25%; text-align: center; font-size: 9pt; border-bottom: 1px solid; border-left: 1px solid; border-right: 1px solid;">
                987654321
            </td>
            <td style="width: 3%"></td>
            <td style="width: 26%; text-align: center; font-size: 9pt; border-bottom: 1px solid; border-left: 1px solid; border-right: 1px solid;">
                <?php echo $linha_dec_rem_data['data_dec'].' 10:00:00'; ?>
            </td>
        </tr>
    </table>
    
    <!-- Tabelas de info de pagamento e texto -->
    <table cellspacing="0" style="margin-top: 20px; width: 103%; text-align: center; margin-left: 0px; padding-left: -20px;">
        <tr>
            <td style="width: 57%; border-top: 1px solid; border-right: 1px solid; border-bottom: 1px solid; border-left: 1px solid; padding-top: 0px; padding-right: 0px;">
                <table cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="width: 100%; font-size: 9pt; background-color: #bbbbbb; padding: 7px 7px 7px 7px;">REFERÊNCIA PARA PAGAMENTO</td>
                    </tr>
                    <tr>
                        <td style="width: 100%; font-size: 9pt; padding: 7px 7px 7px 7px;">012.345.678.910.000</td>
                    </tr>
                    <tr>
                        <td style="width: 100%; font-size: 9pt; background-color: #bbbbbb; padding: 7px 7px 7px 7px;">LINHA ÓTICA</td>
                    </tr>
                    <tr>
                        <td style="width: 100%; font-size: 9pt; padding: 7px 7px 7px 7px;">001234567891011121314151617</td>
                    </tr>
                    <tr>
                        <td style="width: 100%; font-size: 9pt; background-color: #bbbbbb; padding: 7px 7px 7px 7px;">IMPORTÂNCIA A PAGAR</td>
                    </tr>
                    <tr>
                        <td style="width: 100%; font-size: 9pt; padding: 7px 7px 7px 7px;"><?php echo number_format($linha_dec_rem_data['valor'], 2, ',', '.').' '.$linha_dec_rem_data['simbolo']; ?></td>
                    </tr>
                </table>
            </td>
            <td style="width: 43%; border-top: 1px solid; border-right: 1px solid; border-bottom: 1px solid; border-left: 1px solid; font-size: 7pt; font-weight: bold; text-align: left; padding-left: 15px;">
                O pagamento pode ser efetuado no Multibanco, na Internet, <br>
                nos CTT, nas instituições bancárias e nos Serviços de <br>
                Finanças, utilizando a referência indicada. <br><br>
                Para efetuar o pagamento pela Internet utilize o serviço online <br>
                do seu Banco e selecione Pagamentos ao Estado e Setor <br>
                Público. No Multibanco selecione Pagamentos ao Estado e <br>
                Setor Público. <br><br>
                Este documento só é válido quando acompanhado pelos <br>
                comprovativos do pagamento.
            </td>
        </tr>
    </table>
    
    <!-- Área de código de barra -->
    <table cellspacing="0" style="margin-top: 35px; width: 60%;">
        <tr>
            <td style="font-size: 9pt;">REFERÊNCIA</td>
        </tr>
        <tr>
            <td style="padding-top: 8px; padding-left: 15px;">
                <img src="../images/AT_cod_barra_1.png">
            </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 8px; text-align: center;">001234567891011121314151617</td>
        </tr>
    </table>
    
    <table cellspacing="0" style="margin-top: 35px; width: 40%;">
        <tr>
            <td style="font-size: 9pt;">IMPORTÂNCIA</td>
        </tr>
        <tr>
            <td style="padding-top: 8px; padding-left: 15px;">
                <img src="../images/AT_cod_barra_2.png">
            </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 8px; text-align: center;">
                <?php 
                    $val = number_format($linha_dec_rem_data['valor'], 2, '', '');
                    for ($i = strlen($val); $i < 14; $i++) $val = '0'.$val;
                    echo $val;
                ?>
            </td>
        </tr>
    </table>
    
    <!-- Area de Certificado pag. e Assinatura -->
    <table cellspacing="3" style="margin-top: 35px; margin-left: 15px; width: 97%;">
        <tr>
            <td style="width: 35%; padding: 7px 5px 7px 5px; background-color: #bbbbbb; font-size: 9pt; text-align: center;">CERTIFICAÇÃO DO PAGAMENTO</td>
            <td style="width: 20%;"></td>
            <td style="width: 45%; font-size: 9pt; text-align: center;">ASSINATURA</td>
        </tr>
        <tr>
            <td style="width: 35%; height: 140px; border: 1px solid;"></td>
            <td style="width: 20%;"></td>
            <td style="width: 45%; border-bottom: 1px solid;"></td>
        </tr>
    </table>
</page>

