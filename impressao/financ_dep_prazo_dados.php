<?php

include('../conf/check_pastas.php');

$query_dados_logo = $connection->prepare("SELECT b.nome, b.path_imagem_print, b.print_height, b.print_width FROM banco b INNER JOIN conta c ON b.id=c.id_banco INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id");
$query_dados_logo->execute(array(':id' => $_SESSION['id_empresa']));
$linha_dados_logo = $query_dados_logo->fetch(PDO::FETCH_ASSOC);

$query_moeda = $connection->prepare("SELECT mo.simbolo, mo.nome FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_dados_dp = $connection->prepare("SELECT j.deposito, j.prestacao, j.montante, j.tx_juro, j.tx_irc, j.id_conta, c.num_conta, e.nome, DATE(c.date) AS data_reg, DATE(c.data_lim_v) AS data_lim, TIMESTAMPDIFF(MONTH, c.date, c.data_lim_v) AS prazo FROM juros_dp j INNER JOIN conta c ON j.id_conta=c.id INNER JOIN empresa e ON c.id_empresa=e.id_empresa INNER JOIN movimento m ON c.id=m.id_conta WHERE c.tipo_conta='prazo' AND j.id_juro=:id_juro ORDER BY m.date_reg ASC LIMIT 1");
$query_dados_dp->execute(array(':id_juro' => $_GET['id']));
$linha_dados_dp = $query_dados_dp->fetch(PDO::FETCH_ASSOC);

$prestacao = $linha_dados_dp['prestacao'] - 1;
$query_dados_dp_ant = $connection->prepare("SELECT j.montante, j.prestacao FROM juros_dp j WHERE j.deposito=:dep AND j.id_conta=:id_conta AND j.prestacao=:prestacao LIMIT 1");
$query_dados_dp_ant->execute(array(':dep' => $linha_dados_dp['deposito'], ':id_conta' => $linha_dados_dp['id_conta'], ':prestacao' => $prestacao));
$linha_dados_dp_ant = $query_dados_dp_ant->fetch(PDO::FETCH_ASSOC);

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
            <td style="float: left; width: 50%; vertical-align: middle; height: <?php echo $linha_dados_logo['print_height'] ?>;"><img src="<?php echo '../' . $linha_dados_logo['path_imagem_print']; ?>" style="height: <?php echo $linha_dados_logo['print_height'] ?>; width: <?php echo $linha_dados_logo['print_width'] ?>;"></td>
            <td style="float: right; width: 50%; text-align: right; font-weight: bold;"> Contratual Depósito a Prazo DP Net <br> FICHA DE INFORMAÇÃO NORMALIZADA PARA DEPÓSITOS <br> Depósitos simples, não à ordem </td>
        </tr>
        
        <tr>
            <td style="height: 80px;"></td>
        </tr>
    </table>
    
    <table cellspacing="5" style="width: 70%; font-size: 10pt; line-height: 1.5;">
        <tr>
            <td style="font-weight: bold">
                Nome do Empresa: &nbsp; &nbsp; &nbsp;
            </td>
            <td>
                <?php echo $linha_dados_dp['nome']; ?>
            </td>
        </tr>
        
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        
        <tr>
            <td style="font-weight: bold">
                Número de Conta: &nbsp; &nbsp; &nbsp;
            </td>
            <td>
                <?php echo $linha_dados_dp['num_conta']; ?>
            </td>
        </tr>
        
        <tr>
            <td style="height: 50px;"></td>
        </tr>
        
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Designação
            </td>
            <td style="width: 100%; text-align: justify;">
                Depósito a Prazo DP
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; font-weight: bold">
                Condições de <br> Acesso
            </td>
            <td style="width: 100%; text-align: justify;">
                Depósito dirigido a Clientes Particulares, Empresários em Nome Individual e profissionais liberais, maiores de 18 anos, aderentes do Serviço On-Line Particulares. Produto de constituição e movimentação exclusiva através do Canal On-Line Particulares. <br><br>
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; font-weight: bold">
                Modalidade
            </td>
            <td style="width: 100%; text-align: justify;">
                Depósito a Prazo, sem qualquer risco, que garante capital. O pagamento de juros está garantido para o período do investimento, excepto na mobilização antecipada.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; font-weight: bold">
                Prazo
            </td>
            <td style="width: 100%; text-align: justify;">
                <?php 
                    if ($linha_dados_dp['prazo'] == 1)
                        echo $linha_dados_dp['prazo']; 
                    else
                        echo $linha_dados_dp['prazo'] - $prestacao;
                ?> meses. <br> A data de vencimento será a de <?php echo date("d-m-Y", strtotime($linha_dados_dp['data_lim'])); ?>. <br> A data valor do reembolso do capital será a de <?php echo date("d-m-Y", strtotime($linha_dados_dp['data_lim'])); ?> em caso de não renovação.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Mobilização <br> Antecipada
            </td>
            <td style="width: 100%; text-align: justify;">
                A mobilização parcial ou total do capital, não é permitida antes do final do prazo escolhido.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Renovação
            </td>
            <td style="width: 100%; text-align: justify;">
                Renovação no vencimento.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Moeda
            </td>
            <td style="width: 100%; text-align: justify;">
                <?php echo $linha_moeda['nome']; ?>
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Montante
            </td>
            <td style="width: 100%; text-align: justify;">
                <?php 
                    if ($linha_dados_dp['prestacao'] == 1)
                        echo number_format($linha_dados_dp['montante'], 2, ',', '.').''.$linha_moeda['simbolo'];
                    else
                        echo number_format($linha_dados_dp['montante'] - $linha_dados_dp_ant['montante'], 2, ',', '.').''.$linha_moeda['simbolo']; 
                    ?>
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Reforços
            </td>
            <td style="width: 100%; text-align: justify;">
                Permite reforços.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Taxa de <br> Remuneração
            </td>
            <td style="width: 100%; text-align: justify;">
                <table border=1 style="border: 1px solid black; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px;">
                            TANB (%)
                        </td>
                        <td style="padding: 5px;">
                            <?php echo $linha_dados_dp['tx_juro'].'%'; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Regime de <br> Capitalização
            </td>
            <td style="width: 100%; text-align: justify;">
                Não há capitalização de juros.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Cálculo de Juros
            </td>
            <td style="width: 100%; text-align: justify;">
                 Os Juros são calculados diariamente com uma base de cálculo de Actual/360 com um arredondamento ao cêntimo de <?php echo $linha_moeda['nome']; ?>.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Pagamento de <br> Juros
            </td>
            <td style="width: 100%; text-align: justify;">
                Os juros são pagos no final do prazo contratado no dia <?php echo date("d-m-Y", strtotime($linha_dados_dp['data_lim'])); ?> por crédito na conta D.O.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Regime Fiscal
            </td>
            <td style="width: 100%; text-align: justify;">
                Juros passíveis de IRC à taxa de <?php echo $linha_dados_dp['tx_irc'].'%'; ?>. Regimes fiscais especiais, como por exemplo os decorrentes de isenções fiscais, podem originar diferenças nas taxas mencionadas.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Garantia de <br> Capital
            </td>
            <td style="width: 100%; text-align: justify;">
                Este produto garante a totalidade do capital depositado no vencimento.
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Instituição <br> Depositária
            </td>
            <td style="width: 100%; text-align: justify;">
                <?php echo $linha_dados_logo['nome']; ?>
            </td>
        </tr>
        <tr>
            <td style="background-color: lightgray; padding-top: 10px; padding-bottom: 10px; font-weight: bold">
                Validade das <br> Condições
            </td>
            <td style="width: 100%; text-align: justify;">
                <?php echo date("d-m-Y", strtotime($linha_dados_dp['data_reg'])); ?>
            </td>
        </tr>
    </table>
</page>
