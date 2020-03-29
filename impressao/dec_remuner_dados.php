<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('../conf/check_pastas.php');

$query_dec_rem_data = $connection->prepare("SELECT emp.id_empresa, emp.nome, emp.niss, emp.nipc, DATE(e.`data`) AS data_dec, e.ficheiro, e.mes, e.ano, e.valor, m.simbolo FROM entrega e INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN empresa emp ON e.id_empresa=e.id_empresa INNER JOIN pais p ON emp.pais=p.nome_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE e.id=:fct_id /* AND te.designacao='Fundo de Compensação do Trabalho' */ AND emp.id_empresa=:id_empresa");
$query_dec_rem_data->execute(array(':fct_id' => $_GET['id'], ':id_empresa' => $_SESSION['id_empresa']));
$linha_dec_rem_data = $query_dec_rem_data->fetch(PDO::FETCH_ASSOC);

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
                <img src="../images/SS_dec_remuner_img.png">
            </td>
        </tr>
    </table>
    
    <!-- Primeria secção - Dados do ficheiro -->
    <!-- Primeira subtabela - Primeira linha -->
    <table cellspacing="0" style="width: 45%; margin-top: 35px; margin-left: 35px; padding: 0px 10px 0px 10px; border-top: 1px solid; font-size: 7pt;">
        <tr>
            <td style="width: 100%; text-align: left;">
                Código dos Regimes Contributivos (CRC), aprovado pela Lei n.º <br>
                110/2009, de 16 de setembro e Decreto Regulamentar n.º 1-A/2011, <br>
                de 3 de janeiro, nas suas redações atuais
            </td>
        </tr>
    </table>
    
    <!-- Segunda subtabela - linhas -->
    <table cellspacing="0" style="width: 45%; margin-left: 35px;">
        <tr>
            <td style="width: 100%; padding-left: 10px; border-top: 1px solid;">
                <span style="text-align: left; font-size: 8pt; font-weight: bold;">Data de entrega do ficheiro</span>
                <span>&nbsp;</span>
                <span style="text-align: left; font-size: 9pt;"><?php echo $linha_dec_rem_data['data_dec'] ?></span>
            </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-left: 10px; border-top: 1px solid;">
                <span style="text-align: left; font-size: 8pt; font-weight: bold;">Data de registo</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span style="text-align: left; font-size: 9pt;"><?php echo $linha_dec_rem_data['data_dec'] ?></span>
            </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-left: 10px; border-top: 1px solid;">
                <span style="text-align: left; font-size: 8pt; font-weight: bold;">Nome do ficheiro</span>
                <!-- <span>&nbsp;</span> -->
                <span style="text-align: left; font-size: 7pt;"><?php echo explode('/', $linha_dec_rem_data['ficheiro'])[1] ?></span>
            </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-left: 10px; border-top: 1px solid;">
                <span style="text-align: left; font-size: 8pt; font-weight: bold;">Identificador ficheiro</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span style="text-align: left; font-size: 9pt;">123456789</span>
            </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-left: 10px; border-top: 1px solid; border-bottom: 1px solid;">
                <span style="text-align: left; font-size: 8pt; font-weight: bold;">Estado ficheiro</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span>&nbsp;</span>
                <span style="text-align: left; font-size: 10pt; font-weight: bold;"><i>ACEITE</i></span>
            </td>
        </tr>
    </table>
    
    <!-- Linha de titulo -->
    <table cellspacing="0" style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 100%; vertical-align: middle; text-align: center; font-weight: bold; font-size: 9pt;">
                EXTRATO DE RESUMO
            </td>
        </tr>
    </table>
    
    <!-- Tabela de TOTAL -->
    <table cellspacing="0" style="width: 90%; margin-top: 25px; margin-left: 35px;">
        <tr style="background-color: #bbbbbb;">
            <td style="border: 1px solid black; padding-right: 35px; width: 60%; height: 20px; vertical-align: middle; text-align: center; text-align: right; font-size: 8pt;">
                Total Ficheiro
            </td>
            <td style="border: 1px solid black; width: 20%; height: 20px; vertical-align: middle; text-align: center; text-align: right; font-size: 9pt;">
                <table cellspacing="0" style="width: 100%; padding: 0px 3px 3px 4px;">
                    <tr>
                        <td style="width: 10%; text-align: left;"><?php echo $linha_dec_rem_data['simbolo']; ?></td>
                        <td style="width: 90%; text-align: right;"><?php echo number_format($linha_dec_rem_data['valor'], 2, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
            <td style="border: 1px solid black; width: 20%; height: 20px; vertical-align: middle; text-align: center; text-align: right; font-size: 9pt;">
                <table cellspacing="0" style="width: 100%; padding: 0px 3px 3px 4px;">
                    <tr>
                        <td style="width: 10%; text-align: left;"><?php echo $linha_dec_rem_data['simbolo']; ?></td>
                        <td style="width: 90%; text-align: right;"><?php echo number_format($linha_dec_rem_data['valor'], 2, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Tabela de dados do "cliente", abaixo de linha de TOTAL -->
    <!-- Subtabela de duas linhas -->
    <table cellspacing="0" style="width: 45%; margin-top: 35px; margin-left: 35px; font-weight: bold;">
        <tr>
            <td style="width: 100%; text-align: left; border-top: 1px solid; padding: 2px 10px 2px 10px; font-size: 7pt;">
                N.º DE IDENTIFICAÇÃO DE SEGURANÇA SOCIAL
            </td>
        </tr>
        <tr>
            <td style="width: 100%; text-align: left; border-top: 1px solid; padding: 2px 10px 2px 10px; font-size: 8pt;">
                <?php echo $linha_dec_rem_data['niss']; ?>
            </td>
        </tr>
    </table>
    <!-- Subtabela de linhas restantes -->
    <table cellspacing="0" style="width: 45%; margin-left: 35px;">
        <tr>
            <td style="width: 100%; padding-left: 10px; border-top: 1px solid;">
                <table cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="width: 10%; text-align: left; vertical-align: top; font-weight: bold; font-size: 7pt;">NOME</td>
                        <td style="width: 90%; text-align: left; padding: 0px 40px 0px 40px; font-size: 8pt;"><?php echo $linha_dec_rem_data['nome']; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 100%; padding-left: 10px; border-top: 1px solid; border-bottom: 1px solid;">
                <table cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; text-align: left; font-weight: bold; font-size: 7pt;">N.º DE IDENTIFICAÇÃO FISCAL</td>
                        <td style="width: 20%; text-align: left; padding: 0px 40px 0px 40px; font-size: 8pt;"><?php echo $linha_dec_rem_data['nipc']; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Tabela de valores - última tabela -->
    <table cellspacing="0" style="width: 90%; margin-top: 35px; margin-left: 35px; font-size: 8pt; text-align: center;">
        <tr style="width: 100%; background-color: #bbbbbb; font-weight: bold;">
            <td style="height: 20px; width: 17%; border: 1px solid;">Estabelecimento</td>
            <td style="width: 23%; border: 1px solid;">Ano/Mês de Referência</td>
            <td style="width: 17%; border: 1px solid;">Taxa</td>
            <td style="width: 22%; border: 1px solid;">Total de Remunerações</td>
            <td style="width: 21%; border: 1px solid;">Total de Contribuições</td>
        </tr>
        <tr>
            <td style="height: 20px; width: 17%; border: 1px solid; font-size: 9pt;">1</td>
            <td style="width: 23%; border: 1px solid; font-size: 9pt;">
                <?php echo $linha_dec_rem_data['mes'] < 10 ? $linha_dec_rem_data['ano'].'-0'.$linha_dec_rem_data['mes'] : $linha_dec_rem_data['ano'].'-'.$linha_dec_rem_data['mes']; ?>
            </td>
            <td style="width: 17%; border: 1px solid; font-size: 9pt;">34,75 %</td>
            <td style="width: 22%; border: 1px solid;">
                <table cellspacing="0" style="width: 100%; padding: 5px 5px 5px 5px;">
                    <tr>
                        <td style="width: 10%; text-align: left; font-size: 9pt;"><?php echo $linha_dec_rem_data['simbolo']; ?></td>
                        <td style="width: 90%; text-align: right; font-size: 9pt;"><?php echo number_format($linha_dec_rem_data['valor'], 2, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
            <td style="width: 21%; border: 1px solid;">
                <table cellspacing="0" style="width: 100%; padding: 5px 5px 5px 5px;">
                    <tr>
                        <td style="width: 10%; text-align: left; font-size: 9pt;"><?php echo $linha_dec_rem_data['simbolo']; ?></td>
                        <td style="width: 90%; text-align: right; font-size: 9pt;"><?php echo number_format($linha_dec_rem_data['valor'], 2, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="width: 100%; background-color: #bbbbbb;">
            <td colspan="3" style="height: 20px; width: 57%; border: 1px solid; padding-right: 5px; font-weight: bold; text-align: right;">Total de Remunerações/Contribuições</td>
            <td style="width: 22%; border: 1px solid;">
                <table cellspacing="0" style="width: 100%; padding: 5px 5px 5px 5px;">
                    <tr>
                        <td style="width: 10%; text-align: left; font-size: 9pt;"><?php echo $linha_dec_rem_data['simbolo']; ?></td>
                        <td style="width: 90%; text-align: right; font-size: 9pt;"><?php echo number_format($linha_dec_rem_data['valor'], 2, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
            <td style="width: 21%; border: 1px solid;">
                <table cellspacing="0" style="width: 100%; padding: 5px 5px 5px 5px;">
                    <tr>
                        <td style="width: 10%; text-align: left; font-size: 9pt;"><?php echo $linha_dec_rem_data['simbolo']; ?></td>
                        <td style="width: 90%; text-align: right; font-size: 9pt;"><?php echo number_format($linha_dec_rem_data['valor'], 2, ',', '.'); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</page>