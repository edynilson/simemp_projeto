<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 10:56:16
*/

include('../conf/check_pastas.php');

$query_dados_logo = $connection->prepare("SELECT b.path_imagem_print, b.print_height, b.print_width FROM banco b INNER JOIN conta c ON b.id=c.id_banco INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id");
$query_dados_logo->execute(array(':id' => $_SESSION['id_empresa']));
$linha_dados_logo = $query_dados_logo->fetch(PDO::FETCH_ASSOC);

$query_leas_max = $connection->prepare("SELECT le.id_leasing AS leas_max, date_format(p.data_limit_pag, '%d-%m-%Y') AS data_max FROM leasing le INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN pagamento p ON le.id_leasing=p.id_leasing WHERE emp.ativo='1' AND le.leas=:leas AND emp.id_empresa=:id_empresa ORDER BY le.id_leasing DESC LIMIT 1");
$query_leas_max->execute(array(':leas' => $_GET['leas'], ':id_empresa' => $_SESSION['id_empresa']));
$linha_max = $query_leas_max->fetch(PDO::FETCH_ASSOC);

$query_leas_min = $connection->prepare("SELECT le.id_leasing AS leas_min, date_format(p.data_limit_pag, '%d-%m-%Y') AS data_min FROM leasing le INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN pagamento p ON le.id_leasing=p.id_leasing WHERE emp.ativo='1' AND le.leas=:leas AND emp.id_empresa=:id_empresa ORDER BY le.id_leasing ASC LIMIT 1");
$query_leas_min->execute(array(':leas' => $_GET['leas'], ':id_empresa' => $_SESSION['id_empresa']));
$linha_min = $query_leas_min->fetch(PDO::FETCH_ASSOC);

$query_leasing = $connection->prepare("SELECT emp.id_empresa, emp.nome AS nome, emp.morada, emp.nipc, emp.localidade, cs.capital_social_monetario AS cap_soc, le.descricao_bem, le.n_per, le.prestacao_c_iva, date_format(le.data_leasing,'%d-%m-%Y') AS data_leasing, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, le.data_leasing AS data_bruto, p.valor, c.nib FROM pagamento p INNER JOIN leasing le ON p.id_leasing=le.id_leasing INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa INNER JOIN capital_social cs ON emp.id_empresa=cs.id_empresa WHERE emp.ativo='1' AND le.id_leasing=:id_leasing");
$query_leasing->execute(array(':id_leasing' => $linha_max['leas_max']));
$linha_leasing = $query_leasing->fetch(PDO::FETCH_ASSOC);

$query_valor_leas = $connection->prepare("SELECT le.capital_pendente FROM pagamento p INNER JOIN leasing le ON p.id_leasing=le.id_leasing INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa INNER JOIN capital_social cs ON emp.id_empresa=cs.id_empresa WHERE emp.ativo='1' AND le.id_leasing=:id_leasing");
$query_valor_leas->execute(array(':id_leasing' => $linha_min['leas_min']));
$linha_v_leas = $query_valor_leas->fetch(PDO::FETCH_ASSOC);

$query_despesa = $connection->prepare("SELECT m.debito FROM movimento m WHERE m.descricao LIKE :descricao AND m.data_op=:data_op");
$query_despesa->execute(array(':descricao' => "%Comissão estudo e montagem por leasing%", ':data_op' => $linha_leasing['data_bruto']));
$linha_despesa = $query_despesa->fetch(PDO::FETCH_ASSOC);

$query_v_res = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_v_res->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Valor residual'));
$linha_v_res = $query_v_res->fetch(PDO::FETCH_ASSOC);

$query_cf = $connection->prepare("SELECT emp.id_empresa, emp.nipc, emp.nome, emp.morada, emp.cod_postal, emp.localidade, emp.pais, emp.email FROM empresa emp WHERE emp.ativo='1' AND emp.id_empresa=:id_cf");
$query_cf->execute(array(':id_cf' => 2));
$linha_cf = $query_cf->fetch(PDO::FETCH_ASSOC);

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

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
            <td style="height: 20px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width: 100%; vertical-align: middle; height: <?php echo $linha_dados_logo['print_height'] ?>;"><img src="<?php echo '../' . $linha_dados_logo['path_imagem_print']; ?>" style="height: <?php echo $linha_dados_logo['print_height'] ?>; width: <?php echo $linha_dados_logo['print_width'] ?>;"></td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 16pt; font-weight: bold;">
        <tr>
            <td style="height: 50px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: center;">Contrato de locação financeira mobiliária nº <?php echo $_GET['leas']; ?></td>
            <td style="padding-left: 10px; height: 80px; vertical-align: top;">&nbsp;</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 10px; width: 100%">Entre: 1º outorgante</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"><?php echo $linha_leasing['nome']; ?>, com sede em <?php echo $linha_leasing['morada']; ?>, pessoa coletiva nº <?php echo $linha_leasing['nipc']; ?>, com o capital social de <?php echo $linha_leasing['cap_soc']; ?> <?php echo $linha_moeda['simbolo']; ?>, matriculada na Conservatória do Registo Comercial de <?php echo $linha_leasing['localidade']; ?>, sob o nº <?php echo $linha_leasing['id_empresa']; ?>, adiante designada por locatário;</td>
        </tr>
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%">e: 2º outorgante</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"><?php echo $linha_cf['nome']; ?>, com sede em <?php echo $linha_cf['morada']; ?>, pessoa coletiva nº <?php echo $linha_cf['nipc']; ?>, com o capital social de 15.000.000 <?php echo $linha_moeda['simbolo']; ?>, matriculada na Conservatória do Registo Comercial de <?php echo $linha_cf['localidade']; ?>, sob o nº <?php echo $linha_cf['id_empresa']; ?>, adiante designada por locador;</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">é celebrado o presente contrato de locação financeira mobiliária, sujeito à sua disciplina específica, às disposições da lei civil aplicáveis, às competentes instruções do Banco de Portugal e às seguintes Condições Particulares e Gerais.</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 16pt; font-weight: bold;">
        <tr>
            <td style="height: 50px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: center;">Condições Particulares</td>
            <td style="padding-left: 10px; height: 70px; vertical-align: top;">&nbsp;</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">1. Descrição dos bens</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">Equipamento "<?php echo $linha_leasing['descricao_bem']; ?>", conforme fatura pró-forma anexa que faz parte integrante do contrato e locação financeira mobiliária.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">2. Fornecedor(es)</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"><?php echo $linha_cf['nome']; ?></td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">3. Valor do contrato / preço de aquisição</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">Com o acordo do locador, o(s) preço(s) do(s) bem(ns) poderá(ão) sofrer alteração(ões) até à data de início da locação financeira por aplicação de cláusulas nesse sentido estabelecidas entre o locatário e o(s) fornecedor(es), ou por alteração das taxas de câmbio ou do regime tributário.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">4. Prazo</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"><?php echo $linha_leasing['n_per']; ?> (meses)</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">5. Número, periodicidade e tipo de rendas</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">Total de <?php echo $linha_leasing['n_per']; ?> prestações, de periodicidade mensal, de termos postecipados.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">6. Montante das rendas</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;">
                <table cellspacing="0" border="1" style="width: 100%; font-size: 14pt;">
                    <tr>
                        <td style="width: 20%; padding: 5px; text-align: center;">Nº da renda</td>
                        <td style="width: 30%; padding: 5px; text-align: center;">Valor</td>
                    </tr>
                    <tr>
                        <td style="width: 20%; padding: 5px; text-align: center;">1ª a <?php echo $linha_leasing['n_per']; ?>ª</td>
                        <td style="width: 30%; padding: 5px; text-align: center;"><?php echo number_format($linha_leasing['prestacao_c_iva'], 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">7. Valor residual</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"><?php echo $linha_v_res['valor']; ?><?php echo $linha_v_res['simbolo']; ?>, no valor de <?php echo (($linha_v_leas['capital_pendente'] / (100 - $linha_v_res['valor'])) * 100) * ($linha_v_res['valor'] / 100); ?> <?php echo $linha_moeda['simbolo']; ?></td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">8. Despesas do contrato</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"><?php echo $linha_despesa['debito']; ?> <?php echo $linha_moeda['simbolo']; ?><br>Sobre os valores indicados nos n<sup style="vertical-align: top; font-size: 0.6em;">os</sup> 6, 7 e 8 destas Condições Particulares, incidirá o IVA, assim como quaisquer outros encargos que, nos termos da legislação em vigor, sobre eles recaiam no momento do seu vencimento.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">9. Local de utilização do bem</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">Em qualquer parte do território nacional ou estrangeiro.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">10. Início da locação financeira</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">A locação financeira terá início com a receção, nas instalações do locador, de toda a documentação necessária para a formalização do contrato, designadamente, o contrato de locação financeira, o auto de receção do bem, a fatura do fornecedor, garantias acordadas e seguros. <br>Para efeito de registo, este contrato tem o seu início em <?php echo $linha_min['data_min']?> e o seu termo em <?php echo $linha_max['data_max']?>.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">11. Data de vencimento das rendas</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">As rendas vencer-se-ão, de acordo com a periodicidade indicada no nº 5 destas Condições Particulares:
                <ul style="list-style-type: disc; width: 90%;">
                    <li style="padding-left: 15px; padding-bottom: 10px;">No dia 5, se a locação financeira se iniciar até ao dia 15 do mês, inclusive;</li>
                    <li style="padding-left: 15px;">No dia 20, se a locação financeira se iniciar a partir do dia 16 do mês, inclusive.</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">12. Débito das rendas, valor residual (se aplicável) e outros encargos</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">Para efeito do pagamento das rendas ou de quaisquer valores devidos ao locador por força deste contrato de locação financeira, o locatário autoriza a que seja debitada a conta, cujo NIB é o seguinte: <?php echo $linha_leasing['nib']; ?>.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">13. Indexação das rendas</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">As rendas poderão ser alteradas sempre que a taxa Euribor a 3 meses sofra uma variação mínima de um quarto de ponto percentual, relativamente à data de fixação das condições, ou da última indexação, com arredondamento para um oitavo de ponto percentual.</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">14. Seguros</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">Nos termos da cláusula 8ª das Condições Gerais, o locatário obriga-se a subscrever junto de uma companhia de seguros, por todo o período de vigência do contrato, uma apólice de seguros que cubra os riscos assinalados no "Certificado de Seguros".</td>
        </tr>
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; font-weight: bold; font-style: italic;">15. Garantias</td>
        </tr>
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">Livrança subscrita pelo locatário e avalizada pelos sócios e cônjuge.</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 16pt; font-weight: bold;">
        <tr>
            <td style="height: 50px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: center;">Condições Gerais</td>
            <td style="padding-left: 10px; height: 70px; vertical-align: top;">&nbsp;</td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 1ª</span> — Objeto do contrato</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">O presente contrato tem por objeto a locação financeira do bem identificado no nº 1 das Condições Particulares.</li>
        <li style="padding-left: 15px;">O locatário declara ter escolhido de sua livre vontade o bem a locar e negociou com o(s) respectivo(s) fornecedor(s) o(s) preço(s) de aquisição, suas caraterísticas, especificações técnicas, garantias, local, modalidade e prazo de entrega e restantes termos e condições de venda.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 2ª</span> — Entrega e recepção do bem</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">O locador confere mandato ao locatário, que o aceita, para proceder à receção do bem.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">As despesas de transporte e respetivo seguro, montagem e instalação do bem serão por conta e responsabilidade do locatário.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Com a entrega do bem deverá ser assinado pelo fornecedor e locatário um "Auto de Receção" certificando que o bem está de acordo com a encomenda, se encontra em bom estado de funcionamento e corresponde às exigências do locatário.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O "Auto de Receção" deverá ser remetido ao locador no prazo máximo de 8 dias após a sua assinatura.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Se o "auto de receção" não for recebido pelo locador no prazo de 15 dias após a data prevista de entrega do bem, o locador poderá resolver o contrato.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Se o locatário recusar a receção do bem, deverá informar por escrito e de imediato o locador desse fato, indicando os motivos da recusa.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Recebida a informação da recusa, o locador poderá resolver o contrato ou acordar com o locatário um novo prazo para entrega do mesmo bem ou de um bem alternativo.</li>
        <li style="padding-left: 15px;">Resolvido o contrato nos termos dos anteriores nº 5 ou 7, o locatário deverá reembolsar o locador de todas as despesas por este incorridas com a celebração e execução do contrato, incluindo as despesas de formalização referidas no nº 8 das Condições Particulares, acrescido dos correspondentes juros calculados à taxa prevista no nº 4 da cláusula 4ª destas Condições Gerais.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 3ª</span> — Início e prazo do contrato</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">O presente contrato entra em vigor na data da sua assinatura.</li>
        <li style="padding-left: 15px;">O prazo da locação financeira e o seu início são os fixados nas Condições Particulares.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 4ª</span> — Rendas</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">Com a locação do bem é devido o pagamento, pelo locatário ao locador, de uma renda de valor, periodicidade e data definidas nas Condições Particulares.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">As rendas serão liquidadas por débito da conta que o locatário manterá junto do locador e cuja identificação é efetuada nas Condições Particulares.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Nos casos em que o locador, antes da entrada em vigor da locação, tenha de efetuar adiantamentos ao fornecedor ou fabricante do bem, o locatário pagará ao locador, juntamente com a primeira renda, uma quantia destinada a compensar o locador do custo financeiro da antecipação, e que será calculada pela aplicação da taxa de locação financeira ao valor dos pagamentos realizados antes da entrega do bem.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Sempre que o locatário incorrer em mora no pagamento de qualquer renda ou de qualquer outra quantia em dívida, sem prejuízo do disposto na cláusula 11ª destas Condições Gerais, serão devidos pelo locatário juros, acrescidos de sobretaxas, no montante máximo permitido por lei.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O valor das rendas poderá ser alterado nos seguintes casos:
            <ul style="list-style-type: lower-alpha; width: 90%;">
                <li style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">Em consequência da alteração do preço total de aquisição do bem, acordada entre o locatário e o fornecedor e aceite, por escrito, pelo locador;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Se a aquisição do bem tiver sido financiada em moeda estrangeira e a alteração da cotação do euro face àquela moeda determinar o ajustamento das rendas;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Sempre que a taxa de juro de referência, indicada nas Condições Particulares para efeitos de indexação das rendas, sofrer variações acumuladas superiores ou iguais a um valor fixado nas Condições Particulares;</li>
                <li style="padding-left: 15px;">Caso se verifique uma alteração do capital em dívida do contrato, designadamente como resultado de um pagamento adicional do locatário.</li>
            </ul>
        </li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O pagamento adicional referido na alínea d) do nº anterior só poderá ser efetuado caso se verifiquem cumulativamente as seguintes condições:
            <ul style="list-style-type: lower-alpha; width: 90%;">
                <li style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">Não haja qualquer impedimento legal para o efeito;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">O locatário manifeste tal intenção com uma antecedência não inferior a trinta dias;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">O pagamento coincida com uma das prestações de renda;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">O locatário pague, para além da verba constante no preçário para fazer face aos encargos suportados pelo locador, a título de indemnização, 1% sobre o valor do pagamento adicional.</li>
            </ul>
        </li>
        <li style="padding-left: 15px;">Caso o indexante referido nas Condições Particulares deixe de ser publicado, poderá o locador proceder à sua substituição por outro indexante determinado por Lei ou por aviso do Banco de Portugal ou, na ausência de disposição legal ou regulamentar, por uma taxa de juro representativa dos mercados monetário e/ou de capitais.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 5ª</span> — Propriedade do bem</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">O locador é proprietário exclusivo do bem, não podendo, em consequência, o locatário, sem prévia autorização daquele, ceder a sua utilização, aliená-lo, onerá-lo, sublocá-lo, deslocá-lo ou dele dispor por qualquer forma que não esteja expressamente prevista neste contrato ou na Lei.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O locatário é responsável pela colocação, no bem locado, em local visível, de placa indicativa da propriedade do locador, devendo manter essa placa em bom estado durante o prazo de locação, por forma que a terceiros não seja permitida dúvida quanto à propriedade do mesmo.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Se o locatário der a terceiros em garantia real o conjunto das instalações ou dos bens em que se encontra integrado o bem locado, deverá ser expressamente mencionado que este se encontra excluído dessa garantia.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Caso o locatário não seja o proprietário das instalações onde serão implantados os bens locados, deverá aquele prevenir o respetivo proprietário de que o bem é propriedade do locador.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Em caso de penhora, furto, roubo, requisição ou confisco do bem locado, o locatário deverá avisar o locador nas 48 horas seguintes à ocorrência de qualquer um desses fatos e proceder, por sua conta e responsabilidade, às diligências necessárias à salvaguarda do(s) direito(s) do locador.</li>
        <li style="padding-left: 15px;">O locatário deve avisar imediatamente o locador sempre que tenha conhecimento de vícios do bem ou saiba que o ameaça qualquer perigo ou que terceiros se arrogam direitos sobre ele.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 6ª</span> — Utilização e manutenção do bem</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">Salvo se diferente for estabelecido nas Condições Particulares, entende-se como local de utilização do Bem a Sede ou a morada do Locatário, ou, no caso de viaturas, em todo o Território Nacional, sem prejuízo da transposição de Fronteiras.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Durante toda a vigência do presente contrato, o locatário obriga-se a:
            <ul style="list-style-type: lower-alpha; width: 90%;">
                <li style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">Respeitar as leis e regulamentos em vigor relativos à detenção e utilização do bem locado;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Fazer um uso normal do bem e de acordo com as instruções dadas pelo fornecedor ou fabricante;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Manter o bem em bom estado de funcionamento, suportando da sua conta todas as despesas de conservação e reparação necessárias, incluindo as que resultem de sinistro.</li>
            </ul>
        </li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O locatário não pode introduzir modificações no bem locado, nem alterar a sua afetação, sem acordo escrito do locador, o qual pode fazer suas, sem ficar obrigado a qualquer compensação ou indemnização, as peças ou outros elementos acessórios, incorporados no bem pelo locado.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Se o locatário se encontrar impossibilitado de utilizar o bem, por qualquer razão alheia à vontade do locador, incluindo força maior, não poderá exigir deste indemnização ou redução da renda.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Durante a vigência do contrato, o locador ou seu representante, pode verificar a qualquer momento o estado e a utilização dada pelo locatário ao bem, sem prejuízo do respeito devido ao segredo profissional ou outro interesse atendível do locatário.</li>
        <li style="padding-left: 15px;">Fica expressamente proibida qualquer modificação e/ou transformação nas caraterísticas interiores e/ou exteriores das viaturas dadas em locação financeira, à exceção da respetiva cor e/ou qualquer pintura do logótipo ou inscrição da denominação da empresa do locatário sendo da conta deste todas as diligências necessárias para o efeito.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 7ª</span> — Importação do bem pelo locatário</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">Todas as formalidades inerentes ao processo de importação e ao desembaraço aduaneiro, assim como o pagamento de quaisquer importâncias devidas pela importação do bem descrito nas Condições Particulares são da exclusiva responsabilidade do locatário.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O preço do bem constante das Condições Particulares é fixado com base na taxa de câmbio do dia da emissão do contrato e no valor em divisas que figura na fatura pró-forma do fornecedor ou fabricante estrangeiro.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Após a sua entrega e desalfandegamento, o bem será faturado pelo locatário ao locador, que ficará proprietário do mesmo. O valor da fatura definitiva corresponderá à soma, em <?php echo $linha_moeda['nome']; ?>, de todos os montantes desembolsados pelo locatário a favor do fornecedor ou fabricante estrangeiro, eventualmente acrescido das despesas de importação. O locatário deverá enviar ao locador, juntamente com a fatura do bem acima referida, todos os documentos relacionados com a operação de importação, nomeadamente a cópia da fatura definitiva do fornecedor ou fabricante estrangeiro, justificação dos pagamentos a este efetuados e cópia dos documentos aduaneiros.</li>
        <li style="padding-left: 15px;">O preço definitivo do bem será o que constar dessa fatura, substituindo o indicado nas Condições Particulares, sendo recalculados o montante das rendas e o valor residual.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 8ª</span> — Responsabilidade, riscos e seguros</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">A partir da data da receção total ou parcial do bem e até à sua devolução, o locatário, na sua qualidade de fruidor e de defensor da integridade do bem locado, é o único responsável pelos prejuízos causados pelo bem, qualquer que seja a sua causa, assim como pelos danos produzidos no bem por qualquer motivo.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O Locatário obriga-se em consequência a subscrever, junto de uma companhia de seguros que mereça o acordo do locador, apólices de seguro que cubram, por um lado, a sua responsabilidade civil ilimitada e, por outro lado, o bem locado contra todos os riscos, nomeadamente os de incêndio, roubo, inundação, explosão, raio e destruição, pelo seu valor de reposição.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Para efeitos dos n<sup style="vertical-align: top; font-size: 0.6em;">os</sup> 1 e 2, deverá o locatário fazer prova perante o locador, que efetuou os seguros exigidos no nº 14 das Condições Particulares.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">As apólices devem mencionar expressamente que o bem é propriedade exclusiva do locador e que, em caso de sinistro, qualquer que seja a sua natureza, a indemnização deverá ser paga diretamente pela companhia de seguros ao locador ou a quem este indicar, e ainda que a companhia de seguros renuncia a qualquer recurso contra o locador.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">No prazo de dez dias após a entrega do bem, o locatário deverá entregar ao locador uma cópia das apólices e respetivos adicionais.</li>
        <li style="padding-left: 15px;">O locatário obriga-se a manter as apólices durante todo o prazo do contrato, pagar diretamente os prémios à companhia do seguros ou a quem o locador indicar e comprovar a realização destes pagamentos sob simples pedido do locador.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 9ª</span> — Procedimento em caso de sinistro do bem</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">No caso de sinistro sofrido pelo bem locado, o locatário deve, no prazo máximo de 48 horas e por carta registada com aviso de receção, informar o locador e notificar a companhia de seguros, solicitando desde logo a competente peritagem.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Se o sinistro for de perda parcial, o locatário deve, depois da peritagem, confirmar que o bem é reparável, mandar proceder à reparação, suportando as respetivas despesas; o locatário tem direito a receber do locador, em face do justificativo da reparação, qualquer indemnização que este tenha recebido da companhia de seguros ou a recebê-la diretamente da seguradora, caso o locador assim autorize.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Se o sinistro for de perda total, confirmada por peritagem, considerar-se-á impossibilidade definitiva de cumprimento do contrato e, em consequência:
            <ul style="list-style-type: lower-alpha; width: 90%;">
                <li style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">O locatário pagará ao locador os montantes das rendas vincendas, eventualmente indexadas, e do valor residual, atualizados com base na taxa de locação em vigor, acrescidos dos montantes das rendas vencidas e não pagas e respetivos juros de mora calculados nos termos do nº 4 da cláusula 4ª destas Condições Gerais;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">O locador entregará ao locatário a indemnização que venha a receber da companhia de seguros, após dedução de todas as importâncias que lhe sejam devidas pelo locatário, designadamente as resultantes da aplicação da alínea anterior, podendo o locador autorizar o locatário a receber a indemnização diretamente da seguradora, caso lhe tenham sido regularizados previamente os montantes referidos na alínea a).</li>
            </ul>
        </li>
        <li style="padding-left: 15px;">Se o sinistro tiver afetado apenas uma parte do bem, pode a locação, a pedido do locatário, subsistir, tendo por objeto as partes do bem não afetadas; neste caso, as rendas vincendas e o valor residual deverão ser recalculados em função da indemnização paga pela companhia de seguros ao locador.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 10ª</span> — Termo do contrato e opção de compra do bem</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">No termo do contrato, e caso não existam débitos em atraso para com o locador, poderá o locatário adquirir o bem pelo valor residual fixado nas condições particulares, acrescido do imposto que for devido, devendo para o efeito declarar essa intenção com uma antecedência não inferior a 3 meses relativamente ao termo da locação.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Em alternativa, poderá o locatário solicitar a renovação do contrato de locação, em condições a negociar com o locador, devendo tal solicitação ser efetuada com uma antecedência mínima de 3 meses em relação ao termo do contrato.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Não havendo aquisição do bem nem renovação do contrato, deve o locatário devolver por sua conta e risco, nas modalidades, lugares e termos indicados pelo locador, o bem dotado de todas as componentes e acessórios, incluindo os incorporados durante o período do contrato, em bom estado de conservação e manutenção, salvo as deteriorações inerentes a uma utilização normal.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O estado do bem será verificado e certificado no "auto de restituição" assinado pelo locador e pelo locatário.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Caso a restituição do bem não se verifique no prazo de 15 dias, aplica-se o disposto do nº 6 da Cláusula 12ª;</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O locatário poderá antecipar o exercício do direito de opção de compra a que se refere o nº 1 desta cláusula desde que se verifiquem, cumulativamente, as seguintes condições:
            <ul style="list-style-type: lower-alpha; width: 90%;">
                <li style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">Não exista, à data, qualquer impedimento legal ao exercício de tal direito;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Não se encontre em mora relativamente a qualquer das suas obrigações;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Notifique o Locador da sua intenção com uma antecedência não inferior a 3 meses;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Pague ao locador o valor de compra antecipada.</li>
            </ul>
        </li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Considera-se valor de compra antecipada o valor de todas as rendas vincendas e do valor residual, atualizado à taxa de juro implícita no contrato de locação financeira deduzida de dois pontos percentuais.</li>
        <li style="padding-left: 15px;">Serão de conta do locatário todos os encargos inerentes ou derivados da compra antecipada do bem.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 11ª</span> — Mora no pagamento das rendas</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">A mora no pagamento de uma prestação de renda por um prazo superior a sessenta dias permite ao locador resolver o contrato, com as consequências previstas na cláusula 12ª.</li>
        <li style="padding-left: 15px;">O Locatário pode precludir o direito à resolução por parte do locador, procedendo ao pagamento do montante em dívida, acrescido de 50% no prazo de oito dias contados a partir da data em que for notificado pelo locador da resolução do contrato.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 12ª</span> — Resolução do contrato</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">O contrato pode ser Resolvido por qualquer das partes, nos termos gerais, com fundamento nos incumprimentos das obrigações da outra parte, designadamente em resultado da mora no pagamento das rendas, conforme previsto na cláusula 11ª.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">O contrato pode ainda ser Resolvido pelo locador, nos casos de dissolução ou liquidação da sociedade locatária; verificação de qualquer dos fundamentos de declaração de falência do locatário; transmissão gratuita ou onerosa do estabelecimento comercial onde se encontra instalado o bem locado ou cessão da sua exploração; venda judicial dos seus bens; prestação de falsas informações ou informações inexatas do locatário ao locador; processo especial de recuperação de empresas.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Verificados os pressupostos referidos nos n<sup style="vertical-align: top; font-size: 0.6em;">os</sup> anteriores, a resolução do contrato por iniciativa do locador considera-se efetuada, sem qualquer outra formalidade, no oitavo dia posterior à notificação, nesse sentido, pelo locador ou locatário, por meio de carta registada.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">A notificação prevista no nº anterior considera-se efetuada desde que tenha sido enviada para a última morada que o locatário tenha indicado ao locador e no quinto dia útil posterior ao da data do registo do correio.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Em consequência da resolução efetuada nos termos dos n<sup style="vertical-align: top; font-size: 0.6em;">os</sup> anteriores, fica o locatário obrigado a:
            <ul style="list-style-type: lower-alpha; width: 90%;">
                <li style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">Restituir ao locador no prazo de oito dias, o bem locado em bom estado de funcionamento no local por este indicado, correndo os encargos e riscos de restituição, nomeadamente o seguro, por conta do locatário;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Pagar as prestações vencidas e não pagas, acrescidas dos juros de mora contados desde a data do seu vencimento até à data do pagamento efetivo e calculadas à taxa fixada nos termos do nº 4 da Cláusula 4ª;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Pagar, a título de indemnização, uma importância igual a 20% da soma das rendas ainda não vencidas à data da resolução, com valor residual acrescido dos juros de mora contados desde a data de resolução até à data do pagamento efetivo e calculados à taxa fixada nos termos do nº 4 da cláusula 4ª.</li>
            </ul>
        </li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Se, findo o contrato por resolução ou pelo decurso do prazo sem ter sido exercido o direito de compra, o locatário não proceder à restituição do bem dentro do prazo fixado e no local indicado pelo locador, pode este requerer ao Tribunal as medidas necessárias à apreensão do bem, designadamente Providência Cautelar, consistente na sua entrega imediata e no cancelamento do respetivo registo, caso se trate de bem sujeito a registo.</li>
        <li style="padding-left: 15px; padding-bottom: 10px;">Em alternativa à resolução do contrato, prevista nos n<sup style="vertical-align: top; font-size: 0.6em;">os</sup> anteriores, poderá o locador, sem prejuízo do direito à indemnização estipulada na alínea c) do nº 5 desta cláusula, exercer os seus direitos de crédito sobre o locatário que se considerarão todos vencidos no momento em que ocorra algum dos pressupostos referidos nos n<sup style="vertical-align: top; font-size: 0.6em;">os</sup> 1 e 2 desta cláusula. Nesta hipótese, todos os créditos vencerão juros a partir desse momento à taxa referida no nº 4 da Cláusula 4ª.</li>
        <li style="padding-left: 15px;">Quando a resolução for devida a sinistro, observar-se-á o disposto na Cláusula 9ª.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 13ª</span> — Impostos e taxas</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px;">O locatário é o único responsável pelo pagamento do I.V.A. ou quaisquer outros impostos, taxas ou encargos que incidam ou venham a incidir sobre a renda ou onerem o contrato de locação financeira.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 14ª</span> — Encargos, registos e publicidade</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">Todos os encargos, seja qual for a sua natureza, decorrentes da celebração deste contrato, serão suportados pelo locatário, designadamente as despesas de formalização do mesmo, de montante indicado no nº 8 das Condições Particulares e a liquidar na data da sua celebração.</li>
        <li style="padding-left: 15px;">Tratando-se de bem sujeito a registo, o contrato deverá ser inscrito na Conservatória competente, a requerimento do locador, devendo observar-se o seguinte:
            <ul style="list-style-type: lower-alpha; width: 90%;">
                <li style="padding-left: 15px; padding-top: 10px; padding-bottom: 10px;">São da conta e responsabilidade do locatário todas as diligências necessárias junto da Conservatória do Registo Automóvel, Direcção Geral de Viação, Direcção Geral dos Transportes Terrestres ou quaisquer outras entidades oficiais com vista à obtenção de licenças e à realização dos registos necessários à utilização do bem objeto deste contrato;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">O locatário obriga-se a não utilizar o bem objeto deste contrato enquanto não obtiver toda a documentação necessária para esse efeito;</li>
                <li style="padding-left: 15px; padding-bottom: 10px;">Todas as despesas com a prática dos atos mencionados na alinea a), assim como todos os impostos (nomeadamente o de circulação), taxas, licenças, multas e outras prestações devidas a quaisquer entidades públicas e resultantes da utilização do bem objeto deste contrato são da exclusiva responsabilidade do locatário;</li>
                <li style="padding-left: 15px;">A não satisfação atempada das formalidades referidas na alínea a), assim como de qualquer das prestações referidas na alínea c), são havidas como fundamento de resolução do presente contrato, com as consequências previstas na Cláusula 12ª destas Condições Gerais.</li>
            </ul>
        </li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 15ª</span> — Garantias</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">Como garantia do bom e integral cumprimento dos créditos de rendas e dos outros encargos ou eventuais indemnizações devidas pelo locatário, poderão ser constituídas a favor do locador as garantias reais ou pessoais julgadas necessárias e identificadas no nº 15 das Condições Particulares.</li>
        <li style="padding-left: 15px;">Poderão igualmente ser exigidas garantias adequadas, nomeadamente garantia bancária, sempre que o locador efetue adiantamentos ao fornecedor antes do início do contrato de locação financeira.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 16ª</span> — Notificações</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px;">As notificações ou comunicações entre Locador e Locatário serão consideradas válidas e eficazes se forem efectuadas para os respectivos domicílios ou sedes sociais identificados
            neste Contrato ou para as que, entretanto, sejam informadas por escrito à outra parte.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 17ª</span> — Processamento de dados informatizados</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px;">O locatário autoriza o locador a proceder ao processamento automático dos dados pessoais que lhe foram transmitidos no âmbito da celebração do presente contrato e a conservá-los, utilizá-los e comunicá-los ao Banco de Portugal, à Apelease – Associação Portuguesa das Empresas de Leasing, a outras instituições de crédito, sociedades financeiras e companhias seguradoras, nos termos legais.</li>
        <li style="padding-left: 15px;">O locatário poderá, a seu pedido, consultar e retificar os seus dados pessoais constantes dos ficheiros informáticos do locador.</li>
    </ul>
    <table cellspacing="0" style="width: 100%; font-size: 14pt;">
        <tr>
            <td style="height: 10px;"></td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%;"><span style="font-weight: bold;">Cláusula 18ª</span> — resolução de litígios</td>
        </tr>
    </table>
    <ul style="list-style-type: decimal; width: 96%; font-size: 14pt; text-align: justify;">
        <li style="padding-left: 15px; padding-bottom: 10px;">Para todos os litígios, de natureza declarativa ou executiva, emergentes do presente contrato, serão competentes os Foros das Comarcas do Porto ou de Lisboa, à escolha do locador.</li>
        <li style="padding-left: 15px;">A parte vencida suportará as despesas derivadas de tais litígios, incluindo os honorários dos mandatários forenses a que a outra haja, porventura, de recorrer para fazer declarar e/ou executar os seus direitos. Os outorgantes declaram ter lido e conhecer as Condições Particulares e as Condições Gerais do presente contrato, às quais dão o seu pleno acordo, tendo-lhe sido entregue um exemplar e prestadas as necessárias informações sobre o conteúdo das mesmas.</li>
    </ul>
</page>