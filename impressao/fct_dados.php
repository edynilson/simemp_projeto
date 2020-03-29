<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('../conf/check_pastas.php');

$query_fct_data = $connection->prepare("SELECT emp.id_empresa, emp.nome, emp.niss, emp.nipc, DATE(e.`data`) AS data_dec, DATE(DATE_ADD(e.`data`, INTERVAL 8 DAY)) AS data_lim, e.mes, e.ano, e.valor, m.simbolo FROM entrega e INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN empresa emp ON e.id_empresa=e.id_empresa INNER JOIN pais p ON emp.pais=p.nome_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE e.id=:fct_id AND te.designacao='Fundo de Compensação do Trabalho' AND emp.id_empresa=:id_empresa");
$query_fct_data->execute(array(':fct_id' => $_GET['id'], ':id_empresa' => $_SESSION['id_empresa']));
$linha_fct_data = $query_fct_data->fetch(PDO::FETCH_ASSOC);

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
    
    <!-- Logo CABEÇALHO -->
    <table cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 100%; vertical-align: middle; text-align: center;">
                <img src="../images/fct_logo_2.png">
            </td>
        </tr>
    </table>
    
    <!-- PRIMEIRA secção -->
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="padding-left: 20px; padding-top: 60px; font-size: 13px; font-weight: bold; color: #4c8647">DOCUMENTO DE PAGAMENTO</td>
        </tr>
    </table>
    
    <!-- Primeira àrea cinzenta -->
    <!-- Tabela de 3 colunas, com PRAZOS -->
    <table cellspacing="0" style="margin-left: 20px; margin-top: 10px; padding-top: 10px; width: 100%; text-align: center; background-color: #D9DBD9; border-top-left-radius: 5px; border-top-right-radius: 5px;">
        <tr>
            <td style="width: 33%; font-size: 80%; font-weight: bold">IDENTIFICAÇÃO DO DOCUMENTO </td>
            <td style="width: 33%; font-size: 80%; font-weight: bold">DATA DE EMISSÃO</td>
            <td style="width: 33%; font-size: 80%; font-weight: bold">PERÍODO PAGAMENTO</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style="padding-top: 10px;"><?php echo $linha_fct_data['data_dec']; ?></td>
            <td style="padding-top: 10px; text-align: left; padding-left: 65px;">DE: <?php echo $linha_fct_data['data_dec']; ?></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td style="padding-top: 10px; text-align: left; padding-left: 65px;">ATÉ: <?php echo $linha_fct_data['data_lim']; ?></td>
        </tr>
    </table>
    
    <!-- Tabela de 4 linhas, com variaveis e valor -->
    <table cellspacing="0" style="margin-left: 20px; padding-left: 8px; width: 99%; font-size: 80%; text-align: left; background-color: #D9DBD9; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">
        <tr>
            <td style="width: 100%; padding-top: 12px;"> <b>NOME:</b> <?php echo $linha_fct_data['nome']; ?> </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 12px;"> <b>NÚMERO DE IDENTIFICAÇÃO DE SEGURANÇA SOCIAL:</b> <?php echo $linha_fct_data['niss']; ?> </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 12px;"> <b>NÚMERO DE IDENTIFICAÇÃO FISCAL:</b> <?php echo $linha_fct_data['nipc']; ?> </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 12px;"> <b>VALOR A PAGAR:</b> <?php echo number_format($linha_fct_data['valor'], 2, ',', '.').' '.$linha_fct_data['simbolo']; ?> </td>
        </tr>
        <tr><td style="width: 100%; padding-top: 12px;">&nbsp;</td></tr>
    </table>
    
    <!-- Título de SEGUNDA secção -->
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="padding-left: 20px; padding-top: 40px; font-size: 13px; font-weight: bold; color: #4c8647">PAGAMENTO POR MULTIBANCO</td>
        </tr>
    </table>
    
    <!-- SECÇÃO + COMPLEXA: TABELA com 3 tabelas dentro, para garantir que "coluna de dados MB" + "coluna de espaçamento" + "coluna de texto" se movam da mesma forma -->
    <table cellspacing="0" style="margin-left: 20px; margin-top: 10px; width: 100%;">
        <tr>
            <!-- PRIMEIRA COLUNA: Contem SUBTABELA, com dados de MB -->
            <td style="width: 40%; vertical-align: top;">
                
                <!-- SUBTABELA de 3 colunas, com dados de pagamento -->
                <table cellspacing="0" style="text-align: center; background-color: #D9DBD9; border-top-left-radius: 5px; border-top-right-radius: 5px;">
                    <tr>
                        <td style="width: 20%; padding-top: 5px;" rowspan="2">
                            <img src="../images/MB.png" style="height: 45px; width: 50px;">
                        </td>
                        <td style="width: 40%; padding-top: 5px; font-size: 80%; font-weight: bold">Entidade</td>
                        <td style="width: 40%; padding-top: 5px; font-size: 80%; font-weight: bold">Pagamento</td>
                    </tr>
                    <tr>
                        <td style="font-size: 80%;">21448</td>
                        <td style="font-size: 80%;">De: <?php echo $linha_fct_data['data_dec']; ?></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="font-size: 80%;">Até: <?php echo $linha_fct_data['data_lim']; ?></td>
                    </tr>
                </table>
                
                <!-- SUBTABELA de uma linha, com ref. e montante -->
                <table cellspacing="0" style="width: 100%; padding: 5px 5px 5px 5px; font-size: 80%; background-color: #D9DBD9; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">
                    <tr>
                        <td style="width: 50%; text-align: left;"><b>Referência:</b> 123456789</td>
                        <td style="width: 5%">&nbsp;</td>
                        <td style="width: 45%; text-align: right;"><b>Montante:</b> <?php echo number_format($linha_fct_data['valor'], 2, ',', '.').' '.$linha_fct_data['simbolo']; ?></td>
                    </tr>
                </table>
            </td>
            
            <!-- SEGUNDA COLUNA: espaçamento -->
            <td style="width: 4%">&nbsp;</td>
            
            <!-- TERCEIRA COLUNA: Contem SUBTABELA, com texto fixo do documento -->
            <td style="width: 56%">
                <table cellspacing="0" style="width: 100%; padding: 5px 5px 5px 5px; font-size: 80%; background-color: #D9DBD9; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">
                    <tr>
                        <td style="width: 99%;">
                            <b>Formas de pagamento</b><br>
                            O pagamento pode ser efetuado através de Multibanco ou outro canal<br>
                            do sistema Bancário Português com a opção de Pagamento de Serviços<br>
                            utilizando a referência presente neste documento.<br><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Pagamento</b><br>
                            O pagamento pode ser feito até ao dia 8 (inclusive) do mês seguinte ao<br>
                            indicado na data limite do PERÍODO PAGAMENTO (ATÉ).<br>
                            Alerta-se que serão cobrados juros ao dia, devidos por cada dia de<br>
                            atraso após o dia 20.<br>
                            Os juros serão descriminados para cobrança na emissão do Documento<br>
                            de Pagamento do mês seguinte.<br><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Pagamento de múltiplas referências</b><br>
                            Quando o valor total a pagamento é superior a 99.999,99€, são geradas<br>
                            tantas referências quantas as necessárias ao pagamento fracionado.<br>
                            Todas as referências constantes neste documento deverão ser pagas, o<br>
                            não pagamento de qualquer uma das referências incorre na cobrança<br>
                            de juros e outros procedimentos.<br>
                        </td>
                    </tr>
                </table>
            </td>
            <!-- Fim 3ª coluna -->
        </tr>
    </table>
    <!-- Fim de SECÇÃO + COMPLEXA -->
    
    <!-- SECÇÃO de ESPAÇO EM BRANCO, para permitir NOVA Página -->
    <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    
    <!-- SECÇÃO "resumo" (pág. 2) -->
    <!-- Título -->
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="padding-left: 20px; padding-top: 40px; font-size: 13px; font-weight: bold; color: #4c8647">EXTRATO DE VALORES SELECIONADOS PARA O DOCUMENTO DE PAGAMENTO</td>
        </tr>
    </table>
    
    <!-- Tabela de 4 linhas, com variaveis -->
    <table cellspacing="0" style="margin-left: 20px; width: 99%; font-size: 80%; text-align: left;">
        <tr>
            <td style="width: 100%; padding-top: 25px;"> <b>NOME:</b> <?php echo $linha_fct_data['nome']; ?> </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 12px;"> <b>NÚMERO DE IDENTIFICAÇÃO DE SEGURANÇA SOCIAL:</b> <?php echo $linha_fct_data['niss']; ?> </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 12px;"> <b>NÚMERO DE IDENTIFICAÇÃO FISCAL:</b> <?php echo $linha_fct_data['nipc']; ?> </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-top: 12px;"> <b>DATA DE EMISSÃO:</b> <?php echo $linha_fct_data['data_dec']; ?> </td>
        </tr>
        <tr><td style="width: 100%; padding-top: 12px;">&nbsp;</td></tr>
    </table>
    
    
    <!-- SECÇÃO "quadro" (pág. 2) -->
    <!-- Título -->
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="padding-left: 20px; padding-top: 20px; font-size: 13px; font-weight: bold; color: #4c8647">EMPREGADOR</td>
        </tr>
    </table>
    
    <br>
    <!-- Tabela de de resumo de "pagamento" -->
    <table cellspacing="0" style="margin-left: 20px; width: 97%; font-size: 12px;">
        <tr>
            <td style="width: 15%; text-align: left; padding-left: 2px; font-weight: bold; background-color: #D9DBD9; border-top-left-radius: 5px; border-bottom-left-radius: 5px;">DESCRIÇÃO</td>
            <td style="width: 20%; background-color: #D9DBD9;">&nbsp;</td>
            <td style="width: 20%; background-color: #D9DBD9;">&nbsp;</td>
            <td style="width: 29%; background-color: #D9DBD9; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">&nbsp;</td>
            <td style="width: 1%;">&nbsp;</td>
            <td style="width: 15%; text-align: center; font-weight: bold; background-color: #D9DBD9; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">VALOR</td>
        </tr>
        <tr style="font-size: 12px;">
            <td style="padding-left: 2px;"><?php echo $linha_fct_data['data_dec']; ?></td>
            <td>FCT + FGCT</td>
            <td>ENTREGA</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td style="text-align: center;"><?php echo number_format($linha_fct_data['valor'], 2, ',', '.').' '.$linha_fct_data['simbolo']; ?></td>
        </tr>
        <!-- <tr style="font-size: 12px;">
            <td style="padding-left: 2px; background-color: #D9DBD9; border-top-left-radius: 5px; border-bottom-left-radius: 5px;"><?php echo $linha_fct_data['data_dec']; ?></td>
            <td style="background-color: #D9DBD9;">FGCT</td>
            <td style="background-color: #D9DBD9;">ENTREGA</td>
            <td style="background-color: #D9DBD9; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">&nbsp;</td>
            <td>&nbsp;</td>
            <td style="text-align: center; background-color: #D9DBD9; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">0,89 €</td>
        </tr> -->
    </table>
    <!-- Tabela com ÚLTIMA linha, do Total -->
    <table cellspacing="0" style="padding-top: 2px; margin-left: 20px; width: 97%; font-size: 13px;">
        <tr>
            <td style="width: 100%; text-align: right; font-weight: bold;">
                TOTAL A PAGAR: <?php echo number_format($linha_fct_data['valor'], 2, ',', '.').' '.$linha_fct_data['simbolo']; ?>
            </td>
        </tr>
    </table>
    
    <!-- ÚLTIMA área cinzenta da página. Fundo da ÚLTIMA página -->
    <table cellspacing="0" style="position: absolute; bottom: 0px; width: 98%; height: 190px; margin-left: 20px; padding: 2px 4px 8px 4px; font-size: 11.8px; text-align: justify; background-color: #D9DBD9; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">
        <tr>
            <td style="width: 4%; vertical-align: top; font-weight: bold;"> (1) </td>
            <td style="width: 95%;">
                O montante de juros a pagar foi calculado por referência ao presente mês, aplicando a taxa em vigor ao(s) período (s) <br>
                em dívida, nos termos da Portaria n.º 277/2013. <br><br>
            </td>
        </tr>
        <tr>
            <td style="width: 4%; vertical-align: top; font-weight: bold;"> (2) </td>
            <td style="width: 95%;">
                Pagamento Prestação de Acordo Prestacional <br><br>
                Os juros foram calculados com referência aos dias de atraso no pagamento de contribuições anteriores. O pagamento <br>
                da dívida em mês posterior ao atual determina novo apuramento do valor de juros de mora. <br><br><br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="width: 99%; font-size: 13px; font-weight: bold;">
                A(s) referência(s) de pagamento podem demorar até 48 horas a ficarem ativas. <br><br>
                Nota: O presente extrato não prejudica ulteriores apuramentos
            </td>
        </tr>
    </table>
    
</page>
