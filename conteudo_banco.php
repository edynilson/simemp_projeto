<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-15 17:59:32
*/

include('./conf/check.php');
include_once('./conf/common.php');

/* */
if (isset($_GET['lingua'])) {
	$idioma = $_GET['lingua'];
	$_SESSION['lingua'] = $lingua;
} else if (isset($_SESSION['lingua'])) {
	$idioma = $_SESSION['lingua'];
} else if (isset($_COOKIE['lingua'])) {
	$idioma = $_COOKIE['lingua'];
} else {
	$idioma = 'pt';
}
$idioma == 'pt' ? $name_column = 'nome_pais' : $name_column = 'country_name';
/* */

$query_movimento_saldo = $connection->prepare("SELECT m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id = m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
$query_movimento_saldo->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$linha_movimento_saldo = $query_movimento_saldo->fetch(PDO::FETCH_ASSOC);

$query_moeda = $connection->prepare("SELECT mo.simbolo, mo.ISO4217 FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_conta = $connection->prepare("SELECT c.id, c.num_conta, c.nib, c.iban FROM conta c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN utilizador u ON emp.id_empresa=u.id_empresa WHERE emp.ativo='1' AND u.id=:id_utilizador AND c.tipo_conta='ordem' LIMIT 1");
$query_conta->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);

$query_transf_receb = $connection->prepare("SELECT emp.id_empresa, m.data_op, m.tipo, m.descricao, m.credito, m.descricao AS nome FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND descricao LIKE '%Transferência de%' AND emp.id_empresa=:id_empresa AND c.tipo_conta = 'ordem'");
$query_transf_receb->execute(array(':id_empresa' => $_SESSION['id_empresa']));

$query_cred_receb = $connection->prepare("SELECT em.id, em.emprest, em.capital_pendente, MIN(em.pago) AS pago FROM emprestimo em INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND em.n_per=:n_per AND emp.id_empresa=:id_empresa GROUP BY em.emprest");
$query_cred_receb->execute(array(':n_per' => 1, ':id_empresa' => $_SESSION['id_empresa']));

$query_plafond_emprestimo = $connection->prepare("SELECT re.id_regra, re.id_empresa, re.valor, re.simbolo, re.id_banco FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_plafond_emprestimo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (empréstimo)"));
$linha_plafond_emprestimo = $query_plafond_emprestimo->fetch(PDO::FETCH_ASSOC);

$query_montante_min = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_montante_min->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Montante pretendido mínimo (empréstimo)'));
$linha_montante_min = $query_montante_min->fetch(PDO::FETCH_ASSOC);

$query_montante_max = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_montante_max->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Montante pretendido máximo (empréstimo)'));
$linha_montante_max = $query_montante_max->fetch(PDO::FETCH_ASSOC);

$query_periodo_min = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_periodo_min->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Prazo de financiamento mínimo (empréstimo)'));
$linha_periodo_min = $query_periodo_min->fetch(PDO::FETCH_ASSOC);

$query_periodo_max = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_periodo_max->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Prazo de financiamento máximo (empréstimo)'));
$linha_periodo_max = $query_periodo_max->fetch(PDO::FETCH_ASSOC);

$query_taxa_emprestimo = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_taxa_emprestimo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Taxa anual sobre o empréstimo'));
$linha_taxa_emprestimo = $query_taxa_emprestimo->fetch(PDO::FETCH_ASSOC);

$query_v_res = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_v_res->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Valor residual'));
$linha_v_res = $query_v_res->fetch(PDO::FETCH_ASSOC);

$query_min_leasing = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_min_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Valor mínimo (leasing)'));
$linha_min_leasing = $query_min_leasing->fetch(PDO::FETCH_ASSOC);

$query_max_leasing = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_max_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Valor máximo (leasing)'));
$linha_max_leasing = $query_max_leasing->fetch(PDO::FETCH_ASSOC);

$query_taxa_leasing = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_taxa_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Taxa anual sobre o leasing'));
$linha_taxa_leasing = $query_taxa_leasing->fetch(PDO::FETCH_ASSOC);

$query_periodo_min_leasing = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_periodo_min_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Prazo mínimo (leasing)'));
$linha_periodo_min_leasing = $query_periodo_min_leasing->fetch(PDO::FETCH_ASSOC);

$query_periodo_max_leasing = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_periodo_max_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Prazo máximo (leasing)'));
$linha_periodo_max_leasing = $query_periodo_max_leasing->fetch(PDO::FETCH_ASSOC);

$query_taxa_iva_normal = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_taxa_iva_normal->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Taxa de IVA normal'));
$linha_taxa_iva_normal = $query_taxa_iva_normal->fetch(PDO::FETCH_ASSOC);

$query_leasing_cont = $connection->prepare("SELECT l.id_leasing, l.leas, l.capital_pendente, MIN(l.pago) AS pago FROM leasing l INNER JOIN conta c ON l.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND l.n_per=:n_per AND emp.id_empresa=:id_empresa GROUP BY l.leas");
$query_leasing_cont->execute(array(':n_per' => 1, ':id_empresa' => $_SESSION['id_empresa']));

$query_factoring_cont = $connection->prepare("SELECT c.num_conta, fa.`data`, fa.valor, fa.tempo, fa.recurso, fa.comissao_valor, fa.seguro_valor, fa.juros_valor, fa.valor_recebido FROM factoring fa INNER JOIN conta c ON fa.id_conta=c.id WHERE c.id_empresa=:id_empresa");
$query_factoring_cont->execute(array(':id_empresa' => $_SESSION['id_empresa']));

$query_fatura = $connection->prepare("SELECT f.id_fatura, f.num_fatura FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND f.id_empresa=:id_empresa AND id_factoring IS NULL AND pago=:pago");
$query_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => 0));
$num_fatura = $query_fatura->rowCount();
$linha_fatura = $query_fatura->fetchAll();

$query_comissao_factoring = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_comissao_factoring->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Comissão (factoring)'));
$linha_query_comissao_factoring = $query_comissao_factoring->fetch(PDO::FETCH_ASSOC);

$query_seguro_factoring = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_seguro_factoring->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Seguro (factoring)'));
$linha_query_seguro_factoring = $query_seguro_factoring->fetch(PDO::FETCH_ASSOC);

$query_jurocr_factoring = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_jurocr_factoring->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Juro anual com recurso (factoring)'));
$linha_query_jurocr_factoring = $query_jurocr_factoring->fetch(PDO::FETCH_ASSOC);

$query_jurosr_factoring = $connection->prepare("SELECT re.valor FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_jurosr_factoring->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Juro anual sem recurso (factoring)'));
$linha_query_jurosr_factoring = $query_jurosr_factoring->fetch(PDO::FETCH_ASSOC);

$query_tipo_entrega = $connection->prepare("SELECT id, designacao FROM tipo_entrega");
$query_tipo_entrega->execute();

$query_plafond_factoring = $connection->prepare("SELECT re.id_regra, re.id_empresa, re.valor, re.simbolo, re.id_banco FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_plafond_factoring->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (factoring)"));
$linha_plafond_fact = $query_plafond_factoring->fetch(PDO::FETCH_ASSOC);

// $query_titulos = $connection->prepare("SELECT ac.id, p.id_pais, p.nome_pais, p.nome_bolsa, ac.nome, IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade) AS total FROM (SELECT ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_c AND act.tipo=:tipo_c GROUP BY nome) AS compras LEFT JOIN (SELECT ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_v AND act.tipo=:tipo_v GROUP BY nome) AS vendas ON compras.id=vendas.id INNER JOIN acao ac ON ac.id=compras.id OR ac.id=vendas.id INNER JOIN pais p ON ac.id_pais=p.id_pais WHERE IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade)<>0 ORDER BY p.nome_pais ASC");
$query_titulos = $connection->prepare("SELECT ac.id, p.id_pais, p.nome_abrev, p.$name_column AS nome_pais, p.nome_bolsa, ac.nome, IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade) AS total FROM (SELECT ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_c AND act.tipo=:tipo_c GROUP BY id) AS compras LEFT JOIN (SELECT ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_v AND act.tipo=:tipo_v GROUP BY id) AS vendas ON compras.id=vendas.id INNER JOIN acao ac ON ac.id=compras.id OR ac.id=vendas.id INNER JOIN pais p ON ac.id_pais=p.id_pais WHERE IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade)<>0 ORDER BY p.nome_pais ASC");
$query_titulos->execute(array(':id_empresa_c' => $_SESSION['id_empresa'], ':tipo_c' => 'C', ':id_empresa_v' => $_SESSION['id_empresa'], ':tipo_v' => 'V'));

$query_emprestimo = $connection->prepare("SELECT em.id, em.emprest, date_format(em.data_emprestimo,'%d-%m-%Y') AS data_emprestimo, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN emprestimo em ON p.id_emprestimo=em.id INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago");
$query_emprestimo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));

$query_banco = $connection->prepare("SELECT b.id, b.nome FROM banco b INNER JOIN entidade_banco eb ON b.id=eb.id_banco INNER JOIN utilizador u ON eb.id_entidade=u.id_entidade WHERE u.id = :id_utilizador LIMIT 1");
$query_banco->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_banco = $query_banco->fetch(PDO::FETCH_ASSOC);

$query_empresa = $connection->prepare("SELECT e.id_empresa, e.nome FROM empresa e INNER JOIN utilizador u ON e.id_empresa=u.id_empresa WHERE u.id = :id_utilizador LIMIT 1");
$query_empresa->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_empresa = $query_empresa->fetch(PDO::FETCH_ASSOC);

//-- Depósitos a Prazo
$query_contaP = $connection->prepare("SELECT c.id, c.num_conta, c.nib, c.iban FROM conta c INNER JOIN empresa e ON c.id_empresa = e.id_empresa INNER JOIN utilizador u ON u.id_empresa = e.id_empresa WHERE e.ativo = '1' AND c.tipo_conta = 'prazo' AND u.id = :id_utilizador LIMIT 1");
$query_contaP->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
// ADDED ON 23012017 TO AVOID ERROR OF PARSING NULL
$query_contaP->rowCount() > 0 ? $linha_contaP = $query_contaP->fetch(PDO::FETCH_ASSOC) : $linha_contaP = false;

//-- Consultar Taxas DP atuais
$query_tx_juro = $connection->prepare("SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra WHERE r.nome_regra = 'Juros de Depósitos a Prazo' AND re.id_empresa = :id_empresa ORDER BY re.date_reg DESC LIMIT 1");
$query_tx_juro->execute(array('id_empresa' => $_SESSION['id_empresa']));
$tx_juro = $query_tx_juro->fetch(PDO::FETCH_ASSOC);

$query_tx_irc = $connection->prepare("SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra WHERE r.nome_regra = 'Taxa de IRC' AND re.id_empresa = :id_empresa ORDER BY re.date_reg DESC LIMIT 1");
$query_tx_irc->execute(array('id_empresa' => $_SESSION['id_empresa']));
$tx_irc = $query_tx_irc->fetch(PDO::FETCH_ASSOC);

//-- Consultar saldo CP
$query_saldo_cp = $connection->prepare("SELECT c.id, m.saldo_disp FROM movimento m INNER JOIN conta c ON m.id_conta = c.id WHERE c.tipo_conta = 'prazo' AND c.id_empresa = :id_empresa ORDER BY m.id DESC LIMIT 1");
$query_saldo_cp->execute(array('id_empresa' => $_SESSION['id_empresa']));
$saldo_cp = $query_saldo_cp->fetch(PDO::FETCH_ASSOC);

//-- Consultar movimentos contaS a Prazo de determinado user:
$query_mov_contaP = $connection->prepare("SELECT m.data_op, m.tipo, m.descricao, m.credito, m.saldo_disp FROM movimento m INNER JOIN conta c ON m.id_conta = c.id INNER JOIN empresa e ON c.id_empresa = e.id_empresa INNER JOIN utilizador u ON u.id_empresa = e.id_empresa WHERE c.tipo_conta = 'prazo' AND u.id = :id_utilizador");
$query_mov_contaP->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));

// $query_plano_dp = $connection->prepare("SELECT j.id_juro, j.deposito, j.prestacao, j.montante, j.tx_juro, j.valor, j.tx_irc, j.irc, j.pago, j.data_lim_v FROM juros_dp j INNER JOIN conta c ON j.id_conta=c.id INNER JOIN empresa e ON c.id_empresa=e.id_empresa WHERE c.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r > NOW()");
$query_plano_dp = $connection->prepare("SELECT j.id_juro, j.deposito, j.prestacao, j.montante, j.tx_juro, j.valor, j.tx_irc, j.irc, j.pago, j.data_lim_v FROM juros_dp j INNER JOIN conta c ON j.id_conta=c.id INNER JOIN empresa e ON c.id_empresa=e.id_empresa WHERE c.id_empresa=:id_empresa AND c.tipo_conta='prazo'");
$query_plano_dp->execute(array('id_empresa' => $_SESSION['id_empresa']));

//-- Letras
$query_comissao_letra = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_comissao_letra->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Comissão (letras)'));
$linha_comissao_letra = $query_comissao_letra->fetch(PDO::FETCH_ASSOC);

$query_juro_letra = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_juro_letra->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Juros anual (letras)'));
$linha_juro_letra = $query_juro_letra->fetch(PDO::FETCH_ASSOC);

$query_imp_letra = $connection->prepare("SELECT re.valor, re.simbolo FROM regra r INNER JOIN regra_empresa re ON r.id_regra=re.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN banco b ON b.id=r.id_banco WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_imp_letra->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Imposto de selo (títulos de crédito)'));
$linha_imp_letra = $query_imp_letra->fetch(PDO::FETCH_ASSOC);

$query_letra_aceite = $connection->prepare("SELECT l.id_letra, e.nome, l.data_virt, l.data_lim_v, l.valor, l.pago, l.aceite FROM letra l INNER JOIN conta c ON l.id_conta_empresa=c.id INNER JOIN empresa e ON c.id_empresa=e.id_empresa WHERE l.id_conta_sacado=:id_conta_sacado AND l.data_lim_r > NOW()");
$query_letra_aceite->execute(array(':id_conta_sacado' => $linha_conta['id']));

$query_carteira_letra = $connection->prepare("SELECT f.num_fatura, e.nome AS empresa, l.imp_s, l.com, l.juro, l.valor, l.data_virt, l.data_lim_v, l.pago, l.aceite FROM letra l LEFT JOIN conta c ON l.id_conta_sacado=c.id LEFT JOIN empresa e ON c.id_empresa=e.id_empresa LEFT JOIN fatura f ON l.id_fatura=f.id_fatura WHERE l.id_conta_empresa=:id_conta_empresa");
$query_carteira_letra->execute(array(':id_conta_empresa' => $linha_conta['id']));

//-- Aumento de capital
$query_plafond_aumento = $connection->prepare("SELECT r.id_regra, re.valor, r.simbolo, r.id_banco, e.nome, c.id, c.num_conta FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa e ON re.id_empresa=e.id_empresa INNER JOIN conta c ON e.id_empresa=c.id_empresa WHERE r.nome_regra='Plafond de aumento de capital' AND e.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY re.date_reg DESC LIMIT 1");
$query_plafond_aumento->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$linha_plafond_aumento = $query_plafond_aumento->fetch(PDO::FETCH_ASSOC);

$query_limite_adiantamento = $connection->prepare("SELECT re.valor, r.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa e ON re.id_empresa=e.id_empresa WHERE r.nome_regra='Limite de adiantamento de clientes' AND e.id_empresa=:id_empresa ORDER BY re.date_reg DESC LIMIT 1");
$query_limite_adiantamento->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$linha_limite_adiantamento = $query_limite_adiantamento->fetch(PDO::FETCH_ASSOC);


/* MOVED TO conteudo_user
//-- Adiantamento a Fornecedores
$query_fornecedores = $connection->prepare("SELECT f.id, f.nome_abrev FROM fornecedor f ORDER BY f.nome_abrev ASC");
$query_fornecedores->execute();

//-- Adiantamentos efetuados
$query_carrega_adiant_efet = $connection->prepare("SELECT f.nome_abrev, a.valor, a.data_virt FROM adiantamento a INNER JOIN fornecedor f ON a.id_fornecedor=f.id WHERE a.id_empresa=:id_empresa AND a.pago=0 AND a.id_fornecedor IS NOT NULL ORDER BY f.nome_abrev ASC");
$query_carrega_adiant_efet->execute(array(':id_empresa' => $_SESSION['id_empresa']));
*/


//-- Adiantamentos recebidos
$query_carrega_adiant_receb = $connection->prepare("SELECT a.nome_cliente, a.valor, a.data_virt FROM adiantamento a WHERE a.id_empresa=:id_empresa AND a.pago=0 AND a.nome_cliente IS NOT NULL ORDER BY a.nome_cliente ASC");
$query_carrega_adiant_receb->execute(array(':id_empresa' => $_SESSION['id_empresa']));

//-- Consultar taxas de IVA (para Leasing)
$query_tx_iva = $connection->prepare("SELECT re.id_regra, re.valor FROM regra r LEFT JOIN (SELECT * FROM regra_empresa ORDER BY id_regra DESC, date_reg DESC) AS re ON r.id_regra=re.id_regra WHERE r.nome_regra LIKE 'Taxa de IVA %' AND re.id_empresa=:id_empresa GROUP BY re.id_regra ORDER BY re.valor ASC");
$query_tx_iva->execute(array('id_empresa' => $_SESSION['id_empresa']));

//-- Pais e Bolsas
/* Conexão 2ª BD */
try {
//    $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', 'T4h6m3YuniurhCDfHGE9VYBQmQMszt8x');
    $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', '');
    $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
	$query_pais_bolsa = $connection_bd_acao->prepare("SELECT p.id_pais, p.$name_column AS nome_pais, p.abreviatura, b.nome AS bolsa FROM pais p INNER JOIN bolsa b ON p.id_pais=b.id_pais WHERE b.abrev <> '' ORDER BY p.nome_pais ASC");
    $query_pais_bolsa->execute();
    
	/* Transações agendadas */
    $query_trans_agend = $connection_bd_acao->prepare("SELECT pae.id_preco_alvo, a.nome_acao, p.nome_pais, b.nome, pae.qtd, pae.preco_alvo, m.simbolo, pae.tipo, pae.data_limite_virtual FROM preco_alvo_empresa pae INNER JOIN acao a ON pae.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id_moeda WHERE pae.id_empresa=:id_empresa AND pae.active='1' AND (pae.data_limite_real>NOW() OR DATE(pae.data_limite_real)=CURDATE()) ORDER BY pae.tipo ASC, p.nome_pais ASC, a.nome_acao ASC");
    $query_trans_agend->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $linha_trans_agend = $query_trans_agend->fetchAll();
	
} catch (PDOException $e) {
    echo $e->getMessage();
    file_put_contents('PDOErrors_acoes.txt', $e->getMessage(), FILE_APPEND);
}

$connection_bd_acao = null;
?>

<div id="movimentos">
    <div class="linha">
        <div class="left-column" style="width: 40%;">
            <h3><?php echo $lingua['A_BALANCE']; ?> (<?php echo $linha_moeda['ISO4217']; ?>)</h3>
        </div>
        <div class="center-column">
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
    <div class="linha">
        <div class="left-column">&nbsp;</div>
        <div class="center-column">
            <div class="labelNormal"><?php echo $lingua['B_BALANCE']; ?>&nbsp;</div>
            <div class="moneyarea_col1" style="width: 178px;">
                <input id="txtSaldoContab" name="txtSaldoContab" type="text" readonly="readonly" value="<?php
                if ($linha_movimento_saldo['saldo_contab'] !== NULL) {
                    echo number_format($linha_movimento_saldo['saldo_contab'], 2, ',', '.');
                } else {
                    echo "0";
                }
                ?>">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                </div>
            </div>
        </div>
        <div class="right-column">
            <div class="labelNormal"><?php echo $lingua['B_AVAILABLE']; ?>&nbsp;</div>
            <div class="moneyarea_col1" style="width: 178px;">
                <input id="txtSaldoDisp" name="txtSaldoDisp" type="text" readonly="readonly" value="<?php
                if ($linha_movimento_saldo['saldo_disp'] !== NULL) {
                    echo number_format($linha_movimento_saldo['saldo_disp'], 2, ',', '.');
                } else {
                    echo "0";
                }
                ?>">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha">
        <div class="left-center-column">
            <div class="inputData" style="margin-left: 0;">
                <label for="txtDataI"><?php echo $lingua['FROM'].':'; ?></label>
                <input id="txtDataI" name="txtDataI" type="text" class="campoData">
                <div class="icon-cal"></div>
            </div>
            <div class="inputData">
                <label for="txtDataF"><?php echo $lingua['TO'].':'; ?></label>
                <input id="txtDataF" name="txtDataF" type="text" class="campoData">
                <div class="icon-cal"></div>
            </div>
            <div class="botaoLupa">
                <div id="btnProcurarMov" class="icon-lupa"></div>
            </div>
            <div class="botaoPrint">
                <div id="btnPrintMov" class="icon-printer">
                    <img src="images/printer_icon.png" alt="Imprimir">
                </div>
            </div>
            <div class="botaoExcel">
                <div id="btnPrintExcel" class="icon-printer">
                    <img src="images/excel.png" alt="Exportar para excel">
                </div>
            </div>
        </div>
        <div class="right-column">
            <div id="divSctPagination">
                <div class="styled-select">
                    <select id="slcPag" name="slcPag" size="1" class="select">
                        <option value="5" selected="selected">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </div>
            </div>
            <div class="pagination">
                <input id="pagTotal" type="hidden" readonly="readonly">
                <input id="pagAtual" type="hidden" readonly="readonly">
                <input id="pagLinhas" type="hidden" readonly="readonly">
                <div class="first">&laquo;</div>
                <div class="previous">&lsaquo;</div>
                <input id="pagInput" type="text" readonly="readonly">
                <div class="next">&rsaquo;</div>
                <div class="last">&raquo;</div>
            </div>
        </div>
    </div>
    <div class="linha">
        <table id="tblMovimentos" name="tblMovimentos" class="tabela">
            <tr>
                <td class="td10" style="padding: 6px;"><?php echo $lingua['DATE']; ?></td>
                <td class="td5" style="padding: 6px;"><?php echo $lingua['TYPE']; ?></td>
                <td class="td44" style="padding: 6px;"><?php echo $lingua['DESCRIPTION']; ?></td>
                <td class="td12" style="padding: 6px;"><?php echo $lingua['DEBIT']; ?></td>
                <td class="td12" style="padding: 6px;"><?php echo $lingua['CREDIT']; ?></td>
                <td class="td12" style="padding: 6px;"><?php echo $lingua['BALANCE']; ?></td>
                <td class="td5" style="background-color: transparent; padding: 6px;">&nbsp;</td>
            </tr>
        </table>
        <table id="tblMovVazia" name="tblMovVazia" class="tabela">
            <tr>
                <td>Não existem movimentos que satisfaçam a pesquisa</td>
            </tr>
        </table>
    </div>
</div>
<div id="nib">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['DAT'].lcfirst($lingua['ORDER_ACCOUNT']); ?></h3>
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
        <table id="tblIban" name="tblIban" class="tabela form_esq35">
            <tr>
                <td style="background-color: #2b6db9; text-align: center; color: #fff;">Nº</td>
                <td style="text-align: left; background-color: #ccd8e8; color: #000;">
                    <?php
                    $num_conta_raw = str_split($linha_conta['num_conta'], 4);
                    $num_conta = $num_conta_raw[0] . " " . $num_conta_raw[1] . " " . $num_conta_raw[2];
                    echo $num_conta;
                    ?>
                </td>
            </tr>
            <tr>
                <td style="background-color: #2b6db9; text-align: center; color: #fff;">NIB</td>
                <td style="text-align: left; background-color: #ccd8e8; color: #000;">
                    <?php
                    $nib_raw = str_split($linha_conta['nib']);
                    $nib = $nib_raw[0] . $nib_raw[1] . $nib_raw[2] . $nib_raw[3] . " " . $nib_raw[4] . $nib_raw[5] . $nib_raw[6] . $nib_raw[7] . " " . $nib_raw[8] . $nib_raw[9] . $nib_raw[10] . $nib_raw[11] . $nib_raw[12] . $nib_raw[13] . $nib_raw[14] . $nib_raw[15] . $nib_raw[16] . $nib_raw[17] . $nib_raw[18] . " " . $nib_raw[19] . $nib_raw[20];
                    echo $nib;
                    ?>
                </td>
            </tr>
            <tr>
                <td style="background-color: #2b6db9; text-align: center; color: #fff;">IBAN</td>
                <td style="text-align: left; background-color: #ccd8e8; color: #000;"><?php
                    $iban_raw = str_split($linha_conta['iban'], 4);
                    $iban = $iban_raw[0] . " " . $iban_raw[1] . " " . $iban_raw[2] . " " . $iban_raw[3] . " " . $iban_raw[4] . " " . $iban_raw[5] . " " . $iban_raw[6];
                    echo $iban;
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<div id="acoes">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['M_SHARES']; ?></h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- <div class="right-column">&nbsp;</div> -->
        <?php /* */ if ($_SESSION['tipo_grupo'] == "Bolsa") { ?>
            <div class="right-column">
                <!-- -->
                <div class="row middle-row">
                    <div class="cell middle-cell">
                        <div class="main-content">
                            <header>
                                <div class="tblVideo">
                                    <div class="tblCell">
                                        <!-- <img class="player" src="images/play_button_1.png" alt="Player"> <br> -->
                                        <!-- <img class="player" src="images/play_button_1.png" alt="Player" style="margin-top: 10px;"> -->
                                    </div>
                                    <div class="tblCell" style="float: left;">
                                        <!-- <a href="#openModalDicas" class="modalLink"> Tutorial: <i>Conceitos/Conselhos</i> </a> <br> -->
                                        <a href="#openModalAcoes" class="modalLink"> Tutorial: <i>Comprar/Vender</i> </a><br>
                                        <a href="REGULAMENTO BOLSA IPB 2018.pdf" style="letter-spacing: 0px"> <i>Regulamento Bolsa 2018</i> </a>
                                    </div> 
                                   
                                </div>
                            </header>
                            <div id="modalAcoes">
                                <!-- -->
								<div id="openModalDicas" class="window">
                                    <div class="contents">
                                        <video id="vid1" controls poster="images/logo.png">
                                            <source src="video/Conceitos_Conselhos.mp4" type="video/mp4">
                                            <source src="video/Conceitos_Conselhos.webm" type="video/webm">
                                            <source src="video/Conceitos_Conselhos.ogg" type="video/ogg">
                                        </video>
                                        <a href="#" class="close">X</a>
                                    </div>
                                </div>
								<!-- -->
                                <!-- -->
                                <div id="openModalAcoes" class="window">
                                    <div class="contents">
                                        <video id="vid2" controls poster="images/logo.png">
                                            <source src="video/Acoes.mp4" type="video/mp4">
                                            <source src="video/Acoes.webm" type="video/webm">
                                            <source src="video/Acoes.ogg" type="video/ogg">
                                        </video>
                                        <a href="#" class="close">X</a>
                                    </div>
                                </div>
                                <!-- -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- -->
            </div>
        <?php } /* */ ?>
    </div>
	
	<?php // if ($_SESSION['id_utilizador'] == 177 || $_SESSION['id_utilizador'] == 100) { ?>
		<div class="linha">
			<label for="slcPaisAcao" class="labelNormal"> <?php echo $lingua['STCK_MRKT']; ?>: &nbsp; </label>
			<div class="inputarea_col1">
				<div class="styled-select">
					<select id="slcPaisAcao" name="slcPaisAcao" size="1" class="select">
						<?php 
							while ($linha_pais_bolsa = $query_pais_bolsa->fetch(PDO::FETCH_ASSOC)) {
								if ($linha_pais_bolsa['nome_pais'] == "Portugal") { ?>
									<option value="<?php echo $linha_pais_bolsa['id_pais']; ?>" selected="selected"><?php echo $linha_pais_bolsa['nome_pais']. ' - ' .$linha_pais_bolsa['bolsa'] ?></option>
								<?php } /* else if ($linha_pais_bolsa['nome_pais'] == "Estados Unidos da América") { ?>
									<option value="<?php echo $linha_pais_bolsa['id_pais']; ?>"><?php echo $linha_pais_bolsa['abreviatura'] . ' - ' .$linha_pais_bolsa['bolsa'] ?></option>
								<?php } */ else { ?>
									<option value="<?php echo $linha_pais_bolsa['id_pais']; ?>"><?php echo $linha_pais_bolsa['nome_pais']. ' - ' .$linha_pais_bolsa['bolsa'] ?></option>
								<?php }
							}
						?>  
					</select>
				</div>
			</div>
		</div>
	<?php // } else { ?>
		<!-- <h3> *Em atualizações. Prometemos ser breves. Obrigado pela compreensão. </h3> -->
	<?php // } ?>
    <div class="linha">
        <div class="loading" style="width: 150px;">
            <div class="linha10">
                <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
            </div>
            <div class="linha" style="text-align: center;">A carregar...</div>
        </div>
    </div>
    <div class="linha">
        <?php // if ($_SESSION['id_utilizador'] == 177 || $_SESSION['id_utilizador'] == 100) { ?>
			<table id="tblAcoes" name="tblAcoes" class="tabela form_esq65">
				<tr>
					<td class="td15" style="padding: 4px;"><?php echo $lingua['S_NAME']; ?></td>
					<td class="td25" style="padding: 4px;"><?php echo $lingua['CPNY']; ?></td>
					<td class="td10" style="padding: 4px;"><?php echo $lingua['L_PRC']; ?></td>
					<td class="td10" style="padding: 4px;"><?php echo $lingua['CHNG']; ?></td>
					<td class="td10" style="padding: 4px;"><?php echo $lingua['OPN']; ?></td>
					<td class="td20" style="padding: 4px;">Máx / Min</td>
					<td class="td5" style="background-color: transparent;">&nbsp;</td>
					<td class="td5" style="background-color: transparent;">&nbsp;</td>
				</tr>
			</table>
		<?php // } ?>
        <div id="frmOrdemCompra" name="frmOrdemCompra" style="float: left; width: 25%; margin-left: 1%; background-color: #eaedf1;">
            <div class="linha10">
                <div class="esq50" style="width: 48%; margin-top: 15px; margin-left: 2%;">
                    <label for="txtNomeAcao" class="labelNormal"><?php echo $lingua['S_NAME']; ?></label>
                </div>
                <div class="dir50" style="margin-top: 15px;">
                    <div class="inputarea_col1" style="background-color: #ccd8e8; width: 99px; margin-right: 0; padding-left: 5px;">
                        <input name="txtNomeAcao" type="text" readonly="readonly" style="height: 28px;">
                    </div>
                </div>
            </div>
            
            <!-- -->
            <div class="linha10">
                <div class="divSwitchInscricoes">
                    <div class="esq50" style="width: 48%; margin-left: 2%;">
                        <label for="chkOrdemAcoes"><?php echo $lingua['IM_B']; ?></label>
                    </div>
                    <div class="dir50"> <!-- style="width: 38%; margin-left: 12%;" -->
                        <div class="onoffswitch">
                            <input id="chkOrdemAcoes" name="chkOrdemAcoes" type="checkbox" class="onoffswitch-checkbox" checked>
                            <label class="onoffswitch-label" for="chkOrdemAcoes">
                                <div class="onoffswitch-inner"></div>
                                <div class="onoffswitch-switch"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- -->
            
            <div id="acoesCompraImediata">
                <div class="linha10">
                    <div class="esq50" style="width: 48%; margin-left: 2%;">
                        <label for="txtPrecoAcao" class="labelNormal"><?php echo $lingua['L_PRC']; ?></label>
                    </div>
                    <div class="dir50">
                        <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                            <input name="txtPrecoAcao" type="text" readonly="readonly" value="" style="width: 80px;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10">
                    <div class="esq50" style="width: 48%; margin-left: 2%;">
                        <label for="txtDataAcao" class="labelNormal"><?php echo $lingua['DATE']; ?></label>
                    </div>
                    <div class="dir50">
                        <div class="inputarea_col1" style="background-color: #ccd8e8; width: 99px; margin-right: 0; padding-left: 5px;">
                            <input name="txtDataAcao" type="text" readonly="readonly" style="height: 28px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- -->
            <div id="acoesCompraAgendada">
                <div class="linha10">
                    <div class="esq50" style="width: 48%; margin-left: 2%;">
                        <label for="txtPrecoAlvoAcao" class="labelNormal"><?php echo $lingua['TRGT_P']; ?></label>
                    </div>
                    <div class="dir50">
                        <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                            <!-- <input id="txtPrecoAlvoAcaoComprar" name="txtPrecoAlvoAcao" type="text" class="acoes dinheiro" value="" style="width: 80px;"> -->
							<input id="txtPrecoAlvoAcaoComprar" name="txtPrecoAlvoAcao" type="text" class="acoes" value="" style="width: 80px;">
                            <div class="mnyLabel">
                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="linha10">
                    <div class="esq50" style="width: 48%; margin-left: 2%;">
                        <label for="txtDataLimiteAcao" class="labelNormal"><?php echo $lingua['L_DATE']; ?></label>
                    </div>
                    <div class="dir50 inputData" style="background-color: #ccd8e8; width: 109px; margin: 0; padding: 0;">
                        <input name="txtDataLimiteAcao" type="text" readonly="readonly" class="campoData" style="height: 28px;">
                        <div class="icon-cal" style="width: 21%;"></div>
                    </div>
                </div>
            </div>
            <!-- -->
            
            <div class="linha10">
                <div class="esq50" style="width: 48%; margin-left: 2%;">
                    <label for="txtQtdAcao" class="labelNormal"><?php echo $lingua['QTY']; ?></label>
                </div>
                <div class="dir50">
                    <div class="inputarea_col1" style="background-color: #ccd8e8; width: 99px; margin-right: 0; padding-left: 5px;">
                        <input name="txtQtdAcao" type="text" class="numero" style="height: 28px;">
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50" style="width: 48%; margin-left: 2%;">
                    <label for="txtSubtotalAcao" class="labelNormal">Subtotal</label>
                </div>
                <div class="dir50">
                    <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                        <input name="txtSubtotalAcao" type="text" readonly="readonly" value="0,000" style="width: 80px;">
                        <div class="mnyLabel">
                            <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50" style="width: 48%; margin-left: 2%;">
                    <label for="txtTotalAcao" class="labelNormal">Total</label>
                </div>
                <div class="dir50">
                    <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                        <input name="txtTotalAcao" type="text" readonly="readonly" value="0,000" style="width: 80px;">
                        <div class="mnyLabel">
                            <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha" style="text-align: center;">
                <button id="btnComprar" name="btnComprar" class="btn icon-carrinho" style="float: none; margin-top: 10px;"><?php echo $lingua['BUY']; ?></button>
            </div>
            <input id="hddEncargoAcao" name="hddEncargoAcao" type="hidden" readonly="readonly">
            <input id="hddISAcao" name="hddISAcao" type="hidden" readonly="readonly">
            <input id="txtDataCompletaAcao" name="txtDataCompletaAcao" type="hidden" readonly="readonly">
        </div>
		<!-- Div Gráfico
		<div id="chartdiv_pop_up" style="position: absolute; margin-left: 53%;"></div> -->
		<div id="chartdiv_pop_up" style="margin-left: 52%;"></div>
		<!-- -->
    </div>
    
    <!-- <div class="linha"></div> -->
    
    <!-- Div Gráfico
    <div id="chartdiv_pop_up" style="position: absolute; margin-left: 52%; margin-top: 12%;"></div>
    <!-- -->
</div>
<div id="carteira_titulos">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['P_VIEW']; ?></h3>
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
    <!-- <table id="tblCarteiraAcoes" name="tblCarteiraAcoes" style="float: left; width: 74%;" class="tabela">
        <tr>
            <td class="td35">Pais</td>
            <td class="td20">Bolsa/Índice</td>
            <td class="td20">Nome</td>
            <td class="td20">Quantidade</td>
            <td class="td5" style="background-color: transparent;">&nbsp;</td>
        </tr> -->
    
        <?php
        $pais_ant = "";
        $i=0;
        while ($linha_titulos = $query_titulos->fetch(PDO::FETCH_ASSOC)) {
            // if ($linha_titulos['nome_pais'] != $pais_ant && $i > 0) {
			if ($linha_titulos['nome_pais'] != $pais_ant) {
                if ($i > 0) { ?>
                    </table>
                    <div class="linha espacamento">&nbsp;</div>
                <?php }
                
				if ($linha_titulos['nome_pais'] == 'Reino Unido')
					echo '<div class="linha10"><h5 style="color:red"> *O acesso a esse mercado foi descontinuado. As ações detidas e relativas a este mercado devem ser alienadas com base na última cotação disponível. </h5></div>';
				?>
				
                <table id="tblCarteiraAcoes_<?php echo $linha_titulos['nome_abrev']; ?>" name="tblCarteiraAcoes" style="float: left; width: 74%;" class="tabela">
                    <tr>
                        <td class="td35"><?php echo $lingua['COUNTRY']; ?></td>
                        <td class="td20"><?php echo $lingua['STCK_MRKT']; ?></td>
                        <td class="td20"><?php echo $lingua['S_NAME']; ?></td>
                        <td class="td20"><?php echo $lingua['QTY']; ?></td>
                        <td class="td5" style="background-color: transparent;">&nbsp;</td>
                    </tr>
            <?php } 
            $pais_ant = $linha_titulos['nome_pais']; 
            $i++;
            ?>
                    <tr>
                        <td><?php echo $linha_titulos['nome_pais']; ?></td>
                        <td><?php echo $linha_titulos['nome_bolsa']; ?></td>
                        <td><?php echo $linha_titulos['nome']; ?></td>
                        <td><?php echo number_format($linha_titulos['total'], 0, ',', '.'); ?></td>
                        <td style="background-color: #77a4d7; padding: 2px; cursor: pointer;">
                            <input id="hddIdAcao" name="hddIdAcao" type="hidden" value="<?php echo $linha_titulos['id']; ?>">
                            <div name="btnIdAcao" class="labelicon icon-info control"></div>
                        </td>
                    </tr>
            <?php } ?>
        </table>
    
    <table id="tblCarteiraAcoesVazia" name="tblCarteiraAcoesVazia" class="tabela">
        <tr>
            <td>Não possui títulos na sua carteira</td>
        </tr>
    </table>
    <table id="tblAcoesDetalhes" name="tblAcoesDetalhes" style="float: left; width: 74%; margin-right: 1%;" class="tabela">
        <tr>
            <td class="td10" style="padding: 4px;"><?php echo $lingua['S_NAME']; ?></td>
            <td class="td15" style="padding: 4px;"><?php echo $lingua['B_PRC']; ?></td>
            <td class="td10" style="padding: 4px;"><?php echo $lingua['DATE']; ?></td>
            <td class="td15" style="padding: 4px;"><?php echo $lingua['QTY']; ?></td>
            <td class="td15" style="padding: 4px;">Total</td>
            <td class="td15" style="padding: 4px;"><?php echo $lingua['A_PRC']; ?></td>
            <td class="td15" style="padding: 4px;"><?php echo $lingua['PROF']; ?></td>
            <td class="td5" style="background-color: transparent;"></td>
        </tr>
    </table>
    <div class="form_esq25" style="margin-bottom: 5px;">
        <button id="btnVoltarAcoes" name="btnVoltarAcoes" class="btnNoIco voltarDir"><?php echo $lingua['BCK']; ?></button>
    </div>
    <div id="frmVendaAcoes" name="frmVendaAcoes" class="form_esq25" style="background-color: #eaedf1;">
        <div class="linha10">
            <div class="esq50" style="width: 48%; margin-top: 15px; margin-left: 2%;">
                <label for="txtNomeAcao" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['S_NAME']; ?></label>
            </div>
            <div class="dir50" style="margin-top: 15px;">
                <div class="inputarea_col1" style="background-color: #ccd8e8; width: 99px; margin-right: 0; padding-left: 5px;">
                    <input id="txtNomeAcao" name="txtNomeAcao" type="text" readonly="readonly" value="" style="height: 28px;">
                    <input id="hddIdPaisAcao" name="hddIdPaisAcao" type="hidden" value="">
                    <input id="hddIdAcao" name="hddIdAcao" type="hidden" value="">
                </div>
            </div>
        </div>
        
        <!-- -->
        <div class="linha10">
            <div class="divSwitchInscricoes">
                <div class="esq50" style="width: 48%; margin-left: 2%;">
                    <label for="chkOrdemAcoesVender"><?php echo $lingua['IM_S']; ?></label>
                </div>
                <div class="dir50"> <!-- style="width: 38%; margin-left: 12%;" -->
                    <div class="onoffswitch">
                        <input id="chkOrdemAcoesVender" name="chkOrdemAcoesVender" type="checkbox" class="onoffswitch-checkbox" checked>
                        <label class="onoffswitch-label" for="chkOrdemAcoesVender">
                            <div class="onoffswitch-inner"></div>
                            <div class="onoffswitch-switch"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <!-- -->
        
        <div class="linha10">
            <div class="esq50" style="width: 48%; margin-left: 2%;">
                <label for="txtPrecoCompraAcao" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['B_PRC']; ?></label>
            </div>
            <div class="dir50">
                <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                    <input id="txtPrecoCompraAcao" name="txtPrecoCompraAcao" type="text" readonly="readonly" value="" style="width: 80px;">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50" style="width: 48%; margin-left: 2%;">
                <label for="txtPrecoAtualAcao" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['A_PRC']; ?></label>
            </div>
            <div class="dir50">
                <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                    <input id="txtPrecoAtualAcao" name="txtPrecoAtualAcao" type="text" readonly="readonly" value="" style="width: 80px;">
                    <input id="hddMaxPrecoAtual" name="hddMaxPrecoAtual" type="hidden" readonly="readonly" value="">
                    <input id="hddMinPrecoAtual" name="hddMinPrecoAtual" type="hidden" readonly="readonly" value="">
                    <input id="hddVolAtual" type="hidden" readonly="readonly" value="">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50" style="width: 48%; margin-left: 2%;">
                <label for="txtQtdAcoesCompradas" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['QTY'].$lingua['PRCH']; ?></label>
            </div>
            <div class="dir50">
                <div class="inputarea_col1" style="background-color: #ccd8e8; width: 99px; margin-right: 0; padding-right: 5px;">
                    <input id="txtQtdAcoesCompradas" name="txtQtdAcoesCompradas" type="text" readonly="readonly" value="" style="text-align: right; height: 28px; color: #757575;">
                </div>
            </div>
        </div>
        
        <!-- -->
        <div id="acoesVendaAgendada">
            <div class="linha10">
                <div class="esq50" style="width: 48%; margin-left: 2%;">
                    <label for="txtPrecoAlvoAcao" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['TRGT_P']; ?></label>
                </div>
                <div class="dir50">
                    <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                        <!-- <input id="txtPrecoAlvoAcaoVender" name="txtPrecoAlvoAcao" type="text" class="acoes dinheiro" value="" style="width: 80px;"> -->
						<input id="txtPrecoAlvoAcaoVender" name="txtPrecoAlvoAcao" type="text" class="acoes" value="" style="width: 80px;">
                        <div class="mnyLabel">
                            <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10">
                <div class="esq50" style="width: 48%; margin-left: 2%;">
                    <label for="txtDataLimiteAcao" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['L_DATE']; ?></label>
                </div>
                <div class="dir50 inputData" style="background-color: #ccd8e8; width: 104px; margin: 0; padding: 0;">
                    <input name="txtDataLimiteAcao" type="text" readonly="readonly" class="campoData" style="height: 28px;">
                    <div class="icon-cal" style="width: 21%;"></div>
                </div>
            </div>
        </div>
        <!-- -->
        
        <div class="linha10">
            <div class="esq50" style="width: 48%; margin-left: 2%;">
                <label for="txtPrecoVendaAcoes" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['S_PRC']; ?></label>
            </div>
            <div class="dir50">
                <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                    <!-- <input id="txtPrecoVendaAcoes" name="txtPrecoVendaAcoes" type="text" class="acoes dinheiro" style="width: 80px;"> -->
					<input id="txtPrecoVendaAcoes" name="txtPrecoVendaAcoes" type="text" class="acoes" style="width: 80px;">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50" style="width: 48%; margin-left: 2%;">
                <label for="txtQtdVendaAcoes" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;"><?php echo $lingua['QTY'].' '.$lingua['TO'].' '.strtolower($lingua['SELL']); ?></label>
            </div>
            <div class="dir50">
                <div class="inputarea_col1" style="background-color: #ccd8e8; width: 99px; margin-right: 0; padding-right: 5px;">
                    <input id="txtQtdVendaAcoes" name="txtQtdVendaAcoes" type="text" class="numero" value="" style="height: 28px; color: #757575;">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq50" style="width: 48%; margin-left: 2%;">
                <label for="txtTotalVendaAcoes" class="labelNormal" style="font-size: 9pt; line-height: 2.6em;">Total</label>
            </div>
            <div class="dir50">
                <div class="moneyarea_col1" style="background-color: #ccd8e8; width: 104px;">
                    <input id="txtTotalVendaAcoes" name="txtTotalVendaAcoes" type="text" readonly="readonly" value="" style="width: 80px;">
                    <input id="hddEncargoAcao" name="hddEncargoAcao" type="hidden" readonly="readonly" value="">
                    <input id="hddISAcao" name="hddISAcao" type="hidden" readonly="readonly" value="">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnVenderAcoes" name="btnVenderAcoes" class="btn icon-carrinho" style="float: none; margin-top: 10px;"><?php echo $lingua['SELL']; ?></button>
        </div>
    </div>
</div>
<div id="transferencia">
    <div class="linha">
        <div class="left-column">
            <h3>Transferências</h3>
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
    <div class="linha10">
        <div class="esq12"><label for="txtConta" class="labelNormal"><?php echo $lingua['S_ACCOUNT']; ?></label></div>
        <div class="dir88">
            <div class="inputarea_col1">
                <div class="styled-select">
                    <select id="txtConta" name="txtConta" size="1" class="select">
                        <option value="<?php echo $num_conta; ?>" selected="selected"><?php echo $num_conta; ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq12">
            <label for="txtContaDestino1" class="labelNormal"><?php echo $lingua['D_ACCOUNT']; ?>*</label>
        </div>
        <div class="dir88 inputsConta">
            <div class="inputarea_conta">
                <input id="txtContaDestino1" name="txtContaDestino1" type="text" maxlength="4" class="conta">
            </div>
            <div class="inputarea_conta">
                <input id="txtContaDestino2" name="txtContaDestino2" type="text" maxlength="4" class="conta">
            </div>
            <div class="inputarea_conta">
                <input id="txtContaDestino3" name="txtContaDestino3" type="text" maxlength="4" class="conta">
            </div>
            <button id="btnBeneficiarios" name="btnBeneficiarios" class="btnNoIco" style="padding: 7px 20px; height: 30px;"><?php echo $lingua['P_LIST']; ?></button>
        </div>
    </div>
    <div class="linha10">
        <div class="esq12">
            <label for="txtMontanteTransf" class="labelNormal"><?php echo $lingua['AMOUNT']; ?>*</label>
        </div>
        <div class="dir88">
            <div class="moneyarea_col1">
                <input id="txtMontanteTransf" name="txtMontanteTransf" type="text" size="6" class="dinheiro">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq12">
            <label for="txtDescricaoTransf" class="labelNormal"><?php echo $lingua['DESCRIPTION']; ?></label>
        </div>
        <div class="dir88">
            <div class="inputarea_assunto">
                <input id="txtDescricaoTransf" name="txtDescricaoTransf" style="padding-left: 2%; margin: 0;" type="text" size="55" value="">
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq12">
            <label for="" class="labelNormal"><?php echo $lingua['P_DATE']; ?></label>
        </div>
        <div class="dir88">
            <div class="inputarea_conta">
                <input id="txtDataVirtTransf" name="txtDataVirtTransf" type="text" maxlength="4" readonly="readonly">
            </div>
            <input id="hddNomeEmpresa" name="hddNomeEmpresa" type="hidden">
            <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden">
        </div>
    </div>
    <div class="linha10" style="text-align: center;">
            <button id="btnAddTransf" name="btnAddTransf" class="btnNoIco" style="float: none; margin-top: 10px;"><?php echo $lingua['ADD']; ?></button>
            <button id="btnLimparTransf" name="btnLimparTransf" class="btnNoIco" style="float: none; margin-top: 10px;"><?php echo $lingua['CLEAR']; ?></button>
    </div>
    <table id="tblTransferencias" name="tblTransferencias" data-value="1" class="tabela">
        <tr>
            <td class="td5">
                <div class="checkbox">
                    <input id="chkAllTransf" name="chkAllTransf" type="checkbox" class="chk">
                    <label for="chkAllTransf" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="td15"><?php echo $lingua['D_ACCOUNT']; ?></td>
            <td class="td15"><?php echo $lingua['AMOUNT']; ?></td>
            <td class="td35"><?php echo $lingua['DESCRIPTION']; ?></td>
            <td class="td10"><?php echo $lingua['P_DATE']; ?></td>
            <td class="td25"><?php echo $lingua['COMPANY']; ?></td>
        </tr>
    </table>
    <div class="linha10" style="text-align: center;">
        <button id="btnTransf" name="btnTransf" class="btnNoIco" style="float: none; margin-top: 10px;"><?php echo $lingua['TRANSFER']; ?></button>
        <button id="btnDelTransf" name="btnDelTransf" class="btnNoIco" style="float: none; margin-top: 10px;">Apagar</button>
    </div>
</div>
<div id="transf_receb">
    <div class="linha">
        <div class="left-center-column">
            <h3>Transferências recebidas</h3>
        </div>
    </div>
    <table id="tblTransfRec" name="tblTransfRec" class="tabela">
        <tr>
            <td class="td20">Nome do ordenante</td>
            <td class="td10">Data</td>
            <td class="td20">Montante</td>
            <td class="td20">Tipo de transferência</td>
            <td class="td30">Descrição</td>
        </tr>
        <?php while ($linha_transf_receb = $query_transf_receb->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td style="padding: 4px;"><?php
                    $nome = preg_split("/Transferência\sde/", $linha_transf_receb['nome']);
                    echo $nome[1];
                    ?></td>
                <td style="padding: 4px;"><?php
                    $date = preg_split("/\s/", $linha_transf_receb['data_op']);
                    echo date("d-m-Y", strtotime($date[0]));
                    ?></td>
                <td style="padding: 4px;"><?php echo number_format($linha_transf_receb['credito'], 2, ',', '.'); ?></td>
                <td style="padding: 4px;"><?php echo $linha_transf_receb['tipo']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_transf_receb['descricao']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblTransfRecVazia" name="tblTransfRecVazia" class="tabela">
        <tr>
            <td>Não existem transferências recebidas</td>
        </tr>
    </table>
</div>
<div id="exemplo">
    <div class="linha">
        <div class="left-column" style="width: 45%">
            <h3><?php echo "ola mundo"; ?></h3>
        </div>
        <div class="center-right-column" style="width: 55%">
            <div class="error"></div>
        </div>
    </div>

</div>
<div id="credito">
    <div class="linha">
        <div class="left-column" style="width: 45%">
            <h3><?php echo $lingua['P_C_REQ']; ?></h3>
        </div>
        <div class="center-right-column" style="width: 55%">
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
    <div id="frmCredito" name="frmCredito" class="form_esq70">
        <div class="linha">
            <div class="esq30">
                <label for="txtPlafondCre" class="labelNormal">Plafond disponível</label>
            </div>
            <div class="dir70">
                <div class="moneyarea_col1">
                    <input id="txtPlafondCre" name="txtPlafondCre" type="text" readonly="readonly" value="<?php echo number_format($linha_plafond_emprestimo['valor'], 2, ',', '.'); ?>">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="txtMontanteCre" class="labelNormal">Montante pretendido</label>
            </div>
            <div class="dir70">
                <div class="moneyarea_col1" style="margin-right: 15px;">
                    <input id="txtMontanteCre" name="txtMontanteCre" type="text" class="dinheiro">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                    </div>
                </div>
                <div style="float: left; height: 30px; margin-left: 0.5%;">
                    <input style="font-size: 10pt; width: 150px;" class="inputNoBackground" type="text" value="[<?php echo number_format($linha_montante_min['valor'], 0, ',', '.'); ?> - <?php echo number_format($linha_montante_max['valor'], 0, ',', '.'); ?>] <?php echo $linha_moeda['simbolo'] ?>" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="txtPrazoCre" class="labelNormal">Prazo de financiamento</label>
            </div>
            <div class="dir70">
                <div class="inputarea_col1" style="margin-right: 15px;">
                    <input id="txtPrazoCre" name="txtPrazoCre" type="text" class="numero" maxlength="2" style="padding-left: 0; padding-right: 2%;">
                </div>
                <div style="float: left; height: 30px;">
                    <input style="font-size: 10pt; width: 150px;" class="inputNoBackground" type="text" value="[<?php echo number_format($linha_periodo_min['valor'], 0, ',', ''); ?> - <?php echo number_format($linha_periodo_max['valor'], 0, ',', ''); ?>] meses" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30"><label for="txtTaxaCre" class="labelNormal">Taxa</label></div>
            <div class="dir70">
                <div class="moneyarea_col1">
                    <input id="txtTaxaCre" name="txtTaxaCre" type="text" readonly="readonly" value="<?php echo number_format($linha_taxa_emprestimo['valor'], 3, ',', ''); ?>">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_taxa_emprestimo['simbolo'] ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30"><label for="txtDataCre" class="labelNormal">Data</label></div>
            <div class="dir70">
                <div class="inputarea_col1">
                    <input id="txtDataCre" name="txtDataCre" type="text" readonly="readonly" style="height: 28px;">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="slcCarenciaCre" class="labelNormal">Período de carência</label>
            </div>
            <div class="dir70">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcCarenciaCre" name="slcCarenciaCre" size="1" class="select">
                            <option value="0" selected="selected">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="slcPerPagaCre" class="labelNormal">Percentagem paga</label>
            </div>
            <div class="dir70">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcPerPagaCre" name="slcPerPagaCre" size="1" class="select">
                            <option value="0" selected="selected">0</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="30">30</option>
                            <option value="40">40</option>
                            <option value="50">50</option>
                            <option value="60">60</option>
                            <option value="70">70</option>
                            <option value="80">80</option>
                            <option value="90">90</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnSimCre" name="btnSimCre" class="btnNoIco" style="float: none; margin-top: 10px;">Plano de crédito</button>
            <button id="btnEmprestimo" name="btnEmprestimo" class="btnNoIco" style="float: none; margin-top: 10px;">Pedir empréstimo</button>
        </div>
    </div>
    <table id="tblSimCre" name="tblSimCre" class="tabela form_esq55">
        <tr>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Períodos</td>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Capital pendente</td>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Juros</td>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Amortização</td>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Prestação</td>
        </tr>
    </table>
</div>
<div id="cons_cred">
    <div class="linha">
        <div class="left-center-column">
            <h3>Consulta de crédito</h3>
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
    <table id="tblCreditos" name="tblCreditos" class="tabela form_esq40">
        <tr>
            <td class="td5">Nº</td>
            <td class="td85">Valor</td>
            <td class="td5">Paga</td>
            <td class="td5" style="background-color: transparent;"></td>
        </tr>
        <?php while ($linha_cred_receb = $query_cred_receb->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $linha_cred_receb['emprest']; ?></td>
                <td><?php echo number_format($linha_cred_receb['capital_pendente'], 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                <td><?php
                    if ($linha_cred_receb['pago'] == '1') {
                        echo "Sim";
                    } else {
                        echo "Não";
                    }
                    ?>
                </td>
                <td style="background-color: #77a4d7; padding: 2px; cursor: pointer;">
                    <input id="hddIdEmprest" name="hddIdEmprest" type="hidden" value="<?php echo $linha_cred_receb['emprest']; ?>">
                    <div id="btnIdCre_<?php echo $linha_cred_receb['id']; ?>" name="btnIdCre" class="labelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblCreditosVazia" name="tblCreditosVazia" class="tabela">
        <tr>
            <td>Não existem créditos</td>
        </tr>
    </table>
    <table id="tblCreditoDetail" name="tblCreditoDetail" class="tabela form_esq80">
        <tr>
            <td class="td10">Períodos</td>
            <td class="td20">Capital pendente</td>
            <td class="td20">Juros</td>
            <td class="td20">Amortização</td>
            <td class="td20">Prestação</td>
            <td class="td10">Paga</td>
        </tr>
    </table>
    <div style="float: left; width: 20%; margin-bottom: 5px;">
        <button id="btnVoltarCredito" name="btnVoltarCredito" class="btnNoIco voltarDir">Voltar</button>
    </div>
</div>
<div id="leasing_pedido">
    <div class="linha">
        <div class="left-column" style="width: 60%;">
            <h3><?php echo $lingua['L_REQ']; ?></h3>
        </div>
        <div class="center-right-column" style="width: 40%;">
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
    <div id="frmLeasing" name="frmLeasing" style="float: left; width: 60%;">
        <div class="linha10">
            <div class="esq30">
                <label for="txtTaxaResLeas" class="labelNormal">Taxa residual</label>
            </div>
            <div class="dir70">
                <div class="moneyarea_col1">
                    <input id="txtValResLeas" name="txtValResLeas" type="text" readonly="readonly" value="<?php echo number_format($linha_v_res['valor'], 2, ',', '.'); ?>">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_v_res['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="txtValorLeas" class="labelNormal">Valor</label>
            </div>
            <div class="dir70">
                <div class="moneyarea_col1" style="margin-right: 15px;">
                    <input id="txtValorLeas" name="txtValorLeas" type="text" class="dinheiro">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                    </div>
                </div>
                <div style="float: left; height: 30px; margin-left: 0.5%;">
                    <input style="font-size: 10pt; width: 150px;" class="inputNoBackground" type="text" value="[<?php echo number_format($linha_min_leasing['valor'], 0, ',', '.'); ?> - <?php echo number_format($linha_max_leasing['valor'], 0, ',', '.'); ?>] <?php echo $linha_moeda['simbolo'] ?>" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="txtPrazoLeas" class="labelNormal">Prazo de leasing</label>
            </div>
            <div class="dir70">
                <div class="inputarea_col1" style="margin-right: 15px;">
                    <input id="txtPrazoLeas" name="txtPrazoLeas" type="text" class="numero" maxlength="2" style="padding-left: 0; padding-right: 2%;">
                </div>
                <div style="float: left; height: 30px;">
                    <input style="font-size: 10pt; width: 150px;" class="inputNoBackground" type="text" value="[<?php echo number_format($linha_periodo_min_leasing['valor'], 0, ',', '.'); ?> - <?php echo number_format($linha_periodo_max_leasing['valor'], 0, ',', '.'); ?>] meses" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="txtValorResLeas" class="labelNormal">Valor residual</label>
            </div>
            <div class="dir70">
                <div class="moneyarea_col1">
                    <input id="txtValorResLeas" name="txtValorResLeas" type="text" readonly="readonly">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30"><label for="txtTaxaLeas" class="labelNormal">Taxa</label></div>
            <div class="dir70">
                <div class="moneyarea_col1">
                    <input id="txtTaxaLeas" name="txtTaxaLeas" type="text" readonly="readonly" value="<?php echo number_format($linha_taxa_leasing['valor'], 3, ',', ''); ?>">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_taxa_emprestimo['simbolo'] ?>">
                    </div>
                </div>
            </div>
        </div>
		<div class="linha10">
            <div class="esq30">
                <label for="slcPerPagaLeas" class="labelNormal">Taxa de IVA</label>
            </div>
            <div class="dir70">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcTxIvaLeas" name="slcTxIvaLeas" size="1" class="select">
                            <!-- <option value="0">Isento</option> -->
                            <?php while($linha_tx_iva = $query_tx_iva->fetch(PDO::FETCH_ASSOC)) {
                                echo $linha_tx_iva['id_regra'] == 13 ? '<option value="'.$linha_tx_iva['valor'].'" selected="selected">'.number_format($linha_tx_iva['valor'], 2, ',', '.').' %</option>'
                                                                     : '<option value="'.$linha_tx_iva['valor'].'">'.number_format($linha_tx_iva['valor'], 2, ',', '.').' %</option>';
                            } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30"><label for="txtDataLeas" class="labelNormal">Data</label></div>
            <div class="dir70">
                <div class="inputarea_col1">
                    <input id="txtDataLeas" name="txtDataLeas" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="slcCarenciaLeas" class="labelNormal">Período de carência</label>
            </div>
            <div class="dir70">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcCarenciaLeas" name="slcCarenciaLeas" size="1" class="select">
                            <option value="0" selected="selected">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="slcPerPagaLeas" class="labelNormal">Percentagem paga</label>
            </div>
            <div class="dir70">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcPerPagaLeas" name="slcPerPagaLeas" size="1" class="select">
                            <option value="0" selected="selected">0</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="30">30</option>
                            <option value="40">40</option>
                            <option value="50">50</option>
                            <option value="60">60</option>
                            <option value="70">70</option>
                            <option value="80">80</option>
                            <option value="90">90</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq30">
                <label for="txtaDescBemLes" class="labelNormal">Descrição do bem</label>
            </div>
            <div class="dir70" style="height: 100%;">
                <textarea id="txtaDescBemLes" name="txtaDescBemLes" rows="4" cols="30"></textarea>
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnLeasingSim" name="btnLeasingSim" class="btnNoIco" style="float: none; margin-top: 10px;">Plano de leasing</button>
            <button id="btnLeasing" name="btnLeasing" class="btnNoIco" style="float: none; margin-top: 10px;">Pedir leasing</button>
        </div>
    </div>
    <table id="tblLeasing" name="tblLeasing" style="float: left; width: 64%;" class="tabela">
        <tr>
            <td class="td15" style="padding: 2px; font-size: 8pt;">Períodos</td>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Capital pendente</td>
            <td class="td10" style="padding: 2px; font-size: 8pt;">Juros</td>
            <td class="td5" style="padding: 2px; font-size: 8pt;">Amortização</td>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Prestação s/ IVA</td>
            <td class="td10" style="padding: 2px; font-size: 8pt;">IVA</td>
            <td class="td20" style="padding: 2px; font-size: 8pt;">Prestação c/ IVA</td>
        </tr>
    </table>
    <!-- <input id="hddTaxaIvaLea" name="hddTaxaIvaLea" type="hidden" value="<?php echo number_format($linha_taxa_iva_normal['valor'], 0, ',', ''); ?>"> -->
    <input id="hddSimLea" name="hddSimLea" type="hidden" value="1">
    <input id="hddDescricaoLea" name="hddDescricaoLea" type="hidden" value="">
</div>
<div id="cons_leasing">
    <div class="linha">
        <div class="left-center-column">
            <h3>Consultar locações</h3>
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
    <table id="tblConsLeas" name="tblConsLeas" class="tabela form_esq50">
        <tr>
            <td class="td10">Nº</td>
            <td class="td75">Capital pendente</td>
            <td class="td5">Paga</td>
            <td class="td5" style="background-color: transparent;"></td>
            <td class="td5" style="background-color: transparent;"></td>
        </tr>
        <?php while ($linha_leasing_cont = $query_leasing_cont->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $linha_leasing_cont['leas']; ?></td>
                <td><?php echo number_format($linha_leasing_cont['capital_pendente'], 2, ',', '.'); ?></td>
                <td><?php
                    if ($linha_leasing_cont['pago'] == '1') {
                        echo "Sim";
                    } else {
                        echo "Não";
                    }
                    ?></td>
                <td style="background-color: #77a4d7; padding: 2px; cursor: pointer;">
                    <input id="hddIdLeasing" name="hddIdLeasing" type="hidden" value="<?php echo $linha_leasing_cont['leas']; ?>">
                    <div id="btnIdLeas_<?php echo $linha_leasing_cont['id_leasing']; ?>" name="btnIdLeas" class="labelicon icon-info"></div>
                </td>
                <td style="background-color: transparent; padding: 0; cursor: pointer;">
                    <a href="./impressao/leasing.php?leas=<?php echo $linha_leasing_cont['leas']; ?>" target="_blank">
                        <img width="33" height="33" src="images/adobe_logo.png">
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblConsLeasVazia" name="tblConsLeasVazia" class="tabela">
        <tr>
            <td>Não existem contratos de leasing</td>
        </tr>
    </table>
    <table id="tblLeasingDetail" name="tblLeasingDetail" class="tabela form_esq85">
        <tr>
            <td class="td5">Períodos</td>
            <td class="td20">Capital pendente</td>
            <td class="td10">Juros</td>
            <td class="td10">Amortização</td>
            <td class="td20">Prestação s/ IVA</td>
            <td class="td10">IVA</td>
            <td class="td20">Prestação c/ IVA</td>
            <td class="td5">Paga</td>
        </tr>
    </table>
    <div style="float: left; width: 15%; margin-bottom: 5px;">
        <button id="btnVoltarLeasing" name="btnVoltarLeasing" class="btnNoIco voltarDir">Voltar</button>
    </div>
</div>
<div id="novo_factoring">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['F_REQ']; ?></h3>
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
    <table id="tblFactoring" name="tblFactoring" class="tabela">
        <tr>
            <td class="td5" style="background-color: transparent;"></td>
            <td class="td10">Nº</td>
            <td class="td20">Cliente</td>
            <td class="td10">Data</td>
            <td class="td10">Valor</td>
            <td class="td5">Prazo</td>
            <td class="td5">
                <div class="checkbox">
                    <input id="chkAllFact" type="checkbox" name="chkAllFact" class="chk">
                    <label for="chkAllFact" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="td10">Comissão</td>
            <td class="td10">Juros</td>
            <td class="td10">Seguro</td>
            <td class="td5" style="background-color: transparent;"></td>
        </tr>
        <tr>
            <td style="padding: 3px;">Total</td>
            <td style="padding: 3px;">-</td>
            <td style="padding: 3px;">-</td>
            <td style="padding: 3px;"></td>
            <td style="padding: 3px;"></td>
            <td style="padding: 3px;"></td>
            <td style="padding: 3px;">-</td>
            <td style="padding: 3px;"></td>
            <td style="padding: 3px;"></td>
            <td style="padding: 3px;"></td>
        </tr>
    </table>
    <div class="linha" style="text-align: center;">
        <button id="btnEfetuarFactoring" name="btnEfetuarFactoring" class="btnNoIco" style="float: none; margin-top: 10px;">Efetuar factoring</button>
    </div>
    <div id="frmFactoring" name="frmFactoring">
        <div class="linha10" style="width: 50%;">
            <div class="esq40">
                <label for="txtPlafondFact" class="labelNormal">Plafond disponível</label>
            </div>
            <div class="dir60">
                <div class="moneyarea_col1">
                    <input id="txtPlafondFact" name="txtPlafondFact" type="text" readonly="readonly" value="<?php echo number_format($linha_plafond_fact['valor'], 2, ',', '.'); ?>">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fact['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40">
                <label for="slcNumFatFact" class="labelNormal">Número da fatura</label>
            </div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcNumFatFact" name="slcNumFatFact" size="1" class="select">
                            <option value="0" selected="selected">- Fatura -</option>
                            <?php
                            if ($num_fatura > 0) {
                                //while ($linha_fatura = $query_fatura->fetch(PDO::FETCH_ASSOC)) {
                                foreach ($linha_fatura as $lf) {
                                    ?>
                                    <option value="<?php echo $lf['id_fatura']; ?>"><?php echo $lf['num_fatura']; ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40"><label for="txtClienteFact" class="labelNormal">Cliente</label></div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <input id="txtClienteFact" name="txtClienteFact" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40"><label for="txtDataFact" class="labelNormal">Data</label></div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <input id="txtDataFact" name="txtDataFact" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40"><label for="txtValorFact" class="labelNormal">Valor</label></div>
            <div class="dir60">
                <div class="moneyarea_col1">
                    <input id="txtValorFact" name="txtValorFact" type="text" readonly="readonly">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fact['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40">
                <label for="slcTempoFact" class="labelNormal">Prazo de validade</label>
            </div>
            <div class="dir60">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcTempoFact" name="slcTempoFact" size="1" class="select">
                            <option value="0" selected="selected">0</option>
                            <option value="6">6</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40">
                <label class="labelNormal">Com recurso</label>
            </div>
            <div class="dir60">
                <div class="checkbox">
                    <input id="chkRecursoFact" name="chkRecursoFact" type="checkbox" class="chk" value="1">
                    <label for="chkRecursoFact" class="label_chk">&nbsp;</label>
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40">
                <label for="txtComissaoFact" class="labelNormal">Comissões</label>
            </div>
            <div class="dir60">
                <div class="moneyarea_col1">
                    <input id="txtComissaoFact" name="txtComissaoFact" type="text" readonly="readonly" value="">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_query_comissao_factoring['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40">
                <label for="txtJurosFact" class="labelNormal">Juros (anual)</label>
            </div>
            <div class="dir60">
                <div class="moneyarea_col1">
                    <input id="txtJurosFact" name="txtJurosFact" type="text" readonly="readonly" value="">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_query_jurocr_factoring['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10" style="width: 50%;">
            <div class="esq40">
                <label for="txtSeguroFact" class="labelNormal">Prémio de seguro</label>
            </div>
            <div class="dir60">
                <div class="moneyarea_col1">
                    <input id="txtSeguroFact" name="txtSeguroFact" type="text" readonly="readonly" value="">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_query_seguro_factoring['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="linha" style="text-align: center;">
            <button id="btnAddFact" name="btnAddFact" class="btnNoIco" style="float: none; margin-top: 10px;">Adicionar/Confirmar</button>
        </div>
    </div>
    <input id="hddComissaoFact" name="hddComissaoFact" type="hidden" value="<?php echo $linha_query_comissao_factoring['valor'] * 100; ?>">
    <input id="hddJuroCRFact" name="hddJuroCRFact" type="hidden" value="<?php echo $linha_query_jurocr_factoring['valor'] * 100; ?>">
    <input id="hddJuroSRFact" name="hddJuroSRFact" type="hidden" value="<?php echo $linha_query_jurosr_factoring['valor'] * 100; ?>">
    <input id="hddSeguroFact" name="hddSeguroFact" type="hidden" value="<?php echo $linha_query_seguro_factoring['valor'] * 100; ?>">
</div>
<div id="consulta_factoring">
    <div class="linha">
        <div class="left-center-column">
            <h3>Consultar factorings</h3>
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
    <table id="tblConsultarFact" name="tblConsultarFact" class="tabela">
        <tr>
            <td class="td10">Data</td>
            <td class="td10">Recurso</td>
            <td class="td15">Prazo de validade</td>
            <td class="td20">Valor</td>
            <td class="td10">Comissão</td>
            <td class="td10">Seguro</td>
            <td class="td10">Juros</td>
            <td class="td15">Valor recebido</td>
        </tr>
        <?php while ($linha_factoring_cont = $query_factoring_cont->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo date("d-m-Y", strtotime($linha_factoring_cont['data'])); ?></td>
                <td><?php
                    if ($linha_factoring_cont['recurso'] == '1') {
                        echo "Sim";
                    } else {
                        echo "Não";
                    }
                    ?></td>
                <td><?php echo $linha_factoring_cont['tempo']; ?></td>
                <td><?php echo number_format($linha_factoring_cont['valor'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($linha_factoring_cont['comissao_valor'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($linha_factoring_cont['seguro_valor'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($linha_factoring_cont['juros_valor'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($linha_factoring_cont['valor_recebido'], 2, ',', '.'); ?></td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblConsultarFactVazia" name="tblConsultarFactVazia" class="tabela">
        <tr>
            <td>Não existem contratos de factoring</td>
        </tr>
    </table>
</div>
<div id="pag_prest">
    <div class="linha">
        <div class="left-column" style="width: 40%;">
            <h3>Pagamento de prestações</h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
    </div>
    <div id="radPrestacoes" class="linha" style="height: 30px; text-align: center;">
        <div class="radio">
            <input id="radEmprestimo" name="prestacao" type="radio" value="1" checked="checked">
            <label for="radEmprestimo" class="btnRadio">Empréstimos</label>
            <input id="radLeasing" name="prestacao" type="radio" value="2">
            <label for="radLeasing" class="btnRadio">Locações financeiras</label>
        </div>
    </div>
    <div class="linha">
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcOrdenarPrestacoes" name="slcOrdenarPrestacoes" size="1" class="select">
                    <option value="0" selected="selected">- Ordenar por -</option>
                    <option value="1">Prazo de pagamento</option>
                </select>
            </div>
        </div>
        <button id="btnPagarPrestacoes" name="btnPagarPrestacoes" class="btnNoIco" style="padding: 7px 20px; height: 30px;">Pagar</button>
    </div>
    <table id="tblPrestacoes" name="tblPrestacoes" class="tabela form_esq60" data-value="1">
        <tr>
            <td class="td5" style="background-color: #2b6db9; cursor: pointer;">
                <div class="checkbox">
                    <input id="chkAllPrest" name="chkAllPrest" type="checkbox" class="chk" value="0">
                    <label for="chkAllPrest" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="td30">Data</td>
            <td class="td30">Prazo pagamento</td>
            <td class="td35">Valor</td>
        </tr>
        <?php while ($linha_emprestimo = $query_emprestimo->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td style="background-color: transparent; padding: 4px; cursor: pointer;">
                    <div class="checkbox">
                        <input id="chkPrestacao_<?php echo $linha_emprestimo['id']; ?>" name="chkPrestacao" type="checkbox" class="chk" value="<?php echo $linha_emprestimo['id']; ?>">
                        <label for="chkPrestacao" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                        <input id="hddIdPrestacao" name="hddIdPrestacao" type="hidden" value="<?php echo $linha_emprestimo['id']; ?>">
                    </div>
                </td>
                <td style="padding: 4px;"><?php echo $linha_emprestimo['data_emprestimo']; ?></td>
                <td style="padding: 4px;"><?php echo $linha_emprestimo['data_limit_pag']; ?></td>
                <td style="padding: 4px;"><?php echo number_format($linha_emprestimo['valor'], 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblPrestacoesVazia" name="tblPrestacoesVazia" class="tabela">
        <tr>
            <td>Não existem empréstimos</td>
        </tr>
    </table>
</div>
<div id="pag_div">
    <div class="linha">
        <div class="left-column" style="width: 40%;">
            <h3>Pagamento de diversos</h3>
        </div>
        <div class="center-column">
            <div class="error"></div>
        </div>
    </div>
    <div class="linha" style="width: 25%;">
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcTipoDiversos" name="slcTipoDiversos" size="1" class="select">
                    <option value="0" selected="selected">- Escolha um tipo -</option>
                    <?php while ($linha_tipo_entrega = $query_tipo_entrega->fetch(PDO::FETCH_ASSOC)) { ?>
                        <?php /* A ELIMINAR */ if ($linha_tipo_entrega['designacao'] != "Declaração de Retenções" && $linha_tipo_entrega['designacao'] != "Declaração de Retenções (antigo)") { ?>
						<option value="<?php echo $linha_tipo_entrega['id']; ?>"><?php echo $linha_tipo_entrega['designacao']; ?></option>
						<?php } ?>
                    <?php } ?>
                </select>
                <span class="width_tmp"></span>
            </div>
        </div>
    </div>
    <div id="radOthersGroup" class="linha" style="width: 45%; height: 30px; text-align: center;">
        <div class="radio">
            <input id="radOutros" name="outros" type="radio" value="1" checked="checked">
            <label for="radOutros" class="btnRadio">Outros</label>
            <input id="radFaturas" name="outros" type="radio" value="2">
            <label for="radFaturas" class="btnRadio">Faturas</label>
            <input id="radDecRet" name="outros" type="radio" value="3">
            <label for="radDecRet" class="btnRadio">Declarações de retenções</label>
        </div>
    </div>
    <button id="btnPagarFatura" name="btnPagarFatura" class="btnNoIco" style="padding: 7px 20px; height: 30px;">Pagar</button>
    <button id="btnPagarEntrega" name="btnPagarEntrega" class="btnNoIco" style="padding: 7px 20px; height: 30px;">Pagar</button>
    <button id="btnPagarDecRet" name="btnPagarDecRet" class="btnNoIco" style="padding: 7px 20px; height: 30px;">Pagar</button>
    <table id="tblDiversos" name="tblDiversos" data-value="1" class="tabela">
        <tr>
            <td class="td5" style="background-color: #2b6db9; cursor: pointer;">
                <div class="checkbox">
                    <input id="chkAllDiv" name="chkAllDiv" type="checkbox" class="chk" value="0">
                    <label for="chkAllDiv" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="td25">Tipo</td>
            <td class="td15">Data</td>
            <td class="td15">Fora de prazo</td>
            <td class="td20">Valor</td>
            <td class="td10">Mês</td>
            <td class="td10">Ano</td>
        </tr>
    </table>
    <table id="tblFaturas" name="tblFaturas" data-value="0" class="tabela">
        <tr>
            <td class="td5" style="background-color: #2b6db9; cursor: pointer;">
                <div class="checkbox">
                    <input id="chkAllFat" name="chkAllFat" type="checkbox" class="chk" value="0">
                    <label for="chkAllFat" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="td20">Referência</td>
            <td class="td15">Fornecedor</td>
            <td class="td15">País</td>
            <td class="td15">Data</td>
            <td class="td15">IVA</td>
            <td class="td15">Total</td>
			<td class="td5" style="background-color: transparent;">&nbsp;</td>
        </tr>
    </table>
    <table id="tblDecRet" name="tblDecRet" class="tabela form_esq60" data-value="0">
        <tr>
            <td class="td5" style="background-color: #2b6db9; cursor: pointer;">
                <div class="checkbox">
                    <input id="chkAllDecRet" name="chkAllDecRet" type="checkbox" class="chk" value="0">
                    <label for="chkAllDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="td20">Data limite</td>
            <td class="td20">Não residentes</td>
            <td class="td55">Total</td>
        </tr>
    </table>
    <table id="tblVazia" name="tblVazia" class="tabela"></table>
</div>

<div id="criarcontaprazo">
    <div class="linha">
        <div class="left-column">
            <h3>Criar conta a Prazo</h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>

    <!-- <div class="linha">  *Espaço entre linhas* </div> -->

    <div class="linha">
        <h4>Empresa</h4>

        <div class="inputarea_col1" style="background-color: inherit;">
            <input id="hddIdEmp" name="hddIdEmp" type="hidden" value="<?php echo $linha_empresa['id_empresa']; ?>"></input>
            <input id="empNome" name="empNome" type="text" readonly="readonly" style="font-size: 11pt; text-align: left;" value="<?php echo $linha_empresa['nome']; ?>"> </input>
        </div>
    </div>

    <div class="linha">
        <h4>Banco</h4>

        <div class="inputarea_col1" style="background-color: inherit;">
            <input id="hddIdBanco" name="hddIdBanco" type="hidden" value="<?php echo $linha_banco['id']; ?>" ></input>
            <input id="banco" name="banco" type="text" readonly="readonly" style="font-size: 11pt; text-align: left;" value="<?php echo $linha_banco['nome']; ?>" ></input>
        </div>
    </div>

    <div class="linha">
        <div class="esq12" style="width: 42px;">
            <div class="mnyLabel" style="float: left; width: 42px;">
                <input name="IBAN" type="text" readonly="readonly" value="IBAN">
            </div>
        </div>

        <div class="dir88">
            <div class="inputarea_col1" style="margin-left: 2px; width: 215px; margin-right: 0px;">
                <input id="txtNIBAN" name="txtNIBAN" type="text" readonly="readonly" style="text-align: left;" placeholder="International Bank Account Number"></input>
            </div>
            <button id="btnGenIBAN" name="btnGenIBAN" class="btnNoIco" style="padding: 7px 7px; width: 69px; height: 30px;">Gerar</button>
        </div>
    </div>

    <div class="linha">
        <div class="esq12" style="width: 42px;">
            <div class="mnyLabel" style="float: left; width: 42px;">
                <input name="NIB" type="text" readonly="readonly" value="NIB">
            </div>
        </div>

        <div class="dir88">
            <div class="inputarea_col1" style="margin-left: 2px; width: 215px; margin-right: 0px;">
                <input id="txtNNIB" name="txtNNib" type="text" readonly="readonly" style="text-align: left;" placeholder="Número Identificação Bancária"></input>
            </div>
        </div>
    </div>

    <div class="linha">
        <div class="esq12" style="width: 42px;">
            <div class="mnyLabel" style="float: left; width: 42px;">
                <input name="NConta" type="text" readonly="readonly" value="Nº C.">
            </div>
        </div>

        <div class="dir88">
            <div class="inputarea_col1" style="margin-left: 2px; width: 215px; margin-right: 0px;">
                <input id="txtNNC" name="txtNNC" type="text" readonly="readonly" style="text-align: left;" placeholder="Número da Conta"></input>
            </div>
        </div>
    </div>

    <div class="linha">  <!-- *Espaço entre linhas* --> </div>
    
    <div class="linha">
        <div id="subtituloDP">
            <h4>Valores de Entrada</h4>
        </div>
    </div>

    <!-- Depósito a Prazo -->
    <div class='linha'>
        <div id="MontanteDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="montante" type="text" readonly="readonly" value="Inicial">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='txtMontanteDP' name='txtMontanteDP' type='text' size='6' class='dinheiro' style="font-family: 'helvetica-light';" placeholder="Depósito de Entrada">
                    <div class='mnyLabel'>
                        <input name='txtMoeda' type='text' readonly='readonly' value="<?php echo $linha_moeda['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='linha'>
        <div id="slcPrazoDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="txtPrazo" type="text" readonly="readonly" value="Prazo">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 160px; margin-right: 0px;">
                    <div class='styled-select'>
                        <select id='slcPrazo' name='slcPrazo' size='1' class='select'>
                            <?php for ($i=1; $i<=12; $i++) { echo "<option value=$i> $i </option>"; } ?>
                        </select>
                    </div>
                </div>
                <div style='float: left; height: 30px; margin-left: 0.5%;'>
                    <input style='font-size: 10pt; width: 150px;' class='inputNoBackground' type='text' value='[meses]' readonly='readonly'>
                </div>
            </div>
        </div>
    </div>

    <div class='linha'>
        <div id="txJuroDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="txtJuro" type="text" readonly="readonly" value="Juro">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='txtJuros' name='txtJuros' type='text' readonly='readonly' value="<?php echo $tx_juro['valor']; ?>">
                    <div class='mnyLabel'>
                        <input name='txtPercent' type='text' readonly='readonly' value='%'>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='linha'>
        <div id="txIRCDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="txtIRC" type="text" readonly="readonly" value="IRC">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='txIRC' name='txIRC' type='text' readonly='readonly' value="<?php echo $tx_irc['valor']; ?>">
                    <div class='mnyLabel'>
                        <input name='txtPercent' type='text' readonly='readonly' value='%'>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='linha'>
        <div id="TotalDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="txtTotal" type="text" readonly="readonly" value="Total">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='txtTotalDP' name='txtTotalDP' type='text' size='6' class='dinheiro' readonly='readonly'>
                    <div class='mnyLabel'>
                        <input name='txtMoeda' type='text' readonly='readonly' value="<?php echo $linha_moeda['simbolo']; ?>">
                    </div>
                </div>
                <button id='btnCalcularTotal' name='btnCalcularTotal' class='btnNoIco' style='font-size: 7pt; padding: 7px 7px; width: 69px; height: 30px;'> Calcular </button>
            </div>
        </div>
    </div>

    <div class="linha">
        <button id='btnAddDP' name='btnAddDP' class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 15px;"> Seguinte </button>
    </div>
	
	<div class="linha10">
        <div id="contrato_contaprazo" style="width: 90%;">
            
            <div class="linha10">
                <div class="left-column">
                    <div class="error"></div>
                </div>
            </div>
            
            <div id="contrato">
                <div class="checkbox" style="float: left;">
                    <input id="chkDP" name="chkDP" type="checkbox" class="chk">
                    <label for="chkDP" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>

                &nbsp; Declaro(amos) que aceito(amos) as Condições Gerais, as quais me(nos) foram devidamente explicadas e 
                das quais fiquei(ficámos) devidamente ciente(s), procedendo, em consequência e nesta data, à sua plena aceitação.
                <br><br>
            </div>
            
            <div id="btnContaPrazo">
                <button id="btnCriarCPrazo" name="btnCriarCPrazo" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 15px;"> Aceitar </button>
                <button id="btnCancelDP" name="btnCancelDP" class="btn btn-3 btn-3a icon-clean" style="float: none; margin-top: 15px;"> Cancelar </button>
            </div>
        </div>
    </div>
</div>

<div id="nib_contaprazo">
    <div class="linha">
        <div class="left-column">
            <h3>Dados da conta a Prazo</h3>
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
        <table id="tblIbanCP" name="tblIbanCP" class="tabela form_esq35">
            <tr>
                <td style="background-color: #2b6db9; text-align: center; color: #fff;">Nº</td>
                <td style="text-align: left; background-color: #ccd8e8; color: #000;">
                    <?php
                        if ($linha_contaP) {
							$num_contaP_raw = str_split($linha_contaP['num_conta'], 4);
							$num_contaP = $num_contaP_raw[0] . " " . $num_contaP_raw[1] . " " . $num_contaP_raw[2];
							echo $num_contaP;
						}
                    ?>
                </td>
            </tr>
            <tr>
                <td style="background-color: #2b6db9; text-align: center; color: #fff;">NIB</td>
                <td style="text-align: left; background-color: #ccd8e8; color: #000;">
                    <?php
                        if ($linha_contaP) {
							$nibP_raw = str_split($linha_contaP['nib']);
							$nibP = $nibP_raw[0] . $nibP_raw[1] . $nibP_raw[2] . $nibP_raw[3] . " " . $nibP_raw[4] . $nibP_raw[5] . $nibP_raw[6] . $nibP_raw[7] . " " . $nibP_raw[8] . $nibP_raw[9] . $nibP_raw[10] . $nibP_raw[11] . $nibP_raw[12] . $nibP_raw[13] . $nibP_raw[14] . $nibP_raw[15] . $nibP_raw[16] . $nibP_raw[17] . $nibP_raw[18] . " " . $nibP_raw[19] . $nibP_raw[20];
							echo $nibP;
						}
                    ?>
                </td>
            </tr>
            <tr>
                <td style="background-color: #2b6db9; text-align: center; color: #fff;">IBAN</td>
                <td style="text-align: left; background-color: #ccd8e8; color: #000;">
                    <?php
                        if ($linha_contaP) {
							$ibanP_raw = str_split($linha_contaP['iban'], 4);
							$ibanP = $ibanP_raw[0] . " " . $ibanP_raw[1] . " " . $ibanP_raw[2] . " " . $ibanP_raw[3] . " " . $ibanP_raw[4] . " " . $ibanP_raw[5] . " " . $ibanP_raw[6];
							echo $ibanP;
						}
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<div id="plano_deposito_prazo">
    <div class="linha">
        <div class="left-center-column">
            <h3> Plano de Depósito a Prazo </h3>
        </div>
    </div>
    <div class="loading" style="width: 150px;">
        <div class="linha10">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha" style="text-align: center;">A carregar...</div>
    </div>
    <div class="linha"></div>
    <table id="tblDP" name="tblDP" class="tabela">
        <tr>
            <td class="td10" style="padding: 6px;">Depósito</td>
            <td class="td10" style="padding: 6px;">Movimento</td>
            <td class="td10" style="padding: 6px;">Montante</td>
            <td class="td10" style="padding: 6px;">Taxa de Juro</td>
            <td class="td10" style="padding: 6px;">Valor Juros</td>
            <td class="td10" style="padding: 6px;">Taxa IRC</td>
            <td class="td10" style="padding: 6px;">IRC</td>
            <td class="td10" style="padding: 6px;">Juro Liquido</td>
            <td class="td10" style="padding: 6px;">Pago</td>
            <td class="td10" style="padding: 6px;">Data Valor</td>
            <td class="td5" style="background-color: transparent;"></td>
        </tr>
        <?php 
        $montante_ant = 0;
        $deposito = 1;
        while ($linha_plano_dp = $query_plano_dp->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $linha_plano_dp['deposito']; ?></td>
                <td><?php echo $linha_plano_dp['prestacao']; ?></td>
                <td><?php echo number_format($linha_plano_dp['montante'], 2, ',', '.').''.$linha_moeda['simbolo']; ?></td>
                <td><?php echo $linha_plano_dp['tx_juro'].'%'; ?></td>
                <?php
                    $tx_juro_m = pow((1 + $linha_plano_dp['tx_juro'] / 100), (1/12)) - 1;
                    $juro_brut = floatval($linha_plano_dp['montante'] * $tx_juro_m);
                ?>
                <td><?php echo number_format($juro_brut, 2, ',', '.').''.$linha_moeda['simbolo']; ?></td>
                
                <td><?php echo $linha_plano_dp['tx_irc'].'%'; ?></td>
                <td><?php echo number_format($linha_plano_dp['irc'], 2, ',', '.').''.$linha_moeda['simbolo']; ?></td>
                <td><?php echo number_format($linha_plano_dp['valor'], 2, ',', '.').''.$linha_moeda['simbolo']; ?></td>
                <td><?php if($linha_plano_dp['pago'] == 0) echo "Não"; else echo "Sim"; ?></td>
                <td><?php echo date("d-m-Y", strtotime($linha_plano_dp['data_lim_v'])); ?></td>
                <?php if ($linha_plano_dp['deposito'] == $deposito && $linha_plano_dp['montante'] > $montante_ant || $linha_plano_dp['deposito'] > $deposito) { ?>
                    <td style="background-color: transparent; padding: 0; cursor: pointer;">
                        <a href="./impressao/financ_dep_prazo.php?id=<?php echo $linha_plano_dp['id_juro']; ?>" target="_blank">
                            <img width="33" height="33" src="images/adobe_logo.png">
                        </a>
                    </td>
                <?php } ?>
            </tr>
        <?php 
            $deposito = $linha_plano_dp['deposito'];
            $montante_ant = $linha_plano_dp['montante']; }
        ?>
    </table>
</div>

<div id="mov_contaprazo">
    <div class="linha">
        <div class="left-center-column">
            <h3><?php echo $lingua['A_BALANCE']; ?> (<?php echo $linha_moeda['ISO4217']; ?>)</h3>
        </div>
    </div>
    <div class="loading" style="width: 150px;">
        <div class="linha10">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha" style="text-align: center;">A carregar...</div>
    </div>
    <div class="linha"></div>
    <table id="tblMovContaP" name="tblMovContaP" class="tabela">
        <tr>
            <td class="td10" style="padding: 6px;"><?php echo $lingua['DATE']; ?></td>
            <td class="td5" style="padding: 6px;"><?php echo $lingua['TYPE']; ?></td>
            <td class="td44" style="padding: 6px;"><?php echo $lingua['DESCRIPTION']; ?></td>
            <td class="td12" style="padding: 6px;"> Valor </td>
            <td class="td12" style="padding: 6px;"><?php echo $lingua['BALANCE']; ?></td>
            <td class="td5" style="background-color: transparent; padding: 6px;">&nbsp;</td>
        </tr>
        <?php while ($linha_mov_contaP = $query_mov_contaP->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo date("d-m-Y", strtotime($linha_mov_contaP['data_op'])); ?></td>
                <td><?php echo $linha_mov_contaP['tipo']; ?></td>
                <td><?php echo $linha_mov_contaP['descricao']; ?></td>
                <td><?php echo number_format($linha_mov_contaP['credito'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($linha_mov_contaP['saldo_disp'], 2, ',', '.'); ?></td>
            </tr>
        <?php } ?>
    </table>
</div>

<div id="renovardp">
    <div class="linha">
        <div class="left-column">
            <h3> Renovar Depósito a Prazo </h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> </div>
    
    <div class='linha'>
        <div id="renMontanteDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="saldo" type="text" readonly="readonly" value="Saldo">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='IdConta' name='IdConta' type='hidden' value="<?php echo $saldo_cp['id']; ?>">
                    <input id='txtSaldoCP' name='txtSaldoCP' type='text' readonly='readonly' value="<?php echo number_format($saldo_cp['saldo_disp'], 2, ',', '.'); ?>">
                    <div class='mnyLabel'>
                        <input name='txtMoeda' type='text' readonly='readonly' value="<?php echo $linha_moeda['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='linha'>
        <div id="txJuroRDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="txtJuro" type="text" readonly="readonly" value="Juro">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='txtRJuros' name='txtJuros' type='text' readonly='readonly' value="<?php echo $tx_juro['valor']; ?>">
                    <div class='mnyLabel'>
                        <input name='txtPercent' type='text' readonly='readonly' value='%'>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='linha'>
        <div id="txIRCRDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="txtIRC" type="text" readonly="readonly" value="IRC">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='txRIRC' name='txRIRC' type='text' readonly='readonly' value="<?php echo $tx_irc['valor']; ?>">
                    <div class='mnyLabel'>
                        <input name='txtPercent' type='text' readonly='readonly' value='%'>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='linha'>
        <div id="renSlcPrazoDP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="txtPrazo" type="text" readonly="readonly" value="Prazo">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 160px; margin-right: 0px;">
                    <div class='styled-select'>
                        <select id='slcPrazoRCP' name='slcPrazoRCP' size='1' class='select'>
                            <?php for ($i=1; $i<=12; $i++) { echo "<option value=$i> $i </option>"; } ?>
                        </select>
                    </div>
                </div>
                <div style='float: left; height: 30px; margin-left: 0.5%;'>
                    <input style='font-size: 10pt; width: 150px;' class='inputNoBackground' type='text' value='[meses]' readonly='readonly'>
                </div>
            </div>
        </div>
    </div>
	
	<div class="linha">
        <button id='btnRenovDP' name='btnRenovDP' class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 15px;"> Seguinte </button>
    </div>

    <div class="linha10">
        <div id="contrato_rencontaprazo" style="width: 90%;">
            <div id="contratoren">
                <div class="checkbox" style="float: left;">
                    <input id="chkrenDP" name="chkrenDP" type="checkbox" class="chk">
                    <label for="chkrenDP" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>

                &nbsp; Declaro(amos) que aceito(amos) as Condições Gerais, as quais me(nos) foram devidamente explicadas e 
                das quais fiquei(ficámos) devidamente ciente(s), procedendo, em consequência e nesta data, à sua plena aceitação.
                <br><br>
            </div>

            <div id="btnRenContaPrazo">
                <button id="btnRenCPrazo" name="btnRenCPrazo" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 15px;"> Aceitar </button>
                <button id="btnCancelRenDP" name="btnCancelRenDP" class="btn btn-3 btn-3a icon-clean" style="float: none; margin-top: 15px;"> Cancelar </button>
            </div>
        </div>
    </div>
</div>

<div id="terminardp">
    <div class="linha">
        <div class="left-center-column">
            <h3> Terminar Depósito a Prazo </h3>
        </div>
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> </div>
    
    <div class='linha'>
        <div id="saldoCP">
            <div class="esq12" style="width: 42px;">
                <div class="mnyLabel" style="float: left; width: 42px;">
                    <input name="saldo" type="text" readonly="readonly" value="Saldo">
                </div>
            </div>
            <div class='dir88'>
                <div class='moneyarea_col1' style="margin-left: 2px; width: 215px; margin-right: 0px;">
                    <input id='SaldoCP' name='SaldoCP' type='text' readonly='readonly' value="<?php echo number_format($saldo_cp['saldo_disp'], 2, ',', '.'); ?>">
                    <div class='mnyLabel'>
                        <input name='txtMoeda' type='text' readonly='readonly' value="<?php echo $linha_moeda['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="linha">
        <button id='btnTerminarCP' name='btnTerminarCP' class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 15px;"> Seguinte </button><!-- <button id='btnLimparDP' name='btnLimparDP' class='btnNoIco' style='float: none; margin-top: 10px;'> Limpar </button> -->
    </div>

    <div class="linha10">
        <div id="contrato_termcontaprazo" style="width: 90%;">
            <div id="contratoren">
                Está prestes a terminar o Depósito a Prazo. O saldo da Conta a Prazo será creditado na sua conta a ordem, enquanto que sua
                conta a prazo será excluida dos seus registos e consequentemente deixará de receber os juros mensais. <br><br>
                
                <div class="checkbox" style="float: left;">
                    <input id="chktermDP" name="chkrenDP" type="checkbox" class="chk">
                    <label for="chktermDP" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
                
                &nbsp; Ao prosseguir, declaro(amos) que aceito(amos) as Condições Gerais, as quais me(nos) foram devidamente explicadas e 
                das quais fiquei(ficámos) devidamente ciente(s), procedendo, em consequência e nesta data, à sua plena aceitação.
                <br><br>
            </div>

            <div id="btnTermContaPrazo">
                <button id="btnTermCPrazo" name="btnTermCPrazo" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 15px;"> Aceitar </button>
                <button id="btnCancelTermDP" name="btnCancelTermDP" class="btn btn-3 btn-3a icon-clean" style="float: none; margin-top: 15px;"> Cancelar </button>
            </div>
        </div>
    </div>
</div>

<div id="desconto_letra">
    <div class="linha">
        <div class="left-column">
            <h3> Desconto de Letra/Livrança </h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="divSwitchInscricoes">
        <div class="esq12">
            <label for="chkSacadoLet">Sacado Interno</label>
        </div>
        <div class="dir88">
            <div class="onoffswitch">
                <input id="chkSacadoLet" name="chkSacadoLet" type="checkbox" class="onoffswitch-checkbox" checked>
                <label class="onoffswitch-label" for="chkSacadoLet">
                    <div class="onoffswitch-inner"></div>
                    <div class="onoffswitch-switch"></div>
                </label>
            </div>
        </div>
    </div>
    
    <div class="linha10"> <!-- *Espaço entre linhas* --> &nbsp; </div>
    
    <!-- Apresentação para Letras a clientes Externos (em que parametros vêm da fatura) -->
    <div id="letClienteExt">
        <div class="linha10">
            <div class="esq20">
                <label for="slcNumFatLet" class="labelNormal">Número da fatura*</label>
            </div>
            <div class="dir80">
                <div class="inputarea_col1">
                    <div class="styled-select">
                        <select id="slcNumFatLet" name="slcNumFatLet" size="1" class="select">
                            <option value="0" selected="selected">- Fatura -</option>
                            <?php
                            if ($num_fatura > 0) {
                                foreach ($linha_fatura as $lf2) { ?>
                                    <option value="<?php echo $lf2['id_fatura']; ?>"><?php echo $lf2['num_fatura']; ?></option>
								<?php }
                            } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq20"><label for="txtClienteLetExt" class="labelNormal">Cliente</label></div>
            <div class="dir80">
                <div class="inputarea_col1">
                    <input id="txtClienteLetExt" name="txtClienteLetExt" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq20"><label for="txtDataLet" class="labelNormal">Data Limite</label></div>
            <div class="dir80">
                <div class="inputarea_col1">
                    <input id="txtDataLet" name="txtDataLet" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq20"><label for="txtPrazoMax" class="labelNormal">Prazo máximo</label></div>
            <div class="dir80">
                <div class="inputarea_col1">
                    <input id="txtPrazoMax" name="txtPrazoMax" type="text" readonly="readonly">
                </div>
                <div style='float: left; height: 30px; margin-left: 0.5%;'>
                    <input style='font-size: 10pt; width: 150px;' class='inputNoBackground' type='text' value='[dias]' readonly='readonly'>
                </div>
            </div>
        </div>
        <div class="linha10">
            <div class="esq20"><label for="txtTotalLetExt" class="labelNormal">Valor Factura</label></div>
            <div class="dir80">
                <div class="moneyarea_col1">
                    <input id="txtTotalLetExt" name="txtTotalLetExt" type="text" class="dinheiro" readonly="readonly">
                    <div class="mnyLabel">
                        <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fact['simbolo']; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- -->
    
    <!-- Apresentação para Letras a clientes Internos (em que user escolhe o sacado apartir das empresas registadas) -->
    <div id="letClienteInt">
        <div class="linha10">
            <div class="esq20"><label for="txtClienteLetInt" class="labelNormal">Empresa*</label></div>
            <div class="dir80">
                <div class="inputarea_col1">
                    <input id="txtClienteLetInt" name="txtClienteLetInt" type="text" readonly="readonly">
                    <input id="hddIdEmpresaSacado" name="hddIdEmpresaSacado" type="hidden">
                </div>
                <button id="btnSacadoLet" name="btnSacadoLet" class="btnNoIco" style="padding: 7px 20px; height: 30px;"><?php /*echo $lingua[''];*/ echo "Sacado"; ?></button>
            </div>
        </div>
    </div>
    <!-- -->
    
    <div class="linha10">
        <div class="esq20"><label for="txtValorLet" class="labelNormal">Valor Letra*</label></div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtValorLet" name="txtValorLet" type="text" class="dinheiro">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fact['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20"><label for="txtPrazoLet" class="labelNormal">Prazo*</label></div>
        <div class="dir80">
            <div class="inputarea_col1">
                <input id="txtPrazoLet" name="txtPrazoLet" type="text">
            </div>
            <div style='float: left; height: 30px; margin-left: 0.5%;'>
                <input style='font-size: 10pt; width: 150px;' class='inputNoBackground' type='text' value='[dias]' readonly='readonly'>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20">
            <label for="txtISLet" class="labelNormal">Imposto Selo</label>
        </div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtISLet" name="txtISLet" type="text" readonly="readonly" value="<?php echo $linha_imp_letra['valor']; ?>">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_imp_letra['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20">
            <label for="txtComissaoLet" class="labelNormal">Comissões</label>
        </div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtComissaoLet" name="txtComissaoLet" type="text" readonly="readonly" value="<?php echo $linha_comissao_letra['valor']; ?>">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_comissao_letra['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20">
            <label for="txtJurosLet" class="labelNormal">Taxa Juros (anual)</label>
        </div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtJurosLet" name="txtJurosLet" type="text" readonly="readonly" value="<?php echo $linha_juro_letra['valor']; ?>">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_juro_letra['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20"><label for="txtEncargosLet" class="labelNormal">Encargos</label></div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtEncargosLet" name="txtEncargosLet" type="text" class="dinheiro" readonly="readonly">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fact['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20"><label for="txtValorLiqLet" class="labelNormal">Valor a receber</label></div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtValorLiqLet" name="txtValorLiqLet" type="text" class="dinheiro" readonly="readonly">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_fact['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    
    <div class="linha" style="text-align: center;">
        <button id="btnAddLet" name="btnAddLet" class="btnNoIco" style="float: none; margin-top: 10px;">Adicionar/Confirmar</button>
    </div>
</div>

<div id="aceite_letra">
    <div class="linha">
        <div class="left-column">
            <h3> Aceite de Letra/Livrança </h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <table id="tblLetras" name="tblLetras" data-value="1" class="tabela">
        <tr>
            <td class="td15"><?php echo "Empresa"; //$lingua['']; ?></td>
            <td class="td15"><?php echo "Data constituição"; //$lingua['']; ?></td>
            <td class="td15"><?php echo "Data pagamento"; //$lingua['']; ?></td>
            <td class="td15"><?php echo "Valor"; //$lingua['']; ?></td>
            <td class="td15"><?php echo "Pago"; //$lingua['']; ?></td>
            <td class="td10"><?php echo "Aceita pagar?"; //$lingua['']; ?></td>
        </tr>
        
        <?php while ($linha_letra_aceite = $query_letra_aceite->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td class="td15"> <?php echo $linha_letra_aceite['nome']; ?> </td>
                <td class="td15"> <?php echo date("d-m-Y", strtotime($linha_letra_aceite['data_virt'])); ?> </td>
                <td class="td15"> <?php echo date("d-m-Y", strtotime($linha_letra_aceite['data_lim_v'])); ?> </td>
                <td class="td15"> <?php echo number_format($linha_letra_aceite['valor'], 2, ',', '.').$linha_moeda['simbolo']; ?> </td>
                <td class="td15"> <?php if ($linha_letra_aceite['pago'] == 0) echo "Não"; else echo "Sim"; ?> </td>
                <td class="td10">
                    <div class="checkbox">
                        <input id="<?php echo "chkAceiteLetra_". $linha_letra_aceite['id_letra']; ?>" name="<?php echo "chkAceiteLetra_". $linha_letra_aceite['id_letra']; ?>" type="checkbox" class="chk" value="<?php echo $linha_letra_aceite['id_letra']; ?>" <?php if ($linha_letra_aceite['aceite'] == 1) echo "checked"; ?> >
                        <label for="<?php echo "chkAceiteLetra_". $linha_letra_aceite['id_letra']; ?>" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>
    
    <table id="tblLetraVazia" name="tblMovVazia" class="tabela">
        <tr>
            <td>Não existem letras sobre a sua empresa</td>
        </tr>
    </table>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha10" style="text-align: center;">
        <button id="btnAceitaLetra" name="btnAceitaLetra" class="btnNoIco" style="float: none; margin-top: 10px;"><?php echo "Confirmar"; //$lingua['TRANSFER']; ?></button>
    </div>
</div>

<div id="carteira_letra">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['T_CREDIT_PORTFOLIO']; ?></h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <table id="tblCarteiraLetras" name="tblLetras" data-value="1" class="tabela">
        <tr>
            <td class="td10"><?php echo "Empresa/Fatura"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Imposto Selo"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Comissões"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Juros"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Valor"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Data constituição"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Data pagamento"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Aceita pagar?"; //$lingua['']; ?></td>
            <td class="td5"><?php echo "Pago"; //$lingua['']; ?></td>
        </tr>
        
        <?php while ($linha_carteira_letra = $query_carteira_letra->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td class="td10">
                    <?php 
                        if ($linha_carteira_letra['empresa'] == "") echo $linha_carteira_letra['num_fatura'];
                        else echo $linha_carteira_letra['empresa'];
                    ?>
                </td>
                <td class="td5"> <?php echo number_format($linha_carteira_letra['imp_s'], 2, ',', '.').$linha_moeda['simbolo']; ?> </td>
                <td class="td5"> <?php echo number_format($linha_carteira_letra['com'], 2, ',', '.').$linha_moeda['simbolo']; ?> </td>
                <td class="td5"> <?php echo number_format($linha_carteira_letra['juro'], 2, ',', '.').$linha_moeda['simbolo']; ?> </td>
                <td class="td5"> <?php echo number_format($linha_carteira_letra['valor'], 2, ',', '.').$linha_moeda['simbolo']; ?> </td>
                <td class="td5"> <?php echo date("d-m-Y", strtotime($linha_carteira_letra['data_virt'])); ?> </td>
                <td class="td5">
                    <?php
                        if ($linha_carteira_letra['data_lim_v'] == "") echo date("d-m-Y", strtotime($linha_carteira_letra['data_virt'])); 
                        else echo date("d-m-Y", strtotime($linha_carteira_letra['data_lim_v']));
                    ?>
                </td>
                <td class="td5"> <?php if ($linha_carteira_letra['aceite'] == 0) echo "Não"; else echo "Sim"; ?> </td>
                <td class="td5"> <?php if ($linha_carteira_letra['pago'] == 0) echo "Não"; else echo "Sim"; ?> </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblCarteiraLetraVazia" name="tblMovVazia" class="tabela">
        <tr>
            <td>Não possui nenhuma letra</td>
        </tr>
    </table>
</div>

<!-- Possibilidade de haver aumentos de capitais, pelo utilizador, à sua própria empresa. -->
<div id="recebimentos">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['RECEIPTS']; ?></h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha">
        <div class="esq20">
            <label for="txtPlafond" class="labelNormal">Plafond disponível</label>
        </div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtPlafond" name="txtPlafond" type="text" value="<?php echo number_format($linha_plafond_aumento['valor'], 2, ',', '.'); ?>" readonly="readonly">
                <input id="hddIdRegra" name="hddIdRegra" type="hidden" value="<?php echo $linha_plafond_aumento['id_regra']; ?>">
                <input id="hddIdBanco" name="hddIdBanco" type="hidden" value="<?php echo $linha_plafond_aumento['id_banco']; ?>">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_aumento['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20">
            <label for="txtNomeEmpresa" class="labelNormal"><?php echo $lingua['COMPANY']; ?></label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <input id="txtNomeEmpresa" name="txtNomeEmpresa" type="text" value="<?php echo $linha_plafond_aumento['nome']; ?>" readonly="readonly">
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20">
            <label for="txtNrConta" class="labelNormal"><?php echo $lingua['ACCOUNT']; ?></label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <input id="txtNrConta" name="txtNrConta" type="text" value="<?php echo '0' . $linha_plafond_aumento['num_conta']; ?>" readonly="readonly">
                <input id="hddIdConta" name="hddIdConta" type="hidden" value="<?php echo $linha_plafond_aumento['id']; ?>">
            </div>
        </div>
    </div>
    <div class="linha">
        <div class="esq20">
            <label for="slcDescAumento" class="labelNormal"><?php echo $lingua['DESCRIPTION']; ?></label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <div class="styled-select">
                    <select id="slcDescAumento" name="slcDescAumento" size="1" class="select">
                        <option value="0" selected="selected">- Escolha um tipo -</option>
                        <option value="Adiantamento do cliente"> Adiantamento de cliente </option>
                        <option value="Aumento de capital"> Aumento de capital </option>
                        <option value="Empréstimo obrigacionista"> Empréstimo obrigacionista </option>
                        <option value="Subsídio"> Subsídio </option>
                        <option value="Suprimento"> Suprimento </option>
                        <option value="Estorno"> Estorno </option>
                        <option value="Outros"> Outros </option>
                    </select>
                </div>
                <input id="hddLimiteAdiantamento" name="hddLimiteAdiantamento" type="hidden" value="<?php echo $linha_limite_adiantamento["valor"]; ?>">
            </div>
            
            <div id='limiteAdiantamento' style='float: left; height: 30px; margin-left: 0.5%;'>
                <input style='font-size: 10pt; width: 500px;' class='inputNoBackground' type='text' value='<?php echo "[Limite está definido em ". $linha_limite_adiantamento["valor"].$linha_limite_adiantamento["simbolo"] ." do Plafond: " .number_format($linha_plafond_aumento['valor']*$linha_limite_adiantamento["valor"]/100, 2, ',', '.'). "€]"?>' readonly='readonly'>
            </div>
            
        </div>
    </div>
    <div id="clienteAdiantamento" class="linha">
        <div class="esq20">
            <label for="txtCliente" class="labelNormal"><?php echo $lingua['CLIENT']; ?></label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <input id="txtCliente" name="txtCliente" type="text">
            </div>
        </div>
    </div>
    <div class="linha">
        <div class="esq20">
            <label for="txtValorAumento" class="labelNormal"><?php echo $lingua['TOTAL_VAL']; ?></label>
        </div>
        <div class="dir80">
            <div class="moneyarea_col1">
                <input id="txtValorAumento" name="txtValorAumento" type="text" class="dinheiro">
                <div class="mnyLabel">
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_aumento['simbolo']; ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="linha" style="text-align: center;">
        <button id="btnRegAumento" class="btn btn-3 btn-3a icon-ok" style="float: none; margin-top: 10px;"><?php echo $lingua['REGISTER']; ?></button>
    </div>
</div>

<div id="adiant_receb">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['ADVANCE']; echo " "; echo lcfirst($lingua['RECEIVED']); ?></h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha">
        <table id="tblAdiantReceb" name="tblAdiantReceb" class="tabela">
            <tr>
                <td class="td25" style="padding: 6px;"><?php echo /*$lingua[''];*/ "Nome do cliente"; ?></td>
                <td class="td25" style="padding: 6px;"><?php echo /*$lingua[''];*/ "Valor por liquidar"; ?></td>
                <td class="td25" style="padding: 6px;"><?php echo /*$lingua[''];*/ "Data de adiantamento"; ?></td>
            </tr>
            <?php while ($linha_adiant_receb = $query_carrega_adiant_receb->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td class="td25" style="padding: 6px;"><?php echo $linha_adiant_receb['nome_cliente']; ?></td>
                    <td class="td25" style="padding: 6px;"><?php echo number_format($linha_adiant_receb['valor'], 2, ',', '.').' '.$linha_moeda['simbolo']; ?></td>
                    <td class="td25" style="padding: 6px;"><?php echo date("d-m-Y", strtotime($linha_adiant_receb['data_virt'])); ?></td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblAdiantRecebVazia" name="tblAdiantRecebVazia" class="tabela">
            <tr>
                <td>Não Tem, neste momento, nenhum adiantamento</td>
            </tr>
        </table>
    </div>
</div>
<!-- -->

<?php /* (MOVED TO conteudo_user)
<!-- Adiantamentos a Fornecedores -->
<div id="adiant_fornec">
    <div class="linha">
        <div class="left-column">
            <h3><?php echo $lingua['MAKE']; echo " "; echo lcfirst($lingua['ADVANCE']); ?></h3>
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
                <input id="txtNomeEmpresaF" name="txtNomeEmpresaF" type="text" value="<?php echo $linha_plafond_aumento['nome']; ?>" readonly="readonly">
            </div>
        </div>
    </div>
    <div class="linha10">
        <div class="esq20">
            <label for="txtNrContaF" class="labelNormal"><?php echo $lingua['ACCOUNT']; ?></label>
        </div>
        <div class="dir80">
            <div class="inputarea_col1">
                <input id="txtNrContaF" name="txtNrContaF" type="text" value="<?php echo '0' . $linha_plafond_aumento['num_conta']; ?>" readonly="readonly">
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
                        <?php while ($linha_fornecedores = $query_fornecedores->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $linha_fornecedores['id']; ?>"><?php echo $linha_fornecedores['nome_abrev']; ?></option>
                        <?php } ?>
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
                    <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_plafond_aumento['simbolo']; ?>">
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
            <h3><?php echo $lingua['ADVANCE']; echo " "; echo $lingua['MADE']; ?></h3>
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
                <td class="td25" style="padding: 6px;"><?php echo /*$lingua[''];* / "Nome do fornecedor"; ?></td>
                <td class="td25" style="padding: 6px;"><?php echo /*$lingua[''];* / "Valor por liquidar"; ?></td>
                <td class="td25" style="padding: 6px;"><?php echo /*$lingua[''];* / "Data de adiantamento"; ?></td>
            </tr>
            <?php while ($linha_adiant_efet = $query_carrega_adiant_efet->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td class="td25" style="padding: 6px;"><?php echo $linha_adiant_efet['nome_abrev']; ?></td>
                    <td class="td25" style="padding: 6px;"><?php echo number_format($linha_adiant_efet['valor'], 2, ',', '.').' '.$linha_moeda['simbolo']; ?></td>
                    <td class="td25" style="padding: 6px;"><?php echo date("d-m-Y", strtotime($linha_adiant_efet['data_virt'])); ?></td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblAdiantEfetVazia" name="tblAdiantEfetVazia" class="tabela">
            <tr>
                <td>Não Tem, neste momento, nenhum adiantamento</td>
            </tr>
        </table>
    </div>
</div>
*/ ?>

<div id="compras_agendadas">
    <div class="linha">
        <div class="left-column">
            <h3> <?php echo $lingua['SCH_B']; ?> </h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha">
        <table id="tblCompraDetail" name="tblCompraDetail" class="tabela">
            <tr>
                <td class="td25"><?php echo $lingua['COUNTRY']; ?></td>
                <td class="td20"><?php echo $lingua['STCK_MRKT']; ?></td>
                <td class="td15"><?php echo $lingua['S_NAME']; ?></td>
                <td class="td10"><?php echo $lingua['QTY']; ?></td>
                <td class="td10"><?php echo $lingua['TRGT_P']; ?></td>
                <td class="td15"><?php echo $lingua['L_DATE']; ?></td>
                <td class="td10" style="background-color: transparent;">&nbsp;</td>
            </tr>
            <?php foreach ($linha_trans_agend as $lta) { if ($lta['tipo'] == 'C') { ?>
                <tr>
                    <td style="padding: 6px;"><?php echo $lta['nome_pais']; ?></td>
                    <td style="padding: 6px;"><?php echo $lta['nome']; ?></td>
                    <td style="padding: 6px;"><?php echo $lta['nome_acao']; ?></td>
                    <td style="padding: 6px;"><?php echo number_format($lta['qtd'], 0, ',', '.'); ?></td>
                    <td style="padding: 6px;"><?php echo number_format($lta['preco_alvo'], 3, ',', '.').' '.$lta['simbolo']; ?></td>
                    <td style="padding: 6px;"><?php echo date("d-m-Y", strtotime($lta['data_limite_virtual'])); ?></td>
                    <td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">
                        <div class="labelicon icon-garbage rem_trans_agend">
                            <input id="hddIdCot" name="hddIdCot" type="hidden" value="<?php echo $lta['id_preco_alvo']; ?>">
                            <input id="hddTipoTrans" name="hddTipoTrans" type="hidden" value="<?php echo $lta['tipo']; ?>">
                        </div>
                    </td>
                </tr>
            <?php } 
            } ?>
        </table>
        <table id="tblCompraDetailVazia" name="tblCompraDetailVazia" class="tabela">
            <tr>
                <td>Não Tem, neste momento, nenhuma compra agendada</td>
            </tr>
        </table>
    </div>
</div>

<div id="vendas_agendadas">
    <div class="linha">
        <div class="left-column">
            <h3> <?php echo $lingua['SCH_S']; ?> </h3>
        </div>
        <!-- A ser eliminado após upgrade para plugin "notify.js" -->
        <div class="center-column">
            <div class="error"></div>
        </div>
        <!-- -->
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha">
        <table id="tblVendaDetail" name="tblVendaDetail" class="tabela">
            <tr>
                <td class="td25"><?php echo $lingua['COUNTRY']; ?></td>
                <td class="td20"><?php echo $lingua['STCK_MRKT']; ?></td>
                <td class="td15"><?php echo $lingua['S_NAME']; ?></td>
                <td class="td10"><?php echo $lingua['QTY']; ?></td>
                <td class="td10"><?php echo $lingua['TRGT_P']; ?></td>
                <td class="td15"><?php echo $lingua['L_DATE']; ?></td>
                <td class="td10" style="background-color: transparent;">&nbsp;</td>
            </tr>
            <?php foreach ($linha_trans_agend as $lta) { if ($lta['tipo'] == 'V') { ?>
                <tr>
                    <td style="padding: 6px;"><?php echo $lta['nome_pais']; ?></td>
                    <td style="padding: 6px;"><?php echo $lta['nome']; ?></td>
                    <td style="padding: 6px;"><?php echo $lta['nome_acao']; ?></td>
                    <td style="padding: 6px;"><?php echo number_format($lta['qtd'], 0, ',', '.'); ?></td>
                    <td style="padding: 6px;"><?php echo number_format($lta['preco_alvo'], 3, ',', '.').' '.$lta['simbolo']; ?></td>
                    <td style="padding: 6px;"><?php echo date("d-m-Y", strtotime($lta['data_limite_virtual'])); ?></td>
                    <td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">
                        <div class="labelicon icon-garbage rem_trans_agend">
                            <input id="hddIdCot" name="hddIdCot" type="hidden" value="<?php echo $lta['id_preco_alvo']; ?>">
                            <input id="hddTipoTrans" name="hddTipoTrans" type="hidden" value="<?php echo $lta['tipo']; ?>">
                        </div>
                    </td>
                </tr>
            <?php } 
            } ?>
        </table>
        <table id="tblVendaDetailVazia" name="tblVendaDetailVazia" class="tabela">
            <tr>
                <td>Não Tem, neste momento, nenhuma venda agendada</td>
            </tr>
        </table>
    </div>
</div>

<div id="media">
    <div class="linha">
        <h3> Bolsa IPB 2015 </h3>
    </div>
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    <div id="blueimp-gallery" class="blueimp-gallery">
        <div class="slides"></div>
        <h3 class="title"></h3>
        <a class="prev">‹</a>
        <a class="next">›</a>
        <a class="blueimp-gallery-display"></a>
        <a class="close">×</a>
        <ol class="indicator"></ol>
    </div>
    <div id="links">
        <div style="float: left"><a href="media/2014-2015/IMG_1485.JPG" title="Entrega de prémios - 2015">
            <img src="media/2014-2015/IMG_1485_thumb.jpg" alt="Pr1">
        </a></div>
        <div style="float: left; padding-left: 10px;"><a href="media/2014-2015/IMG_1492.JPG" title="Entrega de prémios - 2015">
            <img src="media/2014-2015/IMG_1492_thumb.jpg" alt="Pr2">
        </a></div>
        <div style="float: left; padding-left: 10px;"><a href="media/2014-2015/Notícia MDB_1.jpg" title="Notícia - 2015">
            <img src="media/2014-2015/Notícia MDB_1_thumb.jpg" alt="News">
        </a></div>
        
        <!-- -->
        <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
        <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
        <!-- -->
        
        <div class="linha">
            <h3> Bolsa IPB 2016 </h3>
        </div>
        <div style="float: left"><a href="media/2015-2016/IMG_5556.JPG" title="Entrega de prémios - 2016">
            <img src="media/2015-2016/IMG_5556_thumb.jpg" alt="Pr1">
        </a></div>
        <div style="float: left; padding-left: 10px;"><a href="media/2015-2016/IMG_5572.JPG" title="Entrega de prémios - 2016">
            <img src="media/2015-2016/IMG_5572_thumb.jpg" alt="Pr2">
        </a></div>
        <div style="float: left; padding-left: 10px;"><a href="media/2015-2016/IMG_5582.JPG" title="Entrega de prémios - 2016">
                <img src="media/2015-2016/IMG_5582_thumb.jpg" alt="News">
        </a></div>
        <div style="float: left; padding-left: 10px;"><a href="media/2015-2016/IMG_5589.JPG" title="Entrega de prémios - 2016">
                <img src="media/2015-2016/IMG_5589_thumb.jpg" alt="News">
        </a></div>
    </div>
</div>
<!-- -->