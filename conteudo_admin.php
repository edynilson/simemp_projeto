<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-29 15:25:08
*/

include('./conf/check_admin.php');
include_once('./functions/functions.php');
include_once('./conf/common.php');

$query_admin = $connection->prepare("SELECT u.login, u.nome, date_format(u.date,'%d-%m-%Y') AS `data` FROM utilizador u WHERE parent=:id");
$query_admin->execute(array(':id' => $_SESSION["id_utilizador"]));

// $query_calendario = $connection->prepare("SELECT cal.id_cal, g.id, g.nome AS grupo, cal.mes, cal.ano FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.tipo=:tipo AND u.id=:id_utilizador GROUP BY cal.id_cal");
//-- LAST // $query_calendario = $connection->prepare("SELECT * FROM (SELECT * FROM (SELECT g.id, g.nome, tg.id AS id_tipo, tg.designacao AS tipo FROM utilizador u INNER JOIN user_grupo ug ON u.id=ug.id_user INNER JOIN grupo g ON ug.id_grupo=g.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE u.tipo=:tipo AND u.id=:id_utilizador) AS grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado ON grupos.id=estado.id_grupo GROUP BY estado.id_grupo ORDER BY grupos.nome ASC) AS grupos_active INNER JOIN calendario cal ON grupos_active.id=cal.id_grupo WHERE grupos_active.estado='1' GROUP BY cal.id_cal");
// $query_calendario->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
//$query_calendario = $connection->prepare("SELECT grupos_active.nome, c.* FROM (SELECT g.nome, last_estado_grupos.* FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo) AS last_estado_grupos INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id WHERE last_estado_grupos.estado='1') AS grupos_active INNER JOIN user_grupo ug ON grupos_active.id_grupo=ug.id_grupo INNER JOIN calendario c ON grupos_active.id_grupo=c.id_grupo WHERE ug.id_user=:id_utilizador ORDER BY grupos_active.nome ASC, c.ano ASC, c.mes ASC");//estava esta
$query_calendario = $connection->prepare("SELECT g.nome,c.id_cal,g.id as id_grupo, c.mes ,c.ano, c.data_inicio , c.hora_inicio, c.data_fim ,c.hora_fim ,c.cor,c.editavel,c.date_reg FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id  INNER JOIN user_grupo ug ON last_estado_grupos.id_grupo=ug.id_grupo  INNER JOIN calendario c ON last_estado_grupos.id_grupo=c.id_grupo WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC, c.ano ASC, c.mes ASC");//meti esta
$query_calendario->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));

$query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
$query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
$query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_empresa2 = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
$query_empresa2->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_empresa3 = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
$query_empresa3->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa, g.id AS id_grupo, g.nome AS grupo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");
$query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_empresas2 = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa, m1.saldo_controlo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN conta c ON c.id_empresa=emp.id_empresa INNER JOIN movimento m1 ON m1.id_conta=c.id LEFT JOIN movimento m2 ON (m1.id_conta = m2.id_conta AND m1.id < m2.id) WHERE m2.id IS NULL AND emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND c.tipo_conta='ordem' ORDER BY emp.nome ASC");
$query_empresas2->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));


/* TODOS EMPRESAS // $query_empresas3 = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");
$query_empresas3->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'])); */
/* APENAS EMPRESAS DOS GRUPOS ATIVOS */ 
// $query_empresas3 = $connection->prepare("SELECT estado_grupos.id_grupo, estado_grupos.nome AS nome_grupo, estado_grupos.estado, e.id_empresa, e.nome AS empresa FROM (SELECT * FROM (SELECT eg.id_grupo, g.nome, eg.estado FROM utilizador u LEFT JOIN user_grupo ug ON u.id=ug.id_user INNER JOIN grupo g ON ug.id_grupo=g.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id LEFT JOIN estado_grupo eg ON g.id=eg.id_grupo WHERE u.tipo='admin' AND u.id=:id_utilizador AND tg.designacao='Normal' ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_grupos GROUP BY estado_grupos.id_grupo) AS estado_grupos LEFT JOIN empresa e ON estado_grupos.id_grupo=e.id_grupo WHERE estado_grupos.estado='1' ORDER BY e.nome ASC");
//$query_empresas3 = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, last_estado_grupos.estado, e.id_empresa, e.nome AS empresa FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo) AS last_estado_grupos INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo LEFT JOIN empresa e ON g.id=e.id_grupo WHERE last_estado_grupos.estado='1' AND e.ativo='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC, e.nome ASC"); //estava esta
$query_empresas3 = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, last_estado_grupos.estado, e.id_empresa, e.nome AS empresa FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id LEFT JOIN empresa e ON g.id=e.id_grupo WHERE last_estado_grupos.estado='1' AND e.ativo='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC, e.nome ASC");//meti esta
$query_empresas3->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));



if($_SESSION['admin'] == "0") {
    $query_empresas4 = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
    $query_empresas4->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
} else {
    $query_empresas4 = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador");
    $query_empresas4->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
}

if($_SESSION['admin'] == "0") {
    $query_empresas5 = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
    $query_empresas5->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
} else {
    $query_empresas5 = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador");
    $query_empresas5->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
}

$query_empresas6 = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa, emp.nipc, emp.morada, g.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
$query_empresas6->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_emprestimos = $connection->prepare("SELECT DISTINCT e.emprest, date_format(e.data_emprestimo, '%d-%m-%Y') AS data, IF(strcmp(e.pago, '1'), 'Não', 'Sim') AS pago, emp.id_empresa, emp.nome FROM emprestimo e INNER JOIN conta c ON e.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND e.pago='0'");
$query_emprestimos->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_entregas = $connection->prepare("SELECT e.id, date_format(e.`data`, '%d-%m-%Y') AS `data`, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(e.`data`) ASC, time(e.`data`) ASC");
$query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

if($_SESSION['admin'] == "0") {
    $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
    $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
} else{
    $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador");
    $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
}

if($_SESSION['admin'] == "0") {
    $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
    $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
} else {
    $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN fatura f ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador");
    $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
}

$query_filtro = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN dec_retencao dr ON dr.id_empresa=emp.id_empresa LEFT JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
$query_filtro->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_fornecedor = $connection->prepare("SELECT id, nome_abrev FROM fornecedor ORDER BY nome_abrev");
$query_fornecedor->execute();

$query_fornecedor_produtos = $connection->prepare("SELECT id, nome_abrev FROM fornecedor ORDER BY nome_abrev");
$query_fornecedor_produtos->execute();

$query_pais_fornecedores = $connection->prepare("SELECT p.id_pais, p.nome_pais FROM pais p ORDER by p.nome_pais");
$query_pais_fornecedores->execute();
$linha_pais_fornecedores = $query_pais_fornecedores->fetchAll();

$query_fornecedor_pt = $connection->prepare("SELECT f.id, f.nome_abrev FROM fornecedor f INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais WHERE p.nome_pais='Portugal' ORDER BY f.nome_abrev ASC");
$query_fornecedor_pt->execute();
$fornecedores_pt = $query_fornecedor_pt->fetchAll();

$query_locacao = $connection->prepare("SELECT DISTINCT l.leas, date_format(l.data_leasing, '%d-%m-%Y') AS data, IF(strcmp(l.pago, '1'), 'Não', 'Sim') AS pago, emp.id_empresa, emp.nome FROM leasing l INNER JOIN conta c ON l.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND l.pago='0'");
$query_locacao->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

/* $query_lucro_real = $connection->prepare("SELECT emp.nome, compras.qtd_compras, compras.compras, vendas.qtd_vendas, IF(vendas.vendas IS NOT NULL, vendas.vendas, 0) AS vendas, IF(vendas-compras_vendidas IS NOT NULL, vendas-compras_vendidas, 0) AS lucro_real, compras.qtd_compras-vendas.qtd_vendas AS qtd_sobrante FROM (SELECT emp.id_empresa, SUM(IF (p.quantidade IS NOT NULL, p.quantidade, f.quantidade)) AS qtd_compras, SUM(p.preco*IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS compras_vendidas, SUM(p.preco*IF (p.quantidade IS NOT NULL, p.quantidade, f.quantidade)) AS compras FROM acao_trans p LEFT JOIN acao_trans f ON p.id = f.parent INNER JOIN acao a ON p.id_acao=a.id OR f.id_acao=a.id INNER JOIN empresa emp ON emp.id_empresa=p.id_empresa OR emp.id_empresa=f.id_empresa WHERE emp.ativo='1' AND p.parent IS NULL GROUP BY emp.nome ORDER BY emp.nome) AS compras LEFT JOIN (SELECT emp.id_empresa, SUM(IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS qtd_vendas, SUM(f.preco*IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS vendas FROM acao_trans p LEFT JOIN acao_trans f ON p.id = f.parent INNER JOIN acao a ON p.id_acao=a.id OR f.id_acao=a.id INNER JOIN empresa emp ON emp.id_empresa=p.id_empresa OR emp.id_empresa=f.id_empresa WHERE emp.ativo='1' AND p.parent IS NULL GROUP BY emp.nome) AS vendas ON compras.id_empresa=vendas.id_empresa INNER JOIN empresa emp ON emp.id_empresa=compras.id_empresa OR emp.id_empresa=vendas.id_empresa INNER JOIN grupo g ON g.id=emp.id_grupo INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.id=:id_utilizador");
$query_lucro_real->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$num_linhas = $query_lucro_real->rowCount(); */

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_produtos = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM produto p INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL ORDER BY f.nome_abrev, p.nome LIMIT 5");
$query_produtos->execute();
$num_rows = $query_produtos->rowCount();

$query_regra = $connection->prepare("SELECT r.id_regra, r.nome_regra FROM regra r ORDER BY r.nome_regra");
$query_regra->execute();

$query_t_entrega = $connection->prepare("SELECT t.id, t.designacao FROM tipo_entrega t");
$query_t_entrega->execute();

/*
$query_taxas = $connection->prepare("SELECT emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE re2.id_regra IS NULL AND re2.id_empresa IS NULL AND emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
$query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
*/

$query_tipo_entrega = $connection->prepare("SELECT DISTINCT te.id, te.designacao FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
$query_tipo_entrega->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$query_titulos = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome AS empresa FROM empresa emp INNER JOIN conta c ON c.id_empresa=emp.id_empresa INNER JOIN acao_trans a_t ON a_t.id_empresa=emp.id_empresa INNER JOIN acao a ON a.id = a_t.id_acao INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
$query_titulos->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

$datetime = new DateTime();

$id_linha = 0;

//--
/* TODOS GRUPOS // $query_grupos = $connection->prepare("SELECT g.id, g.nome FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao='Normal'");
$query_grupos->execute(); */

/* APENAS GRUPOS ATIVOS */
// $query_grupos = $connection->prepare("SELECT * FROM (SELECT * FROM (SELECT eg.id_grupo, g.nome, eg.estado FROM utilizador u LEFT JOIN user_grupo ug ON u.id=ug.id_user INNER JOIN grupo g ON ug.id_grupo=g.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id LEFT JOIN estado_grupo eg ON g.id=eg.id_grupo WHERE u.tipo='admin' AND u.id=:id_utilizador AND tg.designacao='Normal' ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_grupos GROUP BY estado_grupos.id_grupo) AS estado_grupos WHERE estado_grupos.estado='1'");
//$query_grupos = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, last_estado_grupos.estado FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo) AS last_estado_grupos INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC"); //estava esta
$query_grupos = $connection->prepare("SELECT last_estado_grupos.id_grupo,g.nome, last_estado_grupos.estado FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC");//meti esta
$query_grupos->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_grupo_regra = $query_grupos->fetchAll();

//-- Carregar todos os grupos, msmo os INATIVOS.
$grupo1 = $connection->prepare("SELECT g.id, g.nome, tg.id AS id_tipo, tg.designacao AS tipo FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE u.tipo=:tipo AND u.id=:id_utilizador ORDER BY g.nome");
$grupo1->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
//--

//-- Todos os produtos (Afetar a fornecedor)
$query_produtos_tot = $connection->prepare("SELECT p.id, p.nome FROM produto p ORDER BY p.nome ASC");
$query_produtos_tot->execute();
//--

//-- Listagem descontos
//$query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.id_desconto, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', descontos.prazo_pag) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia, descontos.active FROM (SELECT u.id_entidade, fpd.id_desconto, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag, fpd.active FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC, info_desc.produto ASC");// estava esta (problemas ao passar do mariaDB para o mysql)
$query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.id_desconto, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', descontos.prazo_pag) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia, descontos.active FROM (SELECT u.id_entidade, fpd.id_desconto, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag, fpd.active FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC/*, info_desc.produto ASC*/"); //meti esta (comentei o info_desc.produto ASC e funciona bem...)
$query_fp_desc->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));

$query_fornec_desc = $connection->prepare("SELECT DISTINCT f.id, CONCAT(f.nome_abrev, ', ', p.nome_abrev) AS fornecedor FROM fp_desconto fp INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais");
$query_fornec_desc->execute();

//-- Calendario de tarefas de todos GRUPOS ATIVOS, da MESMA ENTIDADE
//$query_calend_tasks = $connection->prepare("SELECT g.nome, ct.* FROM (SELECT * FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo ASC) AS last_estado_grupo WHERE last_estado_grupo.estado='1') AS grupos_active INNER JOIN grupo g ON grupos_active.id_grupo=g.id INNER JOIN calendario_tasks ct ON grupos_active.id_grupo=ct.id_grupo INNER JOIN user_grupo ug ON grupos_active.id_grupo=ug.id_grupo INNER JOIN utilizador admin ON ug.id_user=admin.id INNER JOIN entidade e ON admin.id_entidade=e.id INNER JOIN utilizador me ON e.id=me.id_entidade WHERE me.id=:id_utilizador ORDER BY mes_v_ini ASC, dia_v_ini ASC");//estava esta
$query_calend_tasks = $connection->prepare("SELECT g.nome,c.id,g.id as id_grupo, c.descricao,c.dia_v_ini,c.mes_v_ini,c.dia_v_fim,c.mes_v_fim,c.date_reg  FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON last_estado_grupos.id_grupo=ug.id_grupo INNER JOIN utilizador admin ON ug.id_user=admin.id INNER JOIN calendario_tasks c ON last_estado_grupos.id_grupo=c.id_grupo INNER JOIN entidade e ON admin.id_entidade=e.id WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY mes_v_ini ASC, dia_v_ini ASC");//meti esta
$query_calend_tasks->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));

//-- Listagem alertas
$query_alertas = $connection->prepare("SELECT a.id,a.id_utilizador,a.id_acao_trans,u.login,u.nome,a.nome AS simbolo,a.preco_compra,a.quantidade,a.preco_atual,a.date_reg FROM alerta a INNER JOIN utilizador u ON a.id_utilizador=u.id ORDER BY date_reg desc;");
$query_alertas->execute();

?>
<div id="grupo_novo_pag">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['CREATE'].' '.lcfirst($lingua['NEW']).' '.lcfirst($lingua['GRP']); ?></h3>
        <div class="error"></div>
    </div>
    <div id="frmNovoGrupo" name="frmNovoGrupo" class="width60">
        <div class="linha10 left">
            <div class="width40 left">
                <label for="txtNomeGrupo" class="labelNormal left"><?php echo $lingua['NAME']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width190">
                    <input id="txtNomeGrupo" name="txtNomeGrupo" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width40 left">
                <label for="txtTipoGrupo" class="labelNormal left"><?php echo $lingua['TYPE']; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcTipoGrupo" name="slcTipoGrupo" class="chosenSelect" data-placeholder="<?php echo $lingua['TYPE']; ?>">
                    <option selected="selected" value="0"></option>
                    <?php
                    $tipo_grupo = $connection->prepare("SELECT tg.id, tg.designacao FROM tipo_grupo tg");
                    $tipo_grupo->execute();
                    while ($row = $tipo_grupo->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['designacao']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnInserirGrupo" name="btnInserirGrupo" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
</div>
<div id="grupo_afet_pag">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['AFFT'].' '.lcfirst($lingua['CMPNY']).' '.$lingua['TO'].' '.lcfirst($lingua['GRP']).'s'; ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblGrupoAfetacao" name="tblGrupoAfetacao" class="tabela left width60">
        <tr>
            <td class="width30"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width30"><?php echo $lingua['GRP']; ?></td>
            <td class="width40"><?php echo $lingua['OPTS']; ?></td>
        </tr>
        <?php while ($linha = $query_empresas->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td style="padding: 5px;"><?php echo $linha['empresa']; ?><input id="hddIdEmpAfet" name="hddIdEmpAfet" type="hidden" value="<?php echo $linha['id_empresa']; ?>"></td>
                <td style="padding: 5px;"><?php echo $linha['grupo']; ?></td>
                <td style="padding: 5px;">
                    <select id="slcGrupoAfet_<?php echo $id_linha++; ?>" name="slcGrupoAfet" class="chosenTabelaSelect" data-placeholder="<?php echo $lingua['GRP']; ?>">
                        <?php
                        $grupo2 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
                        while ($row = $grupo2->fetch(PDO::FETCH_ASSOC)) {
                            if ($row['id_grupo'] == $linha["id_grupo"]) {
                                ?>
                                <option selected="selected" value="<?php echo $row['id_grupo']; ?>"><?php echo $row['nome']; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $row['id_grupo']; ?>"><?php echo $row['nome']; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblEmpresaVazia" name="tblEmpresaVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="width60 left textoCentro">
        <button id="btnGuardarAfetGrupos" name="btnGuardarAfetGrupos" class="botao"><?php echo $lingua['SAVE']; ?></button>
    </div>
</div>
<div id="grupo_edit_pag">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['GRP']).'s'; ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha10 left">
        <table id="tblGrupo" name="tblGrupo" class="tabela left width60">
            <tr>
                <td class="width40"><?php echo $lingua['NAME']; ?></td>
                <td class="width30"><?php echo $lingua['STAT']; ?></td>
                <td class="width30"><?php echo $lingua['TYPE']; ?></td>
            </tr>
            <?php
            // $grupo1 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
            while ($linha_dados = $grupo1->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <tr>
                    <td>
                        <div class="inputareaTable width130">
                            <input id="txtNomeGrupo_<?php echo $id_linha++; ?>" name="txtNomeGrupo" type="text" class="editableText" readonly="readonly" value="<?php echo $linha_dados['nome']; ?>">
                            <input id="hddIdGrupo" name="hddIdGrupo" type="hidden" value="<?php echo $linha_dados['id']; ?>">
                        </div>
                    </td>
                    <td>
                        <select id="slcEstadoGrupo_<?php echo $id_linha++; ?>" name="slcEstadoGrupo" class="chosenTabelaSelect">
                            <?php
//                            $query_estado_grupo = $connection->prepare("SELECT * FROM (SELECT g.id AS id_grupo, eg.estado FROM grupo g INNER JOIN estado_grupo eg ON g.id=eg.id_grupo INNER JOIN utilizador u ON eg.id_user=u.id WHERE u.id=:id_utilizador AND g.id=:id_grupo ORDER BY date(eg.`data`) DESC, time(eg.`data`) DESC) AS t1 GROUP BY id_grupo LIMIT 1"); //Comentei esta
                            $query_estado_grupo = $connection->prepare("SELECT * FROM (SELECT g.id AS id_grupo, eg.estado,eg.data FROM grupo g INNER JOIN estado_grupo eg ON g.id=eg.id_grupo INNER JOIN utilizador u ON eg.id_user=u.id WHERE u.id=:id_utilizador AND g.id=:id_grupo ORDER BY eg.data ASC) AS t1 ORDER BY t1.data DESC LIMIT 1"); //Meti esta
                            $query_estado_grupo->execute(array(':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $linha_dados['id']));
                            $row = $query_estado_grupo->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <option <?php if ($row['estado'] == "0") echo "selected=\"selected\""; ?> value="0"><?php echo $lingua['DSBL']; ?></option>
                            <option <?php if ($row['estado'] == "1") echo "selected=\"selected\""; ?> value="1"><?php echo $lingua['ENBL']; ?></option>
                        </select>
                    </td>
                    <td><?php echo $linha_dados['tipo']; ?><input id="hddTipoGrupo" name="hddTipoGrupo" type="hidden" readonly="readonly" value="<?php echo $linha_dados['id_tipo']; ?>"></td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblGrupoVazia" name="tblGrupoVazia" class="tabela left">
            <tr>
                <td><?php echo $lingua['TBL_VAZIA']; ?></td>
            </tr>
        </table>
        <div class="width60 left textoCentro">
            <button id="btnGuardarGrupo" name="btnGuardarGrupo" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
</div>
<div id="del_empresas">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['DEL'].' '.lcfirst($lingua['CMPNY']); ?></h3>
        <div class="error"><span id="error"><?php echo $lingua['ERROR']; ?></span></div>
    </div>
    <div class="linha left">
        <select id="slcFiltrarGrupo" name="slcFiltrarGrupo" class="chosenSelect" data-placeholder="<?php echo $lingua['GRP']; ?>">
            <option selected="selected" value="0"></option>
            <?php
            $grupo3 = $connection->prepare("SELECT DISTINCT g.id, g.nome FROM grupo g INNER JOIN empresa emp ON g.id=emp.id_grupo INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON u.id=ug.id_user WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY g.nome");
            $grupo3->execute(array('tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
            while ($linha_grupo = $grupo3->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <option value="<?php echo $linha_grupo['id']; ?>"><?php echo $linha_grupo['nome']; ?></option>
            <?php } ?>
        </select>
        <button id="btnRemEmpresa" name="btnRemEmpresa" class="botao"><?php echo $lingua['DEL']; ?></button>
    </div>
    <table id="tblEmpresasGeral" name="tblEmpresasGeral" class="tabela left" data-value="1">
        <tr>
            <td class="width5">
                <div class="checkbox">
                    <input id="chkAllEmpresas" name="chkAllEmpresas" type="checkbox" class="chk" value="0">
                    <label for="chkAllEmpresas" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="width30"><?php echo $lingua['NAME']; ?></td>
            <td class="width10">NIPC</td>
            <td class="width40"><?php echo $lingua['ADDR']; ?></td>
            <td class="width15"><?php echo $lingua['GRP']; ?></td>
        </tr>
        <?php while ($linha_empresas = $query_empresas6->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td class="transparent" style="cursor: pointer;">
                    <div class="checkbox">
                        <input id="chkEmpresa_<?php echo $linha_empresas['id_empresa']; ?>" name="chkEmpresa" type="checkbox" class="chk" value="<?php echo $linha_empresas['id_empresa']; ?>">
                        <label for="chkEmpresa" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                        <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_empresas['id_empresa']; ?>">
                    </div>
                </td>
                <td><?php echo $linha_empresas['empresa']; ?></td>
                <td><?php echo $linha_empresas['nipc']; ?></td>
                <td style="text-align: left;"><?php echo $linha_empresas['morada']; ?></td>
                <td><?php echo $linha_empresas['nome']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblEmpresasVazia" name="tblEmpresasVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
</div>
<div id="v_empresas">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['CMPNY']); ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblEmpresasGrupo" name="tblEmpresasGrupo" class="tabela left width45" style="margin-right: 5%">
        <tr>
            <td class="width50"><?php echo $lingua['NAME']; ?></td>
            <td class="width40"><?php echo $lingua['GRP']; ?></td>
            <td class="width10 transparent"></td>
        </tr>
        <?php
        $query_empresa_grupo = $connection->prepare("SELECT emp.id_empresa AS id_empresa, emp.nome AS empresa, g.nome AS grupo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.id=:id_utilizador ORDER BY emp.nome");
        $query_empresa_grupo->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
        while($linha_empresa_grupo = $query_empresa_grupo->fetch(PDO::FETCH_ASSOC)) { ?>
        <tr>
            <td><?php echo $linha_empresa_grupo['empresa']; ?></td>
            <td><?php echo $linha_empresa_grupo['grupo']; ?></td>
            <td class="iconwrapper"><div id="divImgVerEmp" name="divImgVerEmp" class="novolabelicon icon-info"></div><input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_empresa_grupo['id_empresa']; ?>"></td>
        </tr>
        <?php } ?>
    </table>
    <table id="tblEditEmpresaVazia" name="tblEditEmpresaVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div id="frmDadosEmpresa" class="width50 left">
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtNissDEmp" class="labelNormal left">NISS</label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="hddIdEmpresaDEmp" name="hddIdEmpresaDEmp" type="hidden" readonly="readonly">
                    <input id="txtNissDEmp" name="txtNissDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtNipcDEmp" class="labelNormal left">NIPC</label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtNipcDEmp" name="txtNipcDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtNomeDEmp" class="labelNormal left"><?php echo $lingua['NAME']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtNomeDEmp" name="txtNomeDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtTipoDEmp" class="labelNormal left"><?php echo $lingua['TYPE']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtTipoDEmp" name="txtTipoDEmp" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtDesignacaoDEmp" class="labelNormal left"><?php echo $lingua['ACT']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtDesignacaoDEmp" name="txtDesignacaoDEmp" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtMoradaDEmp" class="labelNormal left"><?php echo $lingua['ADDR']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width100">
                    <input id="txtMoradaDEmp" name="txtMoradaDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtCodPostalDEmp" class="labelNormal left"><?php echo $lingua['Z_COD']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtCodPostalDEmp" name="txtCodPostalDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtLocalidadeDEmp" class="labelNormal left"><?php echo $lingua['LOC']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtLocalidadeDEmp" name="txtLocalidadeDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtPaisDEmp" class="labelNormal left"><?php echo $lingua['COUNTRY']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtPaisDEmp" name="txtPaisDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtEmailDEmp" class="labelNormal left">E-mail</label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtEmailDEmp" name="txtEmailDEmp" type="text" class="editableText" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtGrupoDEmp" class="labelNormal left"><?php echo $lingua['GRP']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width190">
                    <input id="txtGrupoDEmp" name="txtGrupoDEmp" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label for="txtUtilizadorDEmp" class="labelNormal left"><?php echo $lingua['USR']; ?></label>
            </div>
            <div class="width80 left"></div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnGuardarDadosEmp" name="btnGuardarDadosEmp" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
</div>
<div id="v_users">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['SEE'].'/'.$lingua['EDT'].' '.lcfirst($lingua['USR']); ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblUsersGeral" name="tblUsersGeral" class="tabela left width50">
        <tr>
            <td class="width40">Login</td>
            <td <?php if($_SESSION['admin'] == "0") { echo "class='td55'";} else { echo "class='td60'";}?>><?php echo $lingua['CMPNY']; ?></td>
            <?php if($_SESSION['admin'] == "0") { ?><td class="width5 transparent"></td><?php } ?>
        </tr>
        <?php
        if($_SESSION['admin'] == "0") {
            $utilizadores2 = carregar_users($connection, "admin", $_SESSION['id_utilizador']);
        } else {
            $utilizadores2 = $connection->prepare("SELECT users.id, users.nome_user AS nome_user, users.id_empresa, users.nome, users.u_ldap FROM (SELECT DISTINCT u1.id, u1.tipo, g.id AS id_grupo FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON u2.id=ug.id_user INNER JOIN grupo g ON ug.id_grupo=g.id) AS adm INNER JOIN (SELECT u.id, u.login AS nome_user, u.u_ldap, emp.id_empresa, emp.nome, g.id AS id_grupo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id WHERE emp.ativo='1') AS users ON adm.id_grupo=users.id_grupo WHERE adm.tipo=:tipo AND adm.id=:id_utilizador ORDER BY users.nome_user");
            $utilizadores2->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        while ($linha_users = $utilizadores2->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <tr>
            <td><?php echo $linha_users['nome_user']; ?></td>
            <td><?php echo $linha_users['nome']; ?></td>
            <?php if($_SESSION['admin'] == "0" && $linha_users['u_ldap'] == "0") { ?><td class="iconwrapper"><div id="divImgVerUser" name="divImgVerUser" class="novolabelicon icon-info"></div><input id="hddIdUser" name="hddIdUser" type="hidden" readonly="readonly" value="<?php echo $linha_users['id']; ?>"></td><?php } ?>
        </tr>
        <?php } ?>
    </table>
    <div id="frmDadosUsers" class="width45 left" style="margin-left: 5%;">
        <div class="linha10 left">
            <div class="width50 left">
                <label for="txtLoginUser" class="labelNormal left">Login</label>
            </div>
            <div class="width50 left">
                <div class="inputarea left">
                    <input id="txtLoginUser" name="txtLoginUser" type="text" class="editableText" readonly="readonly">
                    <input id="hddIdUserFrm" name="hddIdUserFrm" type="hidden" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width50 left">
                <label for="txtNomeEmpresa" class="labelNormal left"><?php echo $lingua['CMPNY']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea left">
                    <input id="txtNomeEmpresa" name="txtNomeEmpresa" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width50 left">
                <label for="txtPassword" class="labelNormal left"><?php echo $lingua['NEW_PASS']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea left">
                    <input id="txtPassword" name="txtPassword" type="password" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width50 left">
                <label for="txtConfPassword" class="labelNormal left"><?php echo $lingua['CONF'].' '.lcfirst($lingua['NEW_PASS']); ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea left">
                    <input id="txtConfPassword" name="txtConfPassword" type="password" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnGuardarDadosUser" name="btnGuardarDadosUser" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
    <table id="tblUsersGeralVazia" name="tblUsersGeralVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
</div>
<div id="user_afet_pag">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['AFFT'].' '.lcfirst($lingua['USR']); ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblUserAfet" name="tblUserAfet" class="tabela left width60">
        <tr>
            <td class="width50"><?php echo $lingua['USERNAME']; ?></td>
            <td class="width50"><?php echo $lingua['CMPNY']; ?></td>
        </tr>
        <?php
        $utilizadores3 = carregar_users($connection, "admin", $_SESSION['id_utilizador']);
        while ($linha_afet_user = $utilizadores3->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <tr>
                <td style="padding: 0 0 0 10px; text-align: left;">
                    <input type="hidden" value="<?php echo $linha_afet_user['id']; ?>"><?php echo $linha_afet_user['nome_user']; ?>
                </td>
                <td style="padding: 5px;">
                    <select id="slcEmpresaAfet_<?php echo $id_linha++; ?>" name="slcEmpresaAfet" class="chosenTabelaSelect">
                        <?php
                        $empresa1 = carregar_empresa($connection, "admin", $_SESSION['id_utilizador']);
                        while ($row = $empresa1->fetch(PDO::FETCH_ASSOC)) {
                            if ($row['id_empresa'] == $linha_afet_user["id_empresa"]) {
                                ?>
                                <option selected="selected" value="<?php echo $row['id_empresa']; ?>"><?php echo $row['nome']; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $row['id_empresa']; ?>"><?php echo $row['nome']; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblUserAfetVazia" name="tblUserAfetVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="width60 left textoCentro">
        <button id="btnGuardarAfet" name="btnGuardarAfet" class="botao"><?php echo $lingua['SAVE']; ?></button>
    </div>
</div>
<div id="v_entregas">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['V_DELIVERY']; ?></h3>
        <div class="error"></div>
    </div>
    <div id="radVOthersGroup" class="linha left textoCentro" style="height: 30px">
        <div class="radio">
            <input id="radVOutros" name="outros" type="radio" value="1" checked="checked">
            <label for="radVOutros" class="btnRadio"><?php echo $lingua['OTH']; ?></label>
            <input id="radVDecRet" name="outros" type="radio" value="2">
            <label for="radVDecRet" class="btnRadio"><?php echo $lingua['DEC_R']; ?></label>
        </div>
    </div>
    <div class="linha left">
        <select id="slcOrdenarEntregas" name="slcOrdenarEntregas" class="chosenSelect" data-placeholder="<?php echo $lingua['ORD_B']; ?>">
            <option selected="selected" value="0"></option>
            <option value="1"><?php echo $lingua['DATE']; ?></option>
            <option value="2"><?php echo $lingua['TYPE']; ?></option>
            <option value="3"><?php echo $lingua['CMPNY']; ?></option>
        </select>
        <span id="hideSlcFiltrarEntregas">
            <select id="slcFiltrarEntregas" name="slcFiltrarEntregas" class="chosenSelect" data-placeholder="<?php echo $lingua['FIL_B'].' '.lcfirst($lingua['CMPNY']); ?>">
                <option selected="selected" value="0"></option>
                <?php
                while ($row = $query_empresa2->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <option value="<?php echo $row['id_empresa']; ?>"><?php echo $row['nome']; ?></option>
                <?php } ?>
            </select>
        </span>
        <span id="hideSlcFiltrarEntregasTipo">
            <select id="slcFiltrarEntregasTipo" name="slcFiltrarEntregasTipo" class="chosenSelect" data-placeholder="<?php echo $lingua['FIL_B'].' '.lcfirst($lingua['TYPE']); ?>">
                <option selected="selected" value="0"></option>
                <?php
                while ($row = $query_tipo_entrega->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['designacao']; ?></option>
                <?php } ?>
            </select>
        </span>
        <span class="width_tmp"></span>
        <button id="btnDeleteDeclaracao" name="btnDeleteDeclaracao" class="botao"><?php echo $lingua['DEL']; ?></button>
    </div>
    <div class="linha left">
        <select id="slcOrdenarDecRet" name="slcOrdenarDecRet" class="chosenSelect" data-placeholder="<?php echo $lingua['ORD_B']; ?>">
            <option selected="selected" value="0"></option>
            <option value="1"><?php echo $lingua['DATE']; ?></option>
            <option value="2"><?php echo $lingua['CMPNY']; ?></option>
        </select>
        <span id="hideSlcFiltrarDecRet">
            <select id="slcFiltrarDecRet" name="slcFiltrarDecRet" class="chosenSelect" data-placeholder="<?php echo $lingua['FIL_B'].' '.$lingua['CMPNY']; ?>">
                <option selected="selected" value="0"></option>
                <?php
                while ($row = $query_filtro->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <option value="<?php echo $row['id_empresa']; ?>"><?php echo $row['nome']; ?></option>
                <?php } ?>
            </select>
        </span>
        <button id="btnDeleteDecRet" name="btnDeleteDecRet" class="botao"><?php echo $lingua['DEL']; ?></button>
    </div>
    <table id="tblEntregasGeral" name="tblEntregasGeral" class="tabela left">
        <tr>
            <td class="width5">
                <div class="checkbox">
                    <input id="chkAllEntregas" name="chkAllEntregas" type="checkbox" class="chk" value="0">
                    <label for="chkAllEntregas" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="width10"><?php echo $lingua['DATE']; ?></td>
            <td class="width25"><?php echo $lingua['TYPE']; ?></td>
            <td class="width10"><?php echo $lingua['PAID']; ?></td>
            <td class="width15"><?php echo $lingua['TOTAL_VAL']; ?></td>
            <td class="width10"><?php echo $lingua['OOT']; ?></td>
            <td class="width25"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha_entregas = $query_entregas->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td class="transparent">
                    <div class="checkbox">
                        <input id="chkDeclaracao_<?php echo $linha_entregas['id']; ?>" name="chkDeclaracao" type="checkbox" class="chk" value="<?php echo $linha_entregas['id']; ?>">
                        <label for="chkDeclaracao" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                        <input id="hddIdDeclaracao" name="hddIdDeclaracao" type="hidden" value="<?php echo $linha_entregas['id']; ?>">
                    </div>
                </td>
                <td><?php echo $linha_entregas['data']; ?></td>
                <td><?php echo $linha_entregas['designacao']; ?></td>
                <td><?php echo $linha_entregas['pago']; ?></td>
                <td><?php echo number_format($linha_entregas['valor'], 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                <td><?php echo $linha_entregas['f_prazo']; ?></td>
                <td><?php echo $linha_entregas['nome']; ?></td>
                <td class="iconwrapper">
                    <input id="hddIdEntrega" name="hddIdEntrega" type="hidden" value="<?php echo $linha_entregas['id']; ?>">
                    <div id="btnIdEntrega_<?php echo $linha_entregas['id']; ?>" name="btnIdEntrega" class="novolabelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
        <input id="hddEntregas" name="hddEntregas" type="hidden" value="1">
    </table>
    <table id="tblDecRetGeral" name="tblDecRetGeral" class="tabela left">
        <tr>
            <td class="width5" style="background-color: #2b6db9;">
                <div class="checkbox">
                    <input id="chkAllDecRet" name="chkAllDecRet" type="checkbox" class="chk" value="0">
                    <label for="chkAllDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                </div>
            </td>
            <td class="width10"><?php echo $lingua['DATE']; ?></td>
            <td class="width20"><?php echo $lingua['RSDN']; ?></td>
            <td class="width10"><?php echo $lingua['PAID']; ?></td>
            <td class="width25"><?php echo $lingua['TOTAL_VAL']; ?></td>
            <td class="width30"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td class="transparent">
                    <div class="checkbox">
                        <input id="chkDecRet_<?php echo $linha_dec_ret['id']; ?>" name="chkDecRet" type="checkbox" class="chk" value="<?php echo $linha_dec_ret['id']; ?>">
                        <label for="chkDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                        <input id="hddIdDecRet" name="hddIdDecRet" type="hidden" value="<?php echo $linha_dec_ret['id']; ?>">
                    </div>
                </td>
                <td><?php echo $linha_dec_ret['data']; ?></td>
                <td><?php echo $linha_dec_ret['residentes']; ?></td>
                <td><?php echo $linha_dec_ret['pago']; ?></td>
                <td><?php echo number_format($linha_dec_ret['total'], 2, ',', '.') . " " . $linha_moeda['simbolo']; ?></td>
                <td><?php echo $linha_dec_ret['nome']; ?></td>
                <td class="iconwrapper">
                    <input name="hddIdDecRet" type="hidden" value="<?php echo $linha_dec_ret['id']; ?>">
                    <div name="btnIdDecRet_<?php echo $linha_dec_ret['id']; ?>" class="novolabelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
        <input id="hddDecRet" name="hddDecRet" type="hidden" value="0">
    </table>
    <table id="tblDecVazia" name="tblDecVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="linha left">
        <div id="divEntregasDetalhes" class="width50 left">
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtDataEntrega" class="labelNormal left"><?php echo $lingua['DATE']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtDataEntrega" name="txtDataEntrega" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtTipoDeclaracao" class="labelNormal left"><?php echo $lingua['TYPE'].' '.$lingua['OF'].' '.$lingua['DLVR']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtTipoDeclaracao" name="txtTipoDeclaracao" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtPago" class="labelNormal left"><?php echo $lingua['PAID']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtPago"name="txtPago" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtForaPrazo" class="labelNormal left"><?php echo $lingua['OOT']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtForaPrazo" name="txtForaPrazo" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtEmpresa" class="labelNormal left"><?php echo $lingua['CMPNY']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtEmpresa"name="txtEmpresa" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtValorEntrega" class="labelNormal left"><?php echo $lingua['TOTAL_VAL']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtValorEntrega" name="txtValor" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtAnoEntrega" class="labelNormal left"><?php echo $lingua['YR']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtAnoEntrega" name="txtAnoEntrega" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtMesEntrega" class="labelNormal left"><?php echo $lingua['MNTH']; ?></label>
                </div>
                <div class="width50 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtMesEntrega" name="txtMesEntrega" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width50 left">
                    <label for="txtPdf" class="labelNormal left"><?php echo $lingua['DOC']; ?></label>
                </div>
                <div class="width50 left">
                    <a href="" target="_blank">
                        <img id="imgPdfEntrega" name="imgPdfEntrega" width="30" height="30" src="images/adobe_logo.png">
                    </a>
                </div>
            </div>
        </div>
        <div class="width50 left" style="text-align: right;margin-top: 2px">
            <button id="btnVoltarEntregas" name="btnVoltarEntregas" class="botao"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
    <div class="linha left">
        <div class="linha10 left">
            <label id="lblDecRet" name="lblDecRet" style="font-size: 18px; color: #2b6db9; font-style: italic;"></label>
        </div>
        <table id="divDecRetDetalhes" name="divDecRetDetalhes" class="tabela left width60">
            <tr>
                <td class="width20"><?php echo $lingua['RUB']; ?></td>
                <td class="width20"><?php echo $lingua['ZON']; ?></td>
                <td class="width60"><?php echo $lingua['TOTAL_VAL']; ?></td>
            </tr>
        </table>
        <div class="width40 left" style="text-align: right;margin-top: 2px">
            <button id="btnVoltarDecRet" name="btnVoltarDecRet" class="botao"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
</div>
<div id="n_tipo">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['CREATE'].' '.lcfirst($lingua['NEW']).' '.lcfirst($lingua['TYPE']).' '.$lingua['OF'].' '.lcfirst($lingua['DLVR']); ?></h3>
        <div class="error"></div>
    </div>
    <div id="frmNovoTipo" name="frmNovoTipo" class="left width40">
        <div class="linha left">
            <div class="width40 left">
                <label for="txtNomeTipo" class="labelNormal left"><?php echo $lingua['NAME'].' '.$lingua['OF'].' '.lcfirst($lingua['TYPE']); ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width100 widthMax190">
                    <input name="txtNomeTipo" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnInserirTipo" name="btnInserirTipo" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
</div>
<div id="e_tipos">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['TYPE']).' '.$lingua['OF'].' '.lcfirst($lingua['DLVR']); ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha10 left">
        <table id="tblTipoEntrega" name="tblTipoEntrega" class="tabela left width40">
            <tr>
                <td><?php echo $lingua['NAME']; ?></td>
            </tr>
            <?php while ($linha_t_ent = $query_t_entrega->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td>
                        <div class="inputareaTable">
                            <input id="txtNomeDesignacao_<?php echo $id_linha++; ?>" name="txtNomeDesignacao" type="text" class="editableText" readonly="readonly" value="<?php echo $linha_t_ent['designacao']; ?>">
                            <input id="hddIdTipoEnt" name="hddIdTipoEnt" type="hidden" value="<?php echo $linha_t_ent['id']; ?>">
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <div class="width40 left textoCentro">
        <button id="btnGuardarTipo" name="btnGuardarTipo" class="botao"><?php echo $lingua['SAVE']; ?></button>
    </div>
</div>
<div id="n_atividade">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['CREATE'].' '.lcfirst($lingua['NEW']).' '.lcfirst($lingua['ACT']); ?></h3>
        <div class="error"></div>
    </div>
    <div id="frmNovaAti" name="frmNovaAti" class="width60">
        <div class="linha10 left">
            <div class="width40 left">
                <label for="txtNomeAtividade" class="labelNormal left"><?php echo $lingua['NAME']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea width100 widthMax190">
                    <input name="txtNomeAtividade" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width40 left">
                <label for="txtCapSocM" class="labelNormal left"><?php echo $lingua['SHR_CAP']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width100 widthMax190">
                    <input name="txtCapSocM" type="text" class="dinheiro editableText">
                    <div class="mnyLabel right">
                        <span><?php echo $linha_moeda['simbolo'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnInserirAtividade" name="btnInserirAtividade" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
</div>
<div id="e_atividade">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.$lingua['ACTS']; ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblAtividades" name="tblAtividades" class="tabela left width70">
        <tr>
            <td class="width20"><?php echo $lingua['NAME']; ?></td>
            <td class="width10"><?php echo $lingua['SHR_CAP']; ?></td>
        </tr>
        <?php
        $atividade = carregar_atividade($connection);
        while ($linha = $atividade->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <tr>
                <td>
                    <div class="inputareaTable width90">
                        <input id="txtNomeDesigAti_<?php echo $id_linha++; ?>" name="txtNomeDesigAti" type="text" class="editableText" readonly="readonly" value="<?php echo $linha['designacao']; ?>">
                        <input id="hddIdAtividade" name="hddIdAtividade" type="hidden" value="<?php echo $linha['id']; ?>">
                    </div>
                </td>
                <td>
                    <div class="inputareaTable width90">
                        <input id="txtCapSocAti" name="txtCapSocAti" type="text" class="editableText dinheiro" readonly="readonly" value="<?php echo number_format($linha['capital_social_monetario'], 2, ',', '.'); ?>">
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblAtividadesVazia" name="tblAtividadesVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="width70 left textoCentro">
        <button id="btnGuardarAtividade" name="btnGuardarAtividade" class="botao"><?php echo $lingua['SAVE']; ?></button>
    </div>
</div>
<div id="emprestimos">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['LNS']; ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblEmprestGeral" name="tblEmprestGeral" class="tabela left width60">
        <tr>
            <td class="width20"><?php echo $lingua['DATE']; ?></td>
            <td class="width20"><?php echo $lingua['PAID']; ?></td>
            <td class="width55"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha_dados = $query_emprestimos->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $linha_dados['data']; ?></td>
                <td><?php echo $linha_dados['pago']; ?></td>
                <td><?php echo $linha_dados['nome']; ?></td>
                <td class="iconwrapper">
                    <input id="hddIdEmprest" name="hddIdEmprest" type="hidden" value="<?php echo $linha_dados['emprest']; ?>">
                    <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_dados['id_empresa']; ?>">
                    <div id="divImgEmprest_<?php echo $linha_dados['emprest']; ?>" name="divImgEmprest" class="novolabelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblEmprestGeralVazia" name="tblEmprestGeralVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="linha left">
        <div class="linha10 left">
            <label id="lblEmpresa" name="lblEmpresa" style="font-size: 18px; color: #2b6db9; font-style: italic;"></label>
        </div>
        <table id="tblEmprestDetail" name="tblEmprestDetail" class="tabela left width80">
            <tr>
                <td class="width15"><?php echo $lingua['DATE']; ?></td>
                <td class="width10"><?php echo $lingua['PAID']; ?></td>
                <td class="width25"><?php echo $lingua['PEND_C']; ?></td>
                <td class="width15"><?php echo $lingua['JUR']; ?></td>
                <td class="width15"><?php echo $lingua['AMR']; ?></td>
                <td class="width20"><?php echo $lingua['PRST']; ?></td>
            </tr>
        </table>
        <div class="width20 left" style="text-align: right;margin-top: 2px">
            <button id="btnVoltarEmprest" name="btnVoltarEmprest" class="botao"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
</div>
<div id="locacoes">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['LSNG']; ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblLocGeral" name="tblLocGeral" class="tabela let width60">
        <tr>
            <td class="width25"><?php echo $lingua['DATE']; ?></td>
            <td class="width25"><?php echo $lingua['PAID']; ?></td>
            <td class="width55"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha_dados = $query_locacao->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $linha_dados['data']; ?></td>
                <td><?php echo $linha_dados['pago']; ?></td>
                <td><?php echo $linha_dados['nome']; ?></td>
                <td class="iconwrapper">
                    <input id="hddIdLeasing" name="hddIdLeasing" type="hidden" value="<?php echo $linha_dados['leas']; ?>">
                    <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_dados['id_empresa']; ?>">
                    <div id="divImgLeas_<?php echo $linha_dados['leas']; ?>" name="divImgLeas" class="novolabelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblLocGeralVazia" name="tblLocGeralVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="linha left">
        <div class="linha10 left">
            <label id="lblEmpresa" name="lblEmpresa" style="font-size: 18px; color: #2b6db9; font-style: italic;"></label>
        </div>
        <table id="tblLocDetail" name="tblLocDetail" class="tabela left width85">
            <tr>
                <td class="width15"><?php echo $lingua['DATE']; ?></td>
                <td class="width5"><?php echo $lingua['PAID']; ?></td>
                <td class="width15"><?php echo $lingua['PEND_C']; ?></td>
                <td class="width10"><?php echo $lingua['JUR']; ?></td>
                <td class="width15"><?php echo $lingua['AMR']; ?></td>
                <td class="width15">P. <?php echo $lingua['WTO'].' '.$lingua['TAX']; ?></td>
                <td class="width10"><?php echo $lingua['TAX']; ?></td>
                <td class="width15">P. <?php echo $lingua['WTH'].' '.$lingua['TAX']; ?></td>
            </tr>
        </table>
        <div class="width15 left" style="text-align: right; margin-top: 2px;">
            <button id="btnVoltarLocacao" name="btnVoltarLocacao" class="botao"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
</div>
<div id="extratos">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['STATEMENT']; ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblExtratoGeral" name="tblExtratoGeral" class="tabela left width60">
        <tr>
            <td class="width60"><?php echo $lingua['NAME']; ?></td>
            <td class="width35"><?php echo $lingua['BLC']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha_dados = $query_empresas2->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $linha_dados['empresa']; ?></td>
                <td><?php echo number_format($linha_dados['saldo_controlo'], 2, ",", "."); ?></td>
                <td class="iconwrapper">
                    <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_dados['id_empresa']; ?>">
                    <div id="divImgEmpresa_<?php echo $linha_dados['id_empresa']; ?>" name="divImgEmpresa" class="novolabelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblExtratoGeralVazia" name="tblExtratoGeralVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="linha left">
        <div class="linha10 left">
            <label id="lblEmpresa" name="lblEmpresa" style="font-size: 18px; color: #2b6db9; font-style: italic;"></label>
        </div>
        <table id="tblExtratoDetail" name="tblExtratoDetail" class="tabela left width85">
            <tr>
                <td class="width10"><?php echo $lingua['DATE']; ?></td>
                <td class="width10"><?php echo $lingua['TYPE']; ?></td>
                <td class="width45"><?php echo $lingua['DESCRIPTION']; ?></td>
                <td class="width15"><?php echo $lingua['OPR']; ?></td>
                <td class="width15"><?php echo $lingua['BLC']; ?></td>
                <td class="width5 transparent"></td>
            </tr>
        </table>
        <div class="width15 left" style="text-align: right;margin-top: 2px">
            <button id="btnVoltarExtrato" name="btnVoltarExtrato" class="botao" style="width: 60%"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
</div>
<div id="titulos_banc">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['TIT']; ?></h3>
        <div class="error"></div>
    </div>
    <table id="tblTitulosGeral" name="tblTitulosGeral" class="tabela left width40">
        <tr>
            <td class="width95"><?php echo $lingua['NAME']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha_dados = $query_titulos->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $linha_dados['empresa']; ?></td>
                <td class="iconwrapper">
                    <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_dados['id_empresa']; ?>">
                    <div id="divImgTitulo_<?php echo $linha_dados['id_empresa']; ?>" name="divImgTitulo" class="novolabelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblTitulosGeralVazia" name="tblTitulosGeralVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div class="linha left">
        <div class="linha10 left">
            <label id="lblEmpresa" name="lblEmpresa" style="font-size: 18px; color: #2b6db9; font-style: italic;"></label>
        </div>
        <table id="tblTitulosDetail" name="tblTitulosDetail" class="tabela left width85">
            <tr>
                <td class="width30"><?php echo $lingua['CMPNY']; ?></td>
                <td class="width15"><?php echo $lingua['DATE']; ?></td>
                <td class="width15"><?php echo $lingua['PRC']; ?></td>
                <td class="width15"><?php echo $lingua['QTY']; ?></td>
                <td class="width15"><?php echo $lingua['STOT']; ?></td>
                <td class="width10"><?php echo $lingua['TYPE']; ?></td>
            </tr>
        </table>
        <div class="width15 left" style="margin-top: 2px; text-align: right">
            <button id="btnVoltarTitulos" name="btnVoltarTitulos" class="botao"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
</div>
<div id="taxas">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['RLS'].'s'; ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha left">
        <?php /* */ ?>
        <span id="hideSlcGrupoTaxas" class="hideSlcGrupoTaxas">
            <select id="slcGrupoTaxas" name="slcGrupoTaxas" class="chosenSelect" data-placeholder="<?php echo $lingua['GRP']; ?>">
                <option value="0" selected="selected"></option>
                <?php // while ($linha_grupo_regra = $query_grupos->fetch(PDO::FETCH_ASSOC)) {
                foreach ($linha_grupo_regra as $lgr) { ?>
                    <!-- <option value="<?php // echo $lgr['id']; ?>"><?php // echo $lgr['nome']; ?></option> -->
                    <option value="<?php echo $lgr['id_grupo']; ?>"><?php echo $lgr['nome']; ?></option>
                <?php } ?>
            </select>
        </span>
        <span id="hideSlcEmpresaTaxas" class="hideSlcEmpresaTaxas">
            <select id="slcEmpresaTaxas" name="slcEmpresaTaxas" class="chosenSelect" data-placeholder="<?php echo $lingua['CMPNY']; ?>">
                <option value="0" selected="selected"></option>
                <?php while ($linha_dados = $query_empresas3->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $linha_dados['id_empresa']; ?>"><?php echo $linha_dados['empresa']; ?></option>
                <?php } ?>
            </select>
        </span>
        <span id="hideSlcRegraTaxas" class="hideSlcRegraTaxas">
            <select id="slcRegraTaxas" name="slcRegraTaxas" class="chosenSelect" data-placeholder="<?php echo $lingua['RLS']; ?>">
                <option value="0" selected="selected"></option>
                <?php while ($row = $query_regra->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $row['id_regra']; ?>"><?php echo $row['nome_regra']; ?></option>
                <?php } ?>
            </select>
        </span>
        <?php /* */ ?>
        <button id="btnGuardarTaxas" name="btnGuardarTaxas" class="botao"><?php echo $lingua['SAVE']; ?></button>
    </div>
    <table id="tblDadosRegras" name="tblDadosRegras" class="tabela left">
        <tr>
            <td class="width20"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width15"><?php echo $lingua['BANK']; ?></td>
            <td class="width40"><?php echo $lingua['RLS']; ?></td>
            <td class="width10"><?php echo $lingua['DATE']; ?></td>
            <td class="width20"><?php echo $lingua['TOTAL_VAL']; ?></td>
        </tr>
        <?php /* while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr class="dados">
                <td style="padding: 4px;"><input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha['id_empresa']; ?>"><?php echo $linha['empresa']; ?></td>
                <td style="padding: 4px;"><input id="hddIdBanco" name="hddIdBanco" type="hidden" value="<?php echo $linha['id_banco']; ?>"><?php echo $linha['banco']; ?></td>
                <td style="padding: 4px;"><input id="hddIdRegra" name="hddIdRegra" type="hidden" value="<?php echo $linha['id_regra']; ?>"><?php echo $linha['nome_regra']; ?></td>
                <td style="padding: 4px;"><?php echo $linha['data']; ?></td>
                <td style="padding: 4px;">
                    <div class="inputareaTable">
                        <input id="txtValorRegra" name="txtValorRegra" type="text" class="editableText dinheiro dynamicInput" readonly="readonly" value="<?php echo number_format($linha['valor'], 2, ',', '.'); ?>"><?php echo $linha['simbolo']; ?>
                        <input id="hddSimboloRegra" name="hddSimboloRegra" type="hidden" value="<?php echo $linha['simbolo']; ?>">
                    </div>
                </td>
            </tr>
        <?php } */ ?>
    </table>
    <table id="tblDadosRegrasVazia" name="tblDadosRegrasVazia" class="tabela left">
        <tr>
            <!-- <td>Não existem dados para mostrar</td> -->
            <td><?php echo $lingua['TBL_MSG']; ?></td>
        </tr>
    </table>
</div>
<div id="calc_acoes">
    <div class="linha left">
        <h3 class="left">Ranking <?php echo $lingua['OF'].' '.lcfirst($lingua['TIT']); ?></h3>
        <div class="error"></div>
    </div>
	<div class="linha left">
        <select id="slcGrupoRanking" name="slcGrupoRanking" class="chosenSelect" data-placeholder="<?php echo $lingua['GRP']; ?>">
            <option value="0" selected="selected"></option>
            <?php $grupo5 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
            while ($linha = $grupo5->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?php echo $linha['id_grupo']; ?>"><?php echo $linha['nome']; ?></option>
            <?php } ?>
        </select>
        <button id="btnCalcular" name="btnCalcular" class="botao" data-sortKey="div.sortMe"><?php echo $lingua['CALC']; ?></button>
        <button id="btnOrdenar" name="btnOrdenar" class="botao"><?php echo $lingua['SORT']; ?></button>
    </div>
	<div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <!--
	<div class="linha">
        <h3">*Em atualizações. Disponivel em breve.</h3>
        <div class="error"></div>
    </div>
    -->
	
    <!-- -->
    <table id="tblAcoes" name="tblAcoes" class="tabela left">
        <tr>
            <td class="width40"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width20"><?php echo $lingua['R_RES']; ?></td>
            <td class="width20"><?php echo $lingua['P_RES']; ?></td>
            <td class="width20"><?php echo $lingua['T_RES']; ?></td>
        </tr>
        <?php /* while ($linha_lucro_real = $query_lucro_real->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr class="tbody">
                <td style="text-align: left; padding: 4px;"><?php echo $linha_lucro_real['nome']; ?></td>
                <td id="tdLucroReal" style="padding: 4px;"><?php echo number_format($linha_lucro_real['lucro_real'], 2, ",", "."); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                <td id="tdLucroPotencial" style="padding: 4px;"></td>
                <td id="tdLucroPotencialReal" class="sortMe" style="padding: 4px;"></td>
            </tr>
        <?php } */ ?>
    </table>
    <table id="tblAcoesVazia" name="tblAcoesVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <!-- -->
</div>
<div id="add_familias">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['ADD'].' '.lcfirst($lingua['CAT']); ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha left textoCentro" style="margin-bottom: 30px;">
        <div class="radio">
            <input id="radCat" name="categorias" type="radio" value="1" checked="checked">
            <label for="radCat" class="btnRadio"><?php echo $lingua['CAT']; ?></label>
            <input id="radSubcat" name="categorias" type="radio" value="2">
            <label for="radSubcat" class="btnRadio"><?php echo $lingua['SCAT']; ?></label>
            <input id="radFam" name="categorias" type="radio" value="3">
            <label for="radFam" class="btnRadio"><?php echo $lingua['FAM']; ?></label>
        </div>
    </div>
    <div id="frmCategoria" name="frmCategoria" class="left width50">
        <div class="linha left">
            <div class="width40 left">
                <label for="txtCategoria" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtCategoria" name="txtCategoria" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnGuardarCategoria" name="btnGuardarCategoria" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
    <div id="frmSubcategoria" name="frmSubcategoria" class="width50">
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcCatAddSubcat" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcCatAddSubcat" name="slcCatAddSubcat" class="chosenSelect" data-placeholder="<?php echo $lingua['CAT']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $categoria1 = carregar_cat($connection);
                    while ($linha = $categoria1->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <option value="<?php echo $linha['id']; ?>"><?php echo $linha['designacao']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha left">
            <div class="width40 left">
                <label for="txtSubcategoria" class="labelNormal left"><?php echo $lingua['SCAT']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtSubcategoria"name="txtSubcategoria" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnGuardarSubcategoria" name="btnGuardarSubcategoria" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
    <div id="frmFamilia" name="frmFamilia" class="width50">
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcCatAddFam" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcCatAddFam" name="slcCatAddFam" class="chosenSelect" data-placeholder="<?php echo $lingua['CAT']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $categoria2 = carregar_cat($connection);
                    while ($linha = $categoria2->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <option value="<?php echo $linha['id']; ?>"><?php echo $linha['designacao']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcSubcatAddFam" class="labelNormal left"><?php echo $lingua['SCAT']; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcSubcatAddFam" name="slcSubcatAddFam" class="chosenSelect" data-placeholder="<?php echo $lingua['SCAT']; ?>">
                    <option value="0" selected="selected"></option>
                </select>
            </div>
        </div>
        <div class="linha left">
            <div class="width40 left">
                <label for="txtFamilia" class="labelNormal left"><?php echo $lingua['FAM']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtFamilia" name="txtFamilia" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnGuardarFamilia" name="btnGuardarFamilia" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
</div>
<div id="asc_familias">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['AFFT'].' '.lcfirst($lingua['CATS']); ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha left textoCentro" style="margin-bottom: 30px;">
        <div class="radio">
            <input id="radAtiCat" name="radCatAti" type="radio" value="1" checked="checked">
            <label for="radAtiCat" class="btnRadio"><?php echo $lingua['ACTS'].' '.$lingua['TO'].' '.lcfirst($lingua['CAT']); ?></label>
            <input id="radCatAti" name="radCatAti" type="radio" value="2">
            <label for="radCatAti" class="btnRadio"><?php echo $lingua['CATS'].' '.$lingua['TO'].' '.lcfirst($lingua['ACT']); ?></label>
        </div>
    </div>
    <div id="frmAtiCat" name="frmAtiCat" class="width90">
        <input id="hddAtiCat" name="hddAtiCat" type="hidden" value="1">
        <div class="linha10 left">
            <div class="width15 left">
                <label for="slcAtividade" class="labelNormal left"><?php echo $lingua['ACT']; ?></label>
            </div>
            <div class="width85 left">
                <select id="slcAtividade" name="slcAtividade" class="chosenSelect" data-placeholder="<?php echo $lingua['ACT']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $atividade2 = carregar_atividade($connection);
                    while ($linha = $atividade2->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <option value="<?php echo $linha['id']; ?>"><?php echo $linha['designacao']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width15 left">
                <label for="chkCategoriaAlt" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
            </div>
            <div class="width85 left" style="height: 100%;">
                <?php
                $categoria3 = carregar_cat($connection);
                while ($linha_categoria = $categoria3->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="checkbox" style="float: left; min-width: 35%;">
                        <input id="chkCategoria_<?php echo $linha_categoria['id']; ?>" name="chkCategoriaAlt" type="checkbox" class="chk" value="<?php echo $linha_categoria['id']; ?>">
                        <label for="chkCategoriaAlt" class="label_chk"><?php echo $linha_categoria['designacao']; ?></label>
                        <input id="hddCategoria" name="hddCategoria" type="hidden" value="<?php echo $linha_categoria['designacao']; ?>">
                        <input id="hddIdCategoria" name="hddIdCategoria" type="hidden" value="<?php echo $linha_categoria['id']; ?>">
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div id="frmCatAti" name="frmCatAti" class="width90">
        <input id="hddCatAti" name="hddCatAti" type="hidden" value="0">
        <div class="linha10 left">
            <div class="width15 left">
                <label for="slcCatAti" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
            </div>
            <div class="width85 left">
                <select id="slcCatAti" name="slcCatAti" class="chosenSelect" data-placeholder="<?php echo $lingua['CAT']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $categoria4 = carregar_cat($connection);
                    while ($linha = $categoria4->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <option value="<?php echo $linha['id']; ?>"><?php echo $linha['designacao']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width15 left">
                <label for="chkAtividade" class="labelNormal left"><?php echo $lingua['ACT']; ?></label>
            </div>
            <div class="width85 left" style="height: 100%;">
                <?php
                $atividade3 = carregar_atividade($connection);
                while ($linha = $atividade3->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="checkbox" style="float: left; min-width: 50%;">
                        <input id="chkAtividade_<?php echo $linha['id']; ?>" name="chkAtividade" type="checkbox" class="chk" value="<?php echo $linha['id']; ?>">
                        <label for="chkAtividade" class="label_chk"><?php echo $linha['designacao']; ?></label>
                        <input id="hddAtividade" name="hddAtividade" type="hidden" value="<?php echo $linha['designacao']; ?>">
                        <input id="hddIdAtividade" name="hddIdAtividade" type="hidden" value="<?php echo $linha['id']; ?>">
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div id="add_produtos">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['ADD'].' '.lcfirst($lingua['PROD']); ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha left">
        <div id="frmAddProduto" name="frmAddProduto" class="left width50">
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcCatAddProd" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcCatAddProd" name="slcCatAddProd" class="chosenSelect" data-placeholder="<?php echo $lingua['CAT']; ?>">
                        <option value="0" selected="selected"></option>
                        <?php
                        $categoria5 = carregar_cat($connection);
                        while ($linha = $categoria5->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <option value="<?php echo $linha['id']; ?>"><?php echo $linha['designacao']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcSubcatAddProd" class="labelNormal left"><?php echo $lingua['SCAT']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcSubcatAddProd" name="slcSubcatAddProd" class="chosenSelect" data-placeholder="<?php echo $lingua['SCAT']; ?>">
                        <option value="0" selected="selected"></option>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcFamAddProd" class="labelNormal left"><?php echo $lingua['FAM']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcFamAddProd" name="slcFamAddProd" class="chosenSelect" data-placeholder="<?php echo $lingua['FAM']; ?>">
                        <option value="0" selected="selected"></option>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtProduto" class="labelNormal left"><?php echo $lingua['NAME']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtProduto" name="txtProduto" type="text" class="editableText">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDescricao" class="labelNormal left"><?php echo $lingua['DESCRIPTION']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtDescricao" name="txtDescricao" type="text" class="editableText">
                    </div>
                </div>
            </div>
            
            <!-- -->
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcPaisFornecedor" class="labelNormal left"><?php echo $lingua['COUNTRY']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcPaisFornecedor" name="slcPaisFornecedor" class="chosenSelect" data-placeholder="<?php echo $lingua['COUNTRY']; ?>">
                        <!-- <option value="0"> -País- </option> -->
                        <!-- <option value="1" selected="selected"> Portugal </option> -->
                        <?php foreach ($linha_pais_fornecedores as $linha) {
                            if ($linha['nome_pais'] == 'Portugal') { ?>
                                <option value="<?php echo $linha['id_pais']; ?>" selected="selected"><?php echo $linha['nome_pais']; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $linha['id_pais']; ?>"><?php echo $linha['nome_pais']; ?></option>
                            <?php } 
                        } ?>    
                    </select>
                </div>
            </div>
            <!-- -->
            
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcFornecedor" class="labelNormal left"><?php echo ucfirst($lingua['SUPPLIER']); ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcFornecedorProduto" name="slcFornecedorProduto" class="chosenSelect" data-placeholder="<?php echo ucfirst($lingua['SUPPLIER']); ?>">
                        <option value="0" selected="selected"></option>
                        <?php foreach ($fornecedores_pt as $linha_fornecedor_pt) { ?>
                            <option value="<?php echo $linha_fornecedor_pt['id']; ?>"><?php echo $linha_fornecedor_pt['nome_abrev']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtPreco" class="labelNormal left"><?php echo $lingua['PRC']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtPreco" name="txtPreco" type="text" class="dinheiro editableText">
                        <div class="mnyLabel right">
                            <span id="simboloMoeda"><?php echo $linha_moeda['simbolo']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha left">
                <div class="width40 left">
                    <label for="slcIVA" class="labelNormal left"><?php echo $lingua['TAX']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcIVA" name="slcIVA" class="chosenSelect" data-placeholder="<?php echo $lingua['TAX']; ?>">
                        <option value="0" selected="selected"></option>
                        <?php
                        $taxa = carregar_taxa($connection, "%Taxa de IVA%");
                        while ($linha = $taxa->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <option value="<?php echo $linha['id_regra']; ?>"><?php echo $linha['valor']; ?><?php echo $linha['simbolo']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="linha left textoCentro">
                <button id="btnGuardarProduto" name="btnGuardarProduto" class="botao"><?php echo $lingua['SAVE']; ?></button>
            </div>
        </div>
    </div>
</div>
<div id="afet_familias">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['SEE'].' '.lcfirst($lingua['AFFTTION']).' '.$lingua['OF'].' '.lcfirst($lingua['CATS']); ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha left textoCentro" style="margin-bottom: 30px;">
        <div class="radio">
            <input id="radEmpCat" name="vEmpCat" type="radio" value="1" checked="checked">
            <label for="radEmpCat" class="btnRadio"><?php echo $lingua['CMPNIES'].' '.$lingua['TO'].' '.$lingua['CAT']; ?></label>
            <input id="radCatEmp" name="vEmpCat" type="radio" value="2">
            <label for="radCatEmp" class="btnRadio"><?php echo $lingua['CATS'].' '.$lingua['TO'].' '.$lingua['CMPNY']; ?></label>
        </div>
    </div>
    <div id="frmEmpCat" name="frmEmpCat">
        <input id="hddEmpCat" name="hddEmpCat" type="hidden" value="1">
        <div class="linha10 left">
            <div class="width15 left">
                <label for="slcEmpresa" class="labelNormal left"><?php echo $lingua['CMPNY']; ?></label>
            </div>
            <div class="width85 left">
                <select id="slcEmpresa" name="slcEmpresa" class="chosenSelect" data-placeholder="<?php echo $lingua['CMPNY']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php while ($linha = $query_empresas4->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $linha['id_empresa']; ?>"><?php echo $linha['empresa']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width15 left">
                <label for="chkCategoria" class="labelNormal left"><?php echo $lingua['CATS']; ?></label>
            </div>
            <div class="width85 left">
                <?php
                $categoria6 = carregar_cat($connection);
                while ($linha_categoria = $categoria6->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="checkbox left" style="min-width: 35%;">
                        <input id="chkCategoria_<?php echo $linha_categoria['id']; ?>" name="chkCategoria" type="checkbox" class="chk" value="<?php echo $linha_categoria['id']; ?>">
                        <label for="chkCategoria" class="label_chk"><?php echo $linha_categoria['designacao']; ?></label>
                        <input id="hddCategoria" name="hddCategoria" type="hidden" value="<?php echo $linha_categoria['designacao']; ?>">
                        <input id="hddIdCategoria" name="hddIdCategoria" type="hidden" value="<?php echo $linha_categoria['id']; ?>">
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div id="frmCatEmp" name="frmCatEmp">
        <input id="hddCatEmp" name="hddCatEmp" type="hidden" value="0">
        <div class="linha10 left">
            <div class="width15 left">
                <label for="slcCatAfetCat" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
            </div>
            <div class="width85 left">
                <select id="slcCatAfetCat" name="slcCatAfetCat" class="chosenSelect" data-placeholder="<?php echo $lingua['CAT']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $categoria7 = carregar_cat($connection);
                    while ($linha = $categoria7->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <option value="<?php echo $linha['id']; ?>"><?php echo $linha['designacao']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width15 left">
                <label for="slcEmpresa" class="labelNormal left"><?php echo $lingua['CMPNY']; ?></label>
            </div>
            <div class="width85 left">
                <?php while ($linha_empresa = $query_empresas5->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class="checkbox left" style="min-width: 35%;">
                        <input id="chkEmpresa_<?php echo $linha_empresa['id_empresa']; ?>" name="chkEmpresa" type="checkbox" class="chk" value="<?php echo $linha_empresa['id_empresa']; ?>">
                        <label for="chkEmpresa" class="label_chk"><?php echo $linha_empresa['empresa']; ?></label>
                        <input id="chkEmpresa" name="chkEmpresa" type="hidden" value="<?php echo $linha_empresa['empresa']; ?>">
                        <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_empresa['id_empresa']; ?>">
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Versão avançada (Paginação) -->
<div id="e_produtos">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['PROD']); ?></h3>
        <div class="error"></div>
    </div>
	
	<div class="linha">
        <ul>
            <li><span style="/*color: red;*/ font-size: 8pt;"><b>Para alterar o nome do Fornecedor, escolha apenas o Fornecedor</b></span>;</li>
            <li><span style="/*color: red;*/ font-size: 8pt;"><b>Para alterar o preço e a taxa de IVA do Produto, escolha apenas a Categoria</b></span>;</li>
            <li><span style="/*color: red;*/ font-size: 8pt;"><b>Para alterar o nome e descrição do Produto, escolha a Categoria e Subcategoria</b></span>;</li>
        </ul>
    </div>
	
    <div class="linha10 left">
        <select id="slcFornecedor" name="slcFornecedor" class="chosenSelect" data-placeholder="<?php echo ucfirst($lingua['SUPPLIER']); ?>">
            <option selected="selected" value="0"></option>
            <?php while ($linha_fornecedor = $query_fornecedor_produtos->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?php echo $linha_fornecedor['id']; ?>"><?php echo $linha_fornecedor['nome_abrev']; ?></option>
            <?php } ?>
        </select>
        <div id="divProcProd" name="divProcProd" class="inputareaProcura left">
            <input id="txtProcProd" name="txtProcProd" type="text" class="procura left editableText" placeholder="<?php echo $lingua['SRCH_TXT']; ?>">
            <div class="iconwrapper left">
                <div class="novolabelicon icon-lupa"></div>
            </div>
        </div>
        <button id="btnGuardarDadosProdutos" name="btnGuardarDadosProdutos" class="botao"><?php echo $lingua['SAVE']; ?></button>
        <!--
        <div class="pagination right">
            <input id="pagTotal" type="hidden" readonly="readonly">
            <input id="pagAtual" type="hidden" readonly="readonly">
            <input id="pagLinhas" type="hidden" readonly="readonly">
            <div class="first left">&laquo;</div>
            <div class="previous left">&lsaquo;</div>
            <input id="pagInput" type="text" readonly="readonly" class="left editableText">
            <div class="next left">&rsaquo;</div>
            <div class="last left">&raquo;</div>
        </div>
        <div id="divSctPagination" name="divSctPagination" class="divSctPagination right">
            <select id="slcPag" name="slcPag" class="chosenSelect" style="width: 90px">
                <option value="5" selected="selected">5</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="20">20</option>
            </select>
        </div>
        -->
    </div>
    <div class="linha left">
        <select id="slcCategoria" name="slcCategoria" class="chosenSelect" data-placeholder="<?php echo $lingua['CAT']; ?>">
            <option value="0" selected="selected"></option>
            <?php
            $categoria8 = carregar_cat($connection);
            while ($linha_categorias = $categoria8->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <option value="<?php echo $linha_categorias['id']; ?>"><?php echo $linha_categorias['designacao']; ?></option>
            <?php } ?>
        </select>
        <select id="slcSubcategoria" name="slcSubcategoria" class="chosenSelect" data-placeholder="<?php echo $lingua['SCAT']; ?>">
            <option value="0" selected="selected"></option>
        </select>
        <select id="slcFamilia" name="slcFamilia" class="chosenSelect" data-placeholder="<?php echo $lingua['FAM']; ?>">
            <option value="0" selected="selected"></option>
        </select>
    </div>
    <table id="tblProdutosAlt" name="tblProdutosAlt" class="tabela left">
        <tr>
            <td class="width20"><?php echo ucfirst($lingua['SUPPLIER']); ?></td>
            <td class="width20"><?php echo $lingua['PROD']; ?></td>
            <td class="width30"><?php echo $lingua['DESCRIPTION']; ?></td>
            <td class="width15"><?php echo $lingua['PRC']; ?></td>
            <td class="width15">Tx. <?php echo $lingua['OF'].' '.$lingua['TAX']; ?></td>
        </tr>
    </table>
    <table id="tblProdutosAltVazio" name="tblProdutosAltVazio" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
</div>

<!-- Versão antiga (online)
<div id="e_produtos">
    <div class="linha" style="display: none;">
        <div class="loading" style="width: 150px;">
            <div class="linha10">
                <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
            </div>
            <div class="linha" style="text-align: center;">A carregar...</div>
        </div>
    </div>
    <div class="linha">
        <div class="left-column">
            <h3>Editar produtos</h3>
        </div>
        <div class="center-right-column">
            <div class="error" style="display: none;"></div>
        </div>
    </div>
    <div class="linha10">
        <div id="divProcProd" style="float: left; background-color: #fff; width: 190px; height: 30px; margin: 0 15px 0 0;">
            <input id="txtProcProd" name="txtProcProd" type="text" style="float: left; height: 28px; width: 153px; border: 1px #77a4d7 solid; margin: 0; padding: 0 0 0 5px; font-size: 14px;" placeholder="Pesquise aqui">
            <label class="icon-lupa" style="float: right; font-size: 20px; border: none; position: absolute; line-height: 1em; color: #eaedf1; background: #77a4d7; padding: 5px;"></label>
        </div>
        <button id="btnGuardarDadosProdutos" name="btnGuardarDadosProdutos" class="btnNoIco" style="padding: 7px 20px; height: 30px; margin: 0;">Guardar</button>
    </div>
    
    <div class="linha">
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcFornecedorProd" name="slcFornecedorProd" class="select">
                    <option value="0" selected="selected">- Fornecedor -</option>
                    <?php
                    while ($linha_fornecedor = $query_fornecedor_produtos->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $linha_fornecedor['id']; ?>"><?php echo $linha_fornecedor['nome_abrev']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcCategoria" name="slcCategoria" class="select">
                    <option value="0" selected="selected">- Categoria -</option>
                    <?php
                    $categoria8 = carregar_cat($connection);
                    while ($linha_categorias = $categoria8->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <option value="<?php echo $linha_categorias['id']; ?>"><?php echo $linha_categorias['designacao']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcSubcategoria" name="slcSubcategoria" class="select">
                    <option value="0" selected="selected">- Subcategoria -</option>
                </select>
            </div>
        </div>
        <div class="inputarea_col1">
            <div class="styled-select">
                <select id="slcFamilia" name="slcFamilia" class="select">
                    <option value="0" selected="selected">- Família -</option>
                </select>
            </div>
        </div>
    </div>
    <table id="tblProdutosAlt" name="tblProdutosAlt" class="tabela">
        <tr>
            <td class="td15">Fornecedor</td>
            <td class="td20">Produto</td>
            <td class="td30">Descrição</td>
            <td class="td15">Preço</td>
            <td class="td20">Taxa IVA</td>
        </tr>
        <?php while ($linha_produto = $query_produtos->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr class="tbody">
                <td style="padding: 2px;"><?php echo $linha_produto['nome_abrev']; ?><input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="<?php echo $linha_produto['id_fornecedor']; ?>"></td>
                <td style="padding: 2px;"><?php echo $linha_produto['nome']; ?><input id="hddIdProd" name="hddIdProd" type="hidden" value="<?php echo $linha_produto['id_produto']; ?>"></td>
                <td style="padding: 2px;"><?php echo $linha_produto['descricao']; ?></td>
                <td style="padding: 2px;"><input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="<?php echo number_format($linha_produto['preco'], 2, ',', '.'); ?>"><?php echo $linha_moeda['simbolo']; ?></td>
                <td style="padding: 2px;">
                    <input id="hddIdRegra" name="hddIdRegra" type="hidden" value="<?php echo $linha_produto['id_regra']; ?>">
                    <div class="inputarea_col1" style="margin: 0 auto; height: 21px; width: 38%; float: none; background-color: transparent;">
                        <div class="styled-select" style="height: 21px;">
                            <select id="slcTaxa" name="slcTaxa" class="select" style="padding: 0 0 0 5%; border: 1px solid #236688; color: #000;">
                                <?php
                                $taxa = carregar_taxa($connection, "%Taxa de IVA%");
                                while ($linha_taxa_iva = $taxa->fetch(PDO::FETCH_ASSOC)) {
                                    if ($linha_taxa_iva['id_regra'] == $linha_produto['id_regra']) {
                                        ?>
                                        <option selected="selected" value="<?php echo $linha_taxa_iva['id_regra']; ?>" style="font-size: 8pt;"><?php echo number_format($linha_taxa_iva['valor'], 0, ',', '.') . " " . $linha_taxa_iva['simbolo']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $linha_taxa_iva['id_regra']; ?>" style="font-size: 8pt;"><?php echo number_format($linha_taxa_iva['valor'], 0, ',', '.') . " " . $linha_taxa_iva['simbolo']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblProdutosAltVazio" name="tblProdutosAltVazio" class="tabela">
        <tr>
            <td>Não existem dados para mostrar</td>
        </tr>
    </table>
</div>
-->

<div id="v_faturas">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['SEE'].' '.lcfirst($lingua['FAT']).'s'; ?></h3>
        <div class="error"></div>
    </div>
    <div class="linha left textoCentro">
        <div class="radio">
            <input id="radFatInt" name="faturas" type="radio" value="0" checked="checked">
            <label for="radFatInt" class="btnRadio"><?php echo $lingua['INTRNL']; ?></label>
            <input id="radFatExt" name="faturas" type="radio" value="1">
            <label for="radFatExt" class="btnRadio"><?php echo $lingua['EXTRNL']; ?></label>
        </div>
    </div>
    <div class="linha left">
        <select id="slcOrdenarFatInt" name="slcOrdenarFatInt" class="chosenSelect" data-placeholder="<?php echo $lingua['ORD_B']; ?>">
            <option selected="selected" value="0"></option>
            <option value="1"><?php echo $lingua['DATE']; ?></option>
            <option value="2"><?php echo $lingua['CMPNY']; ?></option>
        </select>
        
        <span id="hideSlcFiltrarFatGrupo" class="hideSlcFiltrarFatGrupo">
            <select id="slcFiltrarFatGrupo" name="slcFiltrarFatGrupo" class="chosenSelect" data-placeholder="<?php echo $lingua['FIL_B'].' '.$lingua['GRP']; ?>">
                <option selected="selected" value="0"></option>
                <?php
                foreach ($linha_grupo_regra as $lgr2) {
                    ?>
                    <option value="<?php echo $lgr2['id']; ?>"><?php echo $lgr2['nome']; ?></option>
                <?php } ?>
            </select>
        </span>
        
        <span id="hideSlcFiltrarFatInt" class="hideSlcFiltrarFatInt">
            <select id="slcFiltrarFatInt" name="slcFiltrarFatInt" class="chosenSelect" data-placeholder="<?php echo $lingua['FIL_B'].' '.$lingua['CMPNY']; ?>">
                <option selected="selected" value="0"></option>
                <?php
                while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <option value="<?php echo $row['id_empresa']; ?>"><?php echo $row['nome']; ?></option>
                <?php } ?>
            </select>
        </span>
        
        <button id="btnRemFatInt" name="btnRemFatInt" class="botao"><?php echo $lingua['DEL']; ?></button>
    </div>
    <table id="tblDadosFatInt" name="tblDadosFatInt" class="tabela left">
        <tr>
            <?php /* if($_SESSION["admin"] == "0") { ?>
                <td class="width5">
                    <div class="checkbox">
                        <input id="chkAllInt" name="chkAllInt" type="checkbox" class="chk" value="0">
                        <label for="chkAllInt" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                    </div>
                </td>
            <?php } */ ?>
            <td class="<?php if($_SESSION["admin"] == "0") { echo 'td20';} else {echo 'td25';} ?>"><?php echo $lingua['REF']; ?></td>
            <td class="width35"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width15"><?php echo $lingua['DATE']; ?></td>
            <td class="width20"><?php echo $lingua['TOTAL_VAL']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha = $query_faturas_int->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <?php /* if ($_SESSION["admin"] == "0") { ?>
                    <?php if ($linha['pago'] == "0") { ?>
                        <td class="transparent">
                            <div class="checkbox">
                                <input id="chkFatura_<?php echo $linha['id']; ?>" name="chkFatura" type="checkbox" class="chk" value="<?php echo $linha['id']; ?>">
                                <label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                                <input id="hddIdFatura" name="hddIdFatura" type="hidden" value="<?php echo $linha['id']; ?>">
                            </div>
                        </td>
                    <?php } else { ?>
                        <td class="transparent"></td>
                    <?php } ?>
                <?php } */ ?>
                <td><?php echo $linha['ref']; ?><input id="hddIdFatura" name="hddIdFatura" type="hidden" value="<?php echo $linha['id']; ?>"></td>
                <td><?php echo $linha['nome']; ?></td>
                <td><?php echo $linha['data']; ?></td>
                <td><?php echo number_format($linha['total'], 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                <td class="iconwrapper">
                    <div id="btnIDFatInt_<?php echo $linha['id']; ?>" name="btnIDFatInt" class="novolabelicon icon-info"></div>
                </td>
            </tr>
        <?php } ?>
        <input id="hddFatInt" name="hddFatInt" type="hidden" value="1">
    </table>
    <div class="linha left">
        <div id="divFatIntDetail" class="width60 left">
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtRef" class="labelNormal left"><?php echo $lingua['REF']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtRef" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtCliente" class="labelNormal left"><?php echo $lingua['CLIENT']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtCliente" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtData" class="labelNormal left"><?php echo $lingua['DATE']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtData" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDesconto" class="labelNormal left"><?php echo $lingua['DSC']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtDesconto" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtIva" class="labelNormal left"><?php echo $lingua['TAX']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtIva" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtTotal" class="labelNormal left"><?php echo $lingua['TOTAL_VAL']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtTotal" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtPago" class="labelNormal left"><?php echo $lingua['PAID']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtPago" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
        <div class="width40 left" style="margin-top: 2px;">
            <button id="btnVoltarFatInt" name="btnVoltarFatInt" class="botao"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
    <div class="linha left">
        <select id="slcOrdenarFatExt" name="slcOrdenarFatExt" class="chosenSelect" data-placeholder="<?php echo $lingua['ORD_B']; ?>">
            <option selected="selected" value="0"></option>
            <option value="1"><?php echo $lingua['DATE']; ?></option>
            <option value="2"><?php echo $lingua['CPNY']; ?></option>
        </select>
        <span id="hideSlcFiltrarFatExt" class="hideSlcFiltrarFatExt">
            <select id="slcFiltrarFatExt" name="slcFiltrarFatExt" class="chosenSelect" data-placeholder="<?php echo $lingua['FIL_B'].' '.$lingua['CPMNY']; ?>">
                <option selected="selected" value="0"></option>
                <?php
                while ($row = $query_empresa3->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <option value="<?php echo $row['id_empresa']; ?>"><?php echo $row['nome']; ?></option>
                <?php } ?>
            </select>
        </span>
        <button id="btnRemFatExt" name="btnRemFatExt" class="botao"><?php echo $lingua['DEL']; ?></button>
    </div>
    <table id="tblDadosFatExt" name="tblDadosFatExt" class="tabela left">
        <tr>
            <?php /* if($_SESSION["admin"] == "0") { ?>
                <td class="width5">
                    <div class="checkbox">
                        <input id="chkAllExt" name="chkAllExt" type="checkbox" class="chk" value="0">
                        <label for="chkAllExt" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                    </div>
                </td>
            <?php } */ ?>
            <td class="width15">N. <?php echo $lingua['OF'].' '.$lingua['FAT']; ?></td>
            <td class="width25"><?php echo $lingua['CLIENT']; ?></td>
            <td class="width15"><?php echo $lingua['TOTAL_VAL']; ?></td>
            <td class="<?php if($_SESSION["admin"] == "0") { echo 'width10'; } else { echo 'width15'; } ?>"><?php echo $lingua['DATE']; ?></td>
            <td class="width25"><?php echo $lingua['CMPNY']; ?></td>
            <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha = $query_faturas_ext->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <?php /* if($_SESSION["admin"] == "0") { ?>
                    <?php if ($linha['id_factoring'] == null) { ?>
                        <td class="transparent">
                            <div class="checkbox">
                                <input id="chkFatura_<?php echo $linha['id_fatura']; ?>" name="chkFatura" type="checkbox" class="chk" value="<?php echo $linha['id_fatura']; ?>">
                                <label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                                <input id="hddIdFatura" name="hddIdFatura" type="hidden" value="<?php echo $linha['id_fatura']; ?>">
                            </div>
                        </td>
                    <?php } else { ?>
                        <td class="transparent"></td>
                    <?php } ?>
                <?php } */ ?>
                <td><?php echo $linha['num_fatura']; ?></td>
                <td><?php echo $linha['cliente']; ?></td>
                <td><?php echo number_format($linha['valor_fatura'], 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?></td>
                <td><?php echo $linha['data_fatura']; ?></td>
                <td><?php echo $linha['nome']; ?></td>
                <?php if ($linha['id_factoring'] != null) { ?>
                    <td class="iconwrapper">
                        <input id="hddIDFactoring" name="hddIDFactoring" type="hidden" value="<?php echo $linha['id_factoring']; ?>">
                        <div id="btnIDFactoring_<?php echo $linha['id_factoring']; ?>" name="btnIDFactoring" class="novolabelicon icon-info"></div>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
        <input id="hddFatExt" name="hddFatExt" type="hidden" value="0">
    </table>
    <div class="linha left">
        <div id="divFatExtDetail" name="divFatExtDetail" class="width60 left">
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDataFactoring" class="labelNormal left"><?php echo $lingua['DATE']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtDataFactoring" type="text" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtValorFactoring" class="labelNormal left"><?php echo $lingua['TOTAL_VAL']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtValorFactoring" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtTempoFactoring" class="labelNormal left"><?php echo $lingua['TMP']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input type="text" name="txtTempoFactoring" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtRecursoFactoring" class="labelNormal left"><?php echo $lingua['RCRS']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input type="text" name="txtRecursoFactoring" readonly="readonly">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtComissaoFactoring" class="labelNormal left"><?php echo $lingua['CMS']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input type="text" name="txtComissaoFactoring" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtSeguroFactoring" class="labelNormal left"><?php echo $lingua['INSURANCE']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtSeguroFactoring" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtJurosFactoring" class="labelNormal left"><?php echo $lingua['JUR']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtJurosFactoring" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtVRecebidoFactoring" class="labelNormal left"><?php echo $lingua['TOTAL_VAL'].' '.lcfirst($lingua['RECEIVED']); ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input name="txtVRecebidoFactoring" type="text" readonly="readonly" class="dinheiro">
                        <div class="mnyLabel right">
                            <span><?php echo $linha_moeda['simbolo'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="width40 left" style="margin-top: 2px;">
            <button id="btnVoltarFatExt" name="btnVoltarFatExt" class="botao"><?php echo $lingua['BCK']; ?></button>
        </div>
    </div>
    <table id="tblFatVazia" name="tblFatVazia" class="tabela left">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
</div>
<div id="def_cal">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['DEF'].' '.lcfirst($lingua['V_CLND']); ?></h3>
        <div class="error"></div>
    </div>
    <div id="frmCalendario" name="frmCalendario" class="width30 left">
        <div class="linha10 left">
            <div class="width30 left">
                <label for="slcGrupoDefCal" class="labelNormal left"><?php echo $lingua['GRP']; ?></label>
            </div>
            <div class="width70 left">
                <select id="slcGrupoDefCal" name="slcGrupoDefCal" class="chosenSelect width100" data-placeholder="<?php echo $lingua['GRP']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $grupo6 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
                    ?>
                    <?php while ($linha = $grupo6->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $linha['id_grupo']; ?>"><?php echo $linha['nome']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width30 left">
                <label for="slcMesDefCal" class="labelNormal left"><?php echo $lingua['MNTH'].' (virtual)'; ?></label>
            </div>
            <div class="width70 left">
                <select id="slcMesDefCal" name="slcMesDefCal" class="chosenSelect width100" data-placeholder="<?php echo $lingua['MNTH']; ?>">
                    <option value="0" selected="selected"></option>
                    <option value="1"><?php echo $lingua['JAN']; ?></option>
                    <option value="2"><?php echo $lingua['FEV']; ?></option>
                    <option value="3"><?php echo $lingua['MAR']; ?></option>
                    <option value="4"><?php echo $lingua['ABR']; ?></option>
                    <option value="5"><?php echo $lingua['MAI']; ?></option>
                    <option value="6"><?php echo $lingua['JUN']; ?></option>
                    <option value="7"><?php echo $lingua['JUL']; ?></option>
                    <option value="8"><?php echo $lingua['AUG']; ?></option>
                    <option value="9"><?php echo $lingua['SEP']; ?></option>
                    <option value="10"><?php echo $lingua['OCT']; ?></option>
                    <option value="11"><?php echo $lingua['NOV']; ?></option>
                    <option value="12"><?php echo $lingua['DEC']; ?></option>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width30 left">
                <label for="slcAnoDefCal" class="labelNormal left"><?php echo $lingua['YR'].' (virtual)'; ?></label>
            </div>
            <div class="width70 left">
                <select id="slcAnoDefCal" name="slcAnoDefCal" class="chosenSelect width100" data-placeholder="<?php echo $lingua['YR']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $ano = gmdate('Y');
                    ?>
                    <option value="1"><?php echo $ano - 2; ?></option>
                    <option value="2"><?php echo $ano - 1; ?></option>
                    <option value="3"><?php echo $ano; ?></option>
                    <option value="4"><?php echo $ano + 1; ?></option>
                    <option value="5"><?php echo $ano + 2; ?></option>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width30 left">
                <label for="txtDataI" class="labelNormal left"><?php echo $lingua['INI_D']; ?></label>
            </div>
            <div class="width70 left">
                <div class="inputarea left width100">
                    <input id="txtDataI" name="txtDataI" type="text" class="datetimepicker_ini">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width30 left">
                <label for="txtDataF" class="labelNormal left"><?php echo $lingua['FIN_D']; ?></label>
            </div>
            <div class="width70 left">
                <div class="inputarea left width100">
                    <input id="txtDataF" name="txtDataF" type="text" class="datetimepicker_fim">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width30 left">
                <label for="pkrCor" class="labelNormal left"><?php echo $lingua['CLR']; ?></label>
            </div>
            <div class="width70 left">
                <div id="colorSelector">
                    <div style="background-color: #0000ff"></div>
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnGuardarData" name="btnGuardarData" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
    <div id="divCalendario" class="width70 left">
        <div style="margin: 0 auto; width: 90%;">
            <div id="divCalendarioInicio" style="margin-top: 0; font-size: 10px;"></div>
        </div>
    </div>
</div>
<div id="e_cal">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['V_CLND']); ?></h3>
        <div class="error"></div>
    </div>
    <!--
    <div class="linha">
        <?php $grupo10 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']); ?>
        <select id="slcGrupoEditCal" name="slcGrupoEditCal" class="chosenSelect" data-placeholder="Grupo">
        <option value="0" selected="selected"></option>
            <?php while ($linha = $grupo10->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?php echo $linha['id']; ?>"><?php echo $linha['nome']; ?></option>
            <?php } ?>
        </select>
    </div>
    <!-- -->
    <div class="linha left">
        <table id="tblCalendarioGeral" name="tblCalendarioGeral" class="tabela left width70">
            <tr>
                <td class="width50"><?php echo $lingua['GRP']; ?></td>
                <td class="width20"><?php echo $lingua['MNTH']; ?></td>
                <td class="width20"><?php echo $lingua['YR']; ?></td>
                <td class="width5 transparent"></td>
                <td class="width5 transparent"></td>
            </tr>
            <?php while ($linha_dados = $query_calendario->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <!-- <td><?php /* echo $linha_dados['grupo']; */ ?></td> -->
                    <td><?php echo $linha_dados['nome']; ?>
                    <td><?php echo conv_mes($linha_dados['mes']); ?></td>
                    <td><?php echo $linha_dados['ano']; ?></td>
                    <td class="width5 iconwrapper">
                        <input id="hddIdCal" name="hddIdCal" type="hidden" value="<?php echo $linha_dados['id_cal']; ?>">
                        <div id="divImgVerCal_<?php echo $linha_dados['id_cal']; ?>" name="divImgVerCal" class="novolabelicon icon-info"></div>
                    </td>
                    <td class="width5 iconwrapper">
                        <input id="hddIdCal" name="hddIdCal" type="hidden" value="<?php echo $linha_dados['id_cal']; ?>">
                        <div id="divImgRemData_<?php echo $linha_dados['id_cal']; ?>" name="divImgRemData" class="novolabelicon icon-garbage rem_linha"></div>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblCalendarioGeralVazia" name="tblCalendarioGeralVazia" class="tabela left">
            <tr>
                <td><?php echo $lingua['TBL_VAZIA']; ?></td>
            </tr>
        </table>
    </div>
    <div class="linha left">
        <div id="frmDetalhesCal" name="frmDetalhesCal" class="width50 left">
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcGrupoEditCal" class="labelNormal left"><?php echo $lingua['GRP']; ?></label>
                    <input id="hddIdCal" name="hddIdCal" type="hidden">
                </div>
                <div class="width60 left">
                    <?php
                    $grupo7 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
                    //$dados=$grupo7->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <select id="slcGrupoEditCal" name="slcGrupoEditCal" class="chosenSelect">
                        <?php while ($linha = $grupo7->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?php echo $linha['id_grupo']; ?>"><?php echo $linha['nome']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcMesEditCal" class="labelNormal left"><?php echo $lingua['MNTH']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcMesEditCal" name="slcMesEditCal" class="chosenSelect">
                        <option value="1"><?php echo $lingua['JAN']; ?></option>
                        <option value="2"><?php echo $lingua['FEV']; ?></option>
                        <option value="3"><?php echo $lingua['MAR']; ?></option>
                        <option value="4"><?php echo $lingua['ABR']; ?></option>
                        <option value="5"><?php echo $lingua['MAI']; ?></option>
                        <option value="6"><?php echo $lingua['JUN']; ?></option>
                        <option value="7"><?php echo $lingua['JUL']; ?></option>
                        <option value="8"><?php echo $lingua['AUG']; ?></option>
                        <option value="9"><?php echo $lingua['SEP']; ?></option>
                        <option value="10"><?php echo $lingua['OCT']; ?></option>
                        <option value="11"><?php echo $lingua['NOV']; ?></option>
                        <option value="12"><?php echo $lingua['DEC']; ?></option>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcAnoEditCal" class="labelNormal left"><?php echo $lingua['YR']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcAnoEditCal" name="slcAnoEditCal" class="chosenSelect"></select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDatai" class="labelNormal left"><?php echo $lingua['INI_D']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea width100 widthMax190 left">
                        <!-- <input type="text" name="txtDatai" readonly="readonly" class="editableDate"> -->
                        <input type="text" name="txtDatai" class="datetimepicker_ini">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDataf" class="labelNormal left"><?php echo $lingua['FIN_D']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea width100 widthMax190 left">
                        <!-- <input type="text" name="txtDataf" readonly="readonly" class="editableDate"> -->
                        <input type="text" name="txtDataf" class="datetimepicker_fim">
                    </div>
                </div>
            </div>
            <div class="linha left">
                <div class="width40 left">
                    <label for="txtCor" class="labelNormal left"><?php echo $lingua['CLR']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="pickerCor"></div>
                </div>
            </div>
            <div class="linha left textoCentro">
                <button id="btnUpdateData" name="btnUpdateData" class="botao"><?php echo $lingua['SAVE']; ?></button>
                <button id="btnVoltarCal" name="btnVoltarCal" class="botao"><?php echo $lingua['BCK']; ?></button>
            </div>
        </div>
    </div>
</div>

<!--
<div id="imp_cal">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro">A carregar...</div>
    </div>
    <div class="linha left">
        <h3 class="left">Importar calendário virtual</h3>
        <div class="error"></div>
    </div>
    <div id="frmImpCalendario" name="frmImpCalendario" class="width30 left">
        <div class="linha10 left">
            <div class="width30 left">
                <label for="slcGrupoCal" class="labelNormal left">Grupo a definir</label>
            </div>
            <div class="width70 left">
                <select id="slcGrupoCal" name="slcGrupoCal" class="chosenSelect width100" data-placeholder="Grupo">
                    <option value="0" selected="selected"></option>
                    <?php
                    $grupo8 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
                    ?>
                    <?php while ($linha = $grupo8->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $linha['id']; ?>"><?php echo $linha['nome']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
		<div class="linha10 left">
            <div class="width30 left">
                <label for="slcGrupoImpCal" class="labelNormal left">Calendário a importar</label>
            </div>
            <div class="width70 left">
                <select id="slcGrupoImpCal" name="slcGrupoImpCal" class="chosenSelect width100" data-placeholder="Grupo">
                    <option value="0" selected="selected"></option>
                    <?php
                    $grupo9 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
                    ?>
                    <?php while ($linha = $grupo9->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $linha['id']; ?>"><?php echo $linha['nome']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnGuardarData" name="btnGuardarData" class="botao">Guardar</button>
        </div>
    </div>
    <div id="divCalendario" class="width70 left">
        <div style="margin: 0 auto; width: 90%;">
            <div id="divCalendarioInicio" style="margin-top: 0; font-size: 10px;"></div>
        </div>
    </div>
</div>
<!-- -->

<div id="outras_op">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['O_OPERA']; ?></h3>
        <div class="error"></div>
    </div>
    <div id="divOutrasOperacoes" name="divOutrasOperacoes" class="width60 left">
        <div class="linha10 left">
            <div class="width20 left">
                <label class="labelNormal left"><?php echo $lingua['TOTAL_VAL']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtValor" name="txtValor" type="text" class="dinheiro editableText">
                    <div class="mnyLabel right">
                        <span><?php echo $linha_moeda['simbolo'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label class="labelNormal left"><?php echo $lingua['DATE']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtDataOp" name="txtDataOp" type="text" readonly="readonly">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width20 left">
                <label class="labelNormal left"><?php echo $lingua['DESCRIPTION']; ?></label>
            </div>
            <div class="width80 left">
                <div class="inputarea left width100 widthMax380">
                    <input id="txtDescricao" name="txtDescricao" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width20 left">
                <label class="labelNormal left"><?php echo $lingua['DEST'].'s'; ?></label>
            </div>
            <div class="width80 left">
                <div class="dados_imp">
                    <textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq width100 widthMax190"></textarea>
                    <input id="hddDestinatario" name="hddDestinatario" type="hidden">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <div class="radio">
                <input id="radCobrar" name="radCobrarPagar" type="radio" value="0" checked="checked">
                <label for="radCobrar" class="btnRadio"><?php echo $lingua['T_CHRG']; ?></label>
                <input id="radPagar" name="radCobrarPagar" type="radio" value="1">
                <label for="radPagar" class="btnRadio"><?php echo $lingua['T_PAY']; ?></label>
            </div>
        </div>
        <div class="width100 left textoCentro">
            <button id="btnEfOp" name="btnEfOp" class="botao"><?php echo $lingua['MAKE']; ?></button>
        </div>
    </div>
</div>

<!--Alertas acao venda conteudo (inicio)-->
<div id="alertas">
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['ALERT']; ?></h3>
        <div class="error"></div>
        </div>
        <br>
        <table id="tblAlertasGeral" name="tblAlertasGeral" class="tabela left width100">
            
        <tr>
            <td class="width10"><?php echo 'ID ACAO TRANS'; ?></td>
            <td class="width10">login</td>
            <td class="width15"><?php echo 'nome'; ?></td>
             <td class="width15"><?php echo 'simbolo'; ?></td>
             <td class="width10"><?php echo 'preco de compra'; ?></td>
             <td class="width10"><?php echo 'quantidade'; ?></td>
             <td class="width10"><?php echo 'preco_atual'; ?></td>
             <td class="width10"><?php echo 'data de alerta'; ?></td>
             <td class="width5 transparent"></td>
        </tr>
        <?php while ($linha_alertas = $query_alertas->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="padding: 6px;"><?php echo $linha_alertas['id_acao_trans']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_alertas['login']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_alertas['nome']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_alertas['simbolo']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_alertas['preco_compra']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_alertas['quantidade']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_alertas['preco_atual']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_alertas['date_reg']; ?></td>
                    
                    <td class="width5 iconwrapper">
                        <input id="hddIdAlerta" name="hddIdAlerta" type="hidden" value="<?php echo $linha_alertas['id']; ?>">
                        <div id="fDivImgRemAlerta_<?php echo $linha_alertas['id']; ?>" name="fDivImgRemAlerta" class="novolabelicon icon-garbage rem_linha"></div>
                    </td>
                </tr>
            <?php } ?>
    </table>
    
</div>
<!--Alertas acao venda conteudo (fim)-->
<div id="e_familias">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['CAT']); ?></h3>
        <div class="error"></div>
    </div>
    <div id="divEditCat" name="divEditCat">
        <div class="linha10 left">
            <select id="slcCatEditCat" name="slcCatEditCat" class="chosenSelect" data-placeholder="<?php echo $lingua['CAT']; ?>">
                <option value="0" selected="selected"></option>
                <?php
                $categoria9 = carregar_cat($connection);
                while ($linha = $categoria9->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <option value="<?php echo $linha['id']; ?>"><?php echo $linha['designacao']; ?></option>
                <?php } ?>
            </select>
            <select id="slcSubcatEditCat" name="slcSubcatEditCat" class="chosenSelect" data-placeholder="<?php echo $lingua['SCAT']; ?>">
                <option value="0" selected="selected"></option>
            </select>
            <button id="btnGuardarCat" name="btnGuardarCat" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
        <table id="tblCategorias" name="tblCategorias" class="tabela left width40">
            <tr>
                <td><?php echo $lingua['NAME']; ?></td>
            </tr>
            <?php
            $categoria10 = carregar_cat($connection);
            while ($linha_categoria = $categoria10->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <tr>
                    <td>
                        <div class="inputareaTable">
                            <input id="txtNomeDesignacao_<?php echo $id_linha++; ?>" name="txtNomeDesignacao" type="text" class="editableText" readonly="readonly" value="<?php echo $linha_categoria['designacao']; ?>">
                            <input id="hddIdCat" name="hddIdCat" type="hidden" value="<?php echo $linha_categoria['id']; ?>">
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
<div id="mod_pass">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['CH_PASS']; ?></h3>
        <div class="error"></div>
    </div>
    <div id="divMudarPass" class="width50 left">
        <div class="linha10 left">
            <div class="width50 left">
                <label for="txtPassOld" class="labelNormal left"><?php echo $lingua['OLD_PASS']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtPassOld" name="txtPassOld" type="password" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width50 left">
                <label for="txtPassNew" class="labelNormal left"><?php echo $lingua['NEW_PASS']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtPassNew" name="txtPassNew" type="password" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width50 left">
                <label for="txtPassRep" class="labelNormal left"><?php echo $lingua['CONF'].' '.lcfirst($lingua['NEW_PASS']); ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea left width100 widthMax190">
                    <input id="txtPassRep" name="txtPassRep" type="password" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnModify" name="btnModify" class="botao"><?php echo $lingua['SAVE'] ?></button>
        </div>
    </div>
</div>
<div id="novo_admin">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['CREATE'].' '.lcfirst($lingua['ADM']); ?></h3>
        <div class="error"></div>
    </div>
    <div id="divSwitchLDAP" class="linha left">
        <div class="linha left">
            <div class="width20 left">
                <label for="chkLDAP" class="labelNormal left"><?php echo $lingua['ADM'].' '.$lingua['WTH']; ?> LDAP</label>
            </div>

            <div class="width80 left">
                <div class="onoffswitch">
                    <input id="chkLDAP" name="chkLDAP" type="checkbox" class="onoffswitch-checkbox" checked="checked">
                    <label class="onoffswitch-label" for="chkLDAP">
                        <div class="onoffswitch-inner"></div>
                        <div class="onoffswitch-switch"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <table id="tblAdministradores" name="tblAdministradores" class="tabela left width50">
        <tr>
            <td class="width10">Login</td>
            <td class="width70"><?php echo $lingua['NAME']; ?></td>
            <td class="width70"><?php echo $lingua['DATE']; ?></td>
        </tr>
        <?php while ($linha = $query_admin->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td style="padding: 6px;"><?php echo $linha['login']; ?></td>
                <td style="padding: 6px;"><?php echo $linha['nome']; ?></td>
                <td style="padding: 6px;"><?php echo $linha['data']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <table id="tblAdministradoresVazia" name="tblAdministradoresVazia" class="tabela left width50">
        <tr>
            <td><?php echo $lingua['TBL_VAZIA']; ?></td>
        </tr>
    </table>
    <div id="divNovoAdmin" style="float: left; width: 47%; margin-left: 3%;">
        <div class="linha left">
            <div class="width50 left">
                <label for="txtLoginNovoAdmin" class="labelNormal left"><?php echo $lingua['USERNAME']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea">
                    <input id="txtLoginNovoAdmin" name="txtLoginNovoAdmin" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width50 left">
                <label for="txtNomeNovoAdmin" class="labelNormal left"><?php echo $lingua['NAME']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea">
                    <input id="txtNomeNovoAdmin" name="txtNomeNovoAdmin" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width50 left">
                <label for="txtPassNovoAdmin" class="labelNormal left"><?php echo $lingua['PASSWORD']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea">
                    <input id="txtPassNovoAdmin" name="txtPassNovoAdmin" type="password" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width50 left">
                <label for="txtPassRepNovoAdmin" class="labelNormal left"><?php echo $lingua['CONF'].' '.$lingua['PASSWORD']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea">
                    <input id="txtPassRepNovoAdmin" name="txtPassRepNovoAdmin" type="password" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left">
            <div class="width50 left">
                <label for="txtEmailAdmin" class="labelNormal left"><?php echo $lingua['EMAIL']; ?></label>
            </div>
            <div class="width50 left">
                <div class="inputarea">
                    <input id="txtEmailNovoAdmin" name="txtEmailNovoAdmin" type="text" class="editableText">
                </div>
            </div>
        </div>
        <div class="linha left textoCentro">
            <button id="btnNovoAdmin" name="btnNovoAdmin" class="botao"><?php echo $lingua['SAVE'] ?></button>
        </div>
    </div>
</div>
<!-- -->
<div id="afet_produtos">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['AFFT'].' '.lcfirst($lingua['PROD']).' '.$lingua['TO'].' '.lcfirst($lingua['SUPPLIER']); ?></h3>
        <div class="error"></div>
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha left">
        <div id="frmAfetProdFornec" name="frmAfetProdFornec" class="left width50">
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcPaisFornecedorAfet" class="labelNormal left"><?php echo $lingua['COUNTRY']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcPaisFornecedorAfet" name="slcPaisFornecedorProd" class="chosenSelect" data-placeholder="<?php echo $lingua['COUNTRY']; ?>">
                        <?php foreach ($linha_pais_fornecedores as $linha) {
                            if ($linha['nome_pais'] == 'Portugal') { ?>
                                <option value="<?php echo $linha['id_pais']; ?>" selected="selected"><?php echo $linha['nome_pais']; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $linha['id_pais']; ?>"><?php echo $linha['nome_pais']; ?></option>
                            <?php } 
                        } ?>    
                    </select>
                </div>
            </div>
            <!-- -->
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcFornecedorProdutoAfet" class="labelNormal left"><?php echo ucfirst($lingua['SUPPLIER']); ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcFornecedorProdutoAfet" name="slcFornecedorProd" class="chosenSelect" data-placeholder="<?php echo ucfirst($lingua['SUPPLIER']); ?>">
                        <option value="0" selected="selected"></option>
                        <?php foreach ($fornecedores_pt as $linha_fornecedor_pt) { ?>
                            <option value="<?php echo $linha_fornecedor_pt['id']; ?>"><?php echo $linha_fornecedor_pt['nome_abrev']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcProdAfet" class="labelNormal left"><?php echo $lingua['PROD']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcProdAfet" name="slcProd" class="chosenSelect" data-placeholder="<?php echo $lingua['PROD']; ?>">
                        <option value="0" selected="selected"></option>
                        <?php
                        while ($linha_prod_tot = $query_produtos_tot->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <option value="<?php echo $linha_prod_tot['id']; ?>"><?php echo $linha_prod_tot['nome']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDescricaoAfet" class="labelNormal left"><?php echo $lingua['DESCRIPTION']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtDescricaoAfet" name="txtDescricaoProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['DESCRIPTION']; ?>">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtCategoriaAfet" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtCategoriaAfet" name="txtCategoriaProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['CAT']; ?>">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtSubcategoriaAfet" class="labelNormal left"><?php echo $lingua['SCAT']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtSubcategoriaAfet" name="txtSubcategoriaProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['SCAT']; ?>">
                    </div>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtFamiliaAfet" class="labelNormal left"><?php echo $lingua['FAM']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtFamiliaAfet" name="txtFamiliaProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['FAM']; ?>">
                    </div>
                </div>
            </div>
            <!-- -->
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtPrecoAfet" class="labelNormal left"><?php echo $lingua['PRC']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtPrecoAfet" name="txtPrecoProd" type="text" class="dinheiro editableText">
                        <div class="mnyLabel right">
                            <span id="simboloMoeda"><?php echo $linha_moeda['simbolo']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha left">
                <div class="width40 left">
                    <label for="slcIVAAfet" class="labelNormal left"><?php echo $lingua['TAX']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="width60 left">
                        <div class="inputarea left width100 widthMax190">
                            <input id="txtIVAAfet" name="txtIVAProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['TAX']; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha left textoCentro">
                <button id="btnAfetProduto" name="btnAfetProduto" class="botao"><?php echo $lingua['SAVE']; ?></button>
            </div>
        </div>
    </div>
</div>
<!-- -->
<div id="add_desc">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['ADD'].' '.lcfirst($lingua['DSC']); ?></h3>
        <div class="error"></div>
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha left">
        <div id="frmAddDescProd" name="frmAddDescProd" class="left width50">
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcTipoDesc" class="labelNormal left"><?php echo $lingua['TYPE']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcTipoDesc" name="slcTipoDesc" class="chosenSelect">
                        <option value="financ" selected="selected"><?php echo $lingua['FINCL']; ?></option>
                        <option value="comerc">Comercial</option>
                    </select>
                </div>
            </div>
            
            <!-- -->
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcPaisFornecedorDesc" class="labelNormal left"><?php echo $lingua['COUNTRY']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcPaisFornecedorDesc" name="slcPaisFornecedorProd" class="chosenSelect" data-placeholder="<?php echo $lingua['COUNTRY']; ?>">
                        <?php foreach ($linha_pais_fornecedores as $linha) {
                            if ($linha['nome_pais'] == 'Portugal') { ?>
                                <option value="<?php echo $linha['id_pais']; ?>" selected="selected"><?php echo $linha['nome_pais']; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $linha['id_pais']; ?>"><?php echo $linha['nome_pais']; ?></option>
                            <?php } 
                        } ?>    
                    </select>
                </div>
            </div>
            <!-- -->
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcFornecedorProdutoDesc" class="labelNormal left"><?php echo $lingua['SUPPLIER']; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcFornecedorProdutoDesc" name="slcFornecedorProd" class="chosenSelect" data-placeholder="<?php echo $lingua['SUPPLIER']; ?>">
                        <option value="0" selected="selected"></option>
                        <?php foreach ($fornecedores_pt as $linha_fornecedor_pt) { ?>
                            <option value="<?php echo $linha_fornecedor_pt['id']; ?>"><?php echo $linha_fornecedor_pt['nome_abrev']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            
            <div id="divDescFinanc">
                <div class="linha10 left">
                    <div class="width40 left">
                        <label for="slcProdDesc" class="labelNormal left"><?php echo $lingua['PROD']; ?></label>
                    </div>
                    <div class="width60 left">
                        <select id="slcProdDesc" name="slcProd" class="chosenSelect" data-placeholder="<?php echo $lingua['PROD']; ?>">
                            <option value="0" selected="selected"></option>
                        </select>
                    </div>
                </div>
                <div class="linha10 left">
                    <div class="width40 left">
                        <label for="txtDescricaoDesc" class="labelNormal left"><?php echo $lingua['DESCRIPTION']; ?></label>
                    </div>
                    <div class="width60 left">
                        <div class="inputarea left width100 widthMax190">
                            <input id="txtDescricaoDesc" name="txtDescricaoProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['DESCRIPTION']; ?>">
                        </div>
                    </div>
                </div>
                <div class="linha10 left">
                    <div class="width40 left">
                        <label for="txtCategoriaDesc" class="labelNormal left"><?php echo $lingua['CAT']; ?></label>
                    </div>
                    <div class="width60 left">
                        <div class="inputarea left width100 widthMax190">
                            <input id="txtCategoriaDesc" name="txtCategoriaProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['CAT']; ?>">
                        </div>
                    </div>
                </div>
                <div class="linha10 left">
                    <div class="width40 left">
                        <label for="txtSubcategoriaDesc" class="labelNormal left"><?php echo $lingua['SCAT']; ?></label>
                    </div>
                    <div class="width60 left">
                        <div class="inputarea left width100 widthMax190">
                            <input id="txtSubcategoriaDesc" name="txtSubcategoriaProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['SCAT']; ?>">
                        </div>
                    </div>
                </div>
                <div class="linha10 left">
                    <div class="width40 left">
                        <label for="txtFamiliaDesc" class="labelNormal left"><?php echo $lingua['FAM']; ?></label>
                    </div>
                    <div class="width60 left">
                        <div class="inputarea left width100 widthMax190">
                            <input id="txtFamiliaDesc" name="txtFamiliaProd" type="text" readonly="readonly" placeholder="<?php echo $lingua['FAM']; ?>">
                        </div>
                    </div>
                </div>
                <div class="linha10 left">
                    <div class="width40 left">
                        <label for="txtPrzPagProd" class="labelNormal left"><?php echo $lingua['P_PER']; ?></label>
                    </div>
                    <div class="width60 left">
                        <div class="inputarea left width100 widthMax190">
                            <input id="txtPrzPagProd" name="txtPrzPagProd" type="text" class="dinheiro editableText">
                            <!-- --> <div class="mnyLabel right">
                                <span id="przPag" style="font-size: 7pt;"><?php echo $lingua['DAY'].'s'; ?></span>
                            </div> <!-- -->
                        </div>
                    </div>
                </div>
            </div>
                
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDescProd" class="labelNormal left"><?php echo $lingua['DSC']; ?></label>
                </div>
                <div class="width60 left">
                    <div class="inputarea left width100 widthMax190">
                        <input id="txtDescProd" name="txtDescProd" type="text" class="dinheiro editableText">
                        <div class="mnyLabel right">
                            <span id="txDsc">%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="linha left textoCentro">
                <button id="btnAddDescProd" name="btnAddDescProd" class="botao"><?php echo $lingua['SAVE']; ?></button>
            </div>
        </div>
    </div>
</div>

<div id="edit_desc">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['DSC']); ?></h3>
        <div class="error"></div>
    </div>
    
    <div class="linha10 left">
        <select id="slcFornecDesc" name="slcFornecDesc" class="chosenSelect" data-placeholder="<?php echo $lingua['FIL_B'].' '.$lingua['SUPPLIER']; ?>">
            <option value="0" selected="selected"></option>
            <?php while ($linha_fornec_desc = $query_fornec_desc->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?php echo $linha_fornec_desc['id']; ?>"><?php echo $linha_fornec_desc['fornecedor']; ?></option>
            <?php } ?>
        </select>
        <button id="btnGuardarChgDesc" name="btnGuardarChgDesc" class="botao"><?php echo $lingua['SAVE']; ?></button>
    </div>
    
    <div class="linha"> <!-- *Espaço entre linhas* --> &nbsp;</div>
    
    <div class="linha">
        <table id="tblDescDetail" name="tblDescDetail" class="tabela">
            <tr>
                <td class="width25"><?php echo $lingua['SUPPLIER']; ?></td>
                <td class="width25"><?php echo $lingua['PROD']; ?></td>
                <td class="width15"><?php echo $lingua['DSC']; ?></td>
                <td class="width15"><?php echo $lingua['P_PER']; ?></td>
                <td class="width15"><?php echo $lingua['STAT']; ?></td>
                <td class="width5 transparent"></td>
            </tr>
            <?php while ($linha_fp_desc = $query_fp_desc->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td style="padding: 6px;"><?php echo $linha_fp_desc['fornecedor']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_fp_desc['produto']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_fp_desc['desconto']; ?></td>
                    <td style="padding: 6px;"><?php echo $linha_fp_desc['prazo_pag'].' '.$lingua['DAY'].'s'; ?></td>
                    <td style="padding: 6px;">
                        <select id="slcEstadoDesc_<?php echo $linha_fp_desc['id_desconto']; ?>" name="slcEstadoDesc" class="chosenTabelaSelect">
                            <option value="0" <?php if ($linha_fp_desc['active'] == "0") echo 'selected="selected"' ?>> <?php echo $lingua['DSBL']; ?> </option>
                            <option value="1" <?php if ($linha_fp_desc['active'] == "1") echo 'selected="selected"' ?>> <?php echo $lingua['ENBL']; ?> </option>
                        </select>
                    </td>
                    <td class="iconwrapper">
                        <div name="btnIDDesc" class="novolabelicon icon-garbage delDesc">
                            <input id="hddIdDesc" name="hddIdDesc" type="hidden" value="<?php echo $linha_fp_desc['id_desconto']; ?>">
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <table id="tblDescDetailVazia" name="tblDescDetailVazia" class="tabela">
            <tr>
                <td><?php echo $lingua['TBL_VAZIA']; ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Div para adicionar tarefas a serem exibidas no calendário -->
<div id="def_tasks_cal">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <!-- <h3 class="left"><?php echo $lingua['CREATE'].' '.lcfirst($lingua['NEW']).' '.lcfirst($lingua['GRP']); ?></h3> -->
        <h3 class="left">Adicionar tarefa</h3>
        <div class="error"></div>
    </div>
    <div id="frmNewTask" name="frmNewTask" class="width60">
        <div class="linha10 left">
            <div class="width40 left">
                <label for="txtTaskDesc" class="labelNormal left">Descrição</label>
            </div>
            <div class="width60 left">
                <!-- <div class="inputarea left width100">
                    <input id="txtTaskDesc" name="txtTaskDesc" type="text" class="editableText">
                </div> -->
                <textarea id="txtDescTasks" name="txtDescTasks" rows="5" class="caixaTextoNormal" style="width: 98%"></textarea>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcGrupoDefTask" class="labelNormal left"><?php echo $lingua['GRP']; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcGrupoDefTask" name="slcGrupoDefTask" class="chosenSelect width100" data-placeholder="<?php echo $lingua['GRP']; ?>">
                    <option value="0" selected="selected"></option>
                    <?php
                    $grupo11 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
                    ?>
                    <?php while ($linha = $grupo11->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $linha['id_grupo']; ?>"><?php echo $linha['nome']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <?php /* <div class="linha10 left">
            <div class="width40 left">
                <label for="txtDataI" class="labelNormal left"><?php echo $lingua['INI_D']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width100">
                    <input id="txtDataI" name="txtDataI" type="text" class="datepicker">
                </div>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width40 left">
                <label for="txtDataF" class="labelNormal left"><?php echo $lingua['FIN_D']; ?></label>
            </div>
            <div class="width60 left">
                <div class="inputarea left width100">
                    <input id="txtDataF" name="txtDataF" type="text" class="datepicker">
                </div>
            </div>
        </div> */ ?>
        
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcMesIniDefCalTask" class="labelNormal left"><?php echo $lingua['MNTH'].' '.$lingua['INI'].' '.' Virtual'; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcMesIniDefCalTask" name="slcMesIniDefCalTask" class="chosenSelect width100" data-placeholder="<?php echo $lingua['MNTH'].' '.$lingua['INI'].' '.' Virtual'; ?>">
                    <option value="0" selected="selected"></option>
                    <option value="1"><?php echo $lingua['JAN']; ?></option>
                    <option value="2"><?php echo $lingua['FEV']; ?></option>
                    <option value="3"><?php echo $lingua['MAR']; ?></option>
                    <option value="4"><?php echo $lingua['ABR']; ?></option>
                    <option value="5"><?php echo $lingua['MAI']; ?></option>
                    <option value="6"><?php echo $lingua['JUN']; ?></option>
                    <option value="7"><?php echo $lingua['JUL']; ?></option>
                    <option value="8"><?php echo $lingua['AUG']; ?></option>
                    <option value="9"><?php echo $lingua['SEP']; ?></option>
                    <option value="10"><?php echo $lingua['OCT']; ?></option>
                    <option value="11"><?php echo $lingua['NOV']; ?></option>
                    <option value="12"><?php echo $lingua['DEC']; ?></option>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcDiaIniDefCalTask" class="labelNormal left"><?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['INI']).' '.' Virtual'; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcDiaIniDefCalTask" name="slcDiaIniDefCalTask" class="chosenSelect width100" data-placeholder="<?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['INI']).' '.' Virtual'; ?>">
                    <option value="0" selected="selected"></option>
                    <?php for($k=1; $k<=31; $k++) { ?>
                        <option value="<?php echo $k ?>"><?php echo $k ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcMesFimDefCalTask" class="labelNormal left"><?php echo $lingua['MNTH'].' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcMesFimDefCalTask" name="slcMesFimDefCalTask" class="chosenSelect width100" data-placeholder="<?php echo $lingua['MNTH'].' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?>">
                    <option value="0" selected="selected"></option>
                    <option value="1"><?php echo $lingua['JAN']; ?></option>
                    <option value="2"><?php echo $lingua['FEV']; ?></option>
                    <option value="3"><?php echo $lingua['MAR']; ?></option>
                    <option value="4"><?php echo $lingua['ABR']; ?></option>
                    <option value="5"><?php echo $lingua['MAI']; ?></option>
                    <option value="6"><?php echo $lingua['JUN']; ?></option>
                    <option value="7"><?php echo $lingua['JUL']; ?></option>
                    <option value="8"><?php echo $lingua['AUG']; ?></option>
                    <option value="9"><?php echo $lingua['SEP']; ?></option>
                    <option value="10"><?php echo $lingua['OCT']; ?></option>
                    <option value="11"><?php echo $lingua['NOV']; ?></option>
                    <option value="12"><?php echo $lingua['DEC']; ?></option>
                </select>
            </div>
        </div>
        <div class="linha10 left">
            <div class="width40 left">
                <label for="slcDiaFimDefCalTask" class="labelNormal left"><?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?></label>
            </div>
            <div class="width60 left">
                <select id="slcDiaFimDefCalTask" name="slcDiaFimDefCalTask" class="chosenSelect width100" data-placeholder="<?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?>">
                    <option value="0" selected="selected"></option>
                    <?php for($k=1; $k<=31; $k++) { ?>
                        <option value="<?php echo $k ?>"><?php echo $k ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        
        <div class="linha">&nbsp;</div>
        
        <div class="linha left textoCentro">
            <button id="btnSaveTask" name="btnSaveTask" class="botao"><?php echo $lingua['SAVE']; ?></button>
        </div>
    </div>
</div>

<!-- Div para editar tarefas a serem exibidas no calendário -->
<div id="e_tasks_cal">
    <div class="loading">
        <div class="linha10 left">
            <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
        </div>
        <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
    </div>
    <div class="linha left">
        <!-- <h3 class="left"><?php echo $lingua['EDT'].' '.lcfirst($lingua['V_CLND']); ?></h3> -->
        <h3 class="left">Editar tarefa</h3>
        <div class="error"></div>
    </div>
    
    <?php /* Codigo para filtro por Grupo * /
    <div class="linha">
        <?php $grupo10 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']); ?>
        <select id="slcGrupoEditCal" name="slcGrupoEditCal" class="chosenSelect" data-placeholder="Grupo">
        <option value="0" selected="selected"></option>
            <?php while ($linha = $grupo10->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?php echo $linha['id']; ?>"><?php echo $linha['nome']; ?></option>
            <?php } ?>
        </select>
    </div>
    */ ?>
    
    <div class="linha left">
        <table id="tblCalendTasksGeral" name="tblCalendTasksGeral" class="tabela left width70">
            <tr>
                <td class="width30"><?php echo $lingua['GRP']; ?></td>
                <td class="width10"><?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['INI']).' '.' Virtual'; ?></td>
                <td class="width20"><?php echo $lingua['MNTH'].' '.$lingua['INI'].' '.' Virtual'; ?></td>
                <td class="width10"><?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?></td>
                <td class="width20"><?php echo $lingua['MNTH'].' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?></td>
                <td class="width5 transparent"></td>
                <td class="width5 transparent"></td>
            </tr>
            <?php while ($linha_dados = $query_calend_tasks->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $linha_dados['nome']; ?>
                    <td><?php echo $linha_dados['dia_v_ini']; ?>
                    <td><?php echo conv_mes($linha_dados['mes_v_ini']); ?></td>
                    <td><?php echo $linha_dados['dia_v_fim']; ?>
                    <td><?php echo conv_mes($linha_dados['mes_v_fim']); ?></td>
                    <td class="width5 iconwrapper">
                        <input id="hddIdCal" name="hddIdCal" type="hidden" value="<?php echo $linha_dados['id']; ?>">
                        <div id="divImgVerTaskCal_<?php echo $linha_dados['id']; ?>" name="divImgVerTaskCal" class="novolabelicon icon-info"></div>
                    </td>
                    <td class="width5 iconwrapper">
                        <input id="hddIdCal" name="hddIdCal" type="hidden" value="<?php echo $linha_dados['id']; ?>">
                        <div id="divImgRemTaskData_<?php echo $linha_dados['id']; ?>" name="divImgRemTaskData" class="novolabelicon icon-garbage rem_linha"></div>
                    </td>
                </tr>
            <?php } ?>
        </table>
		
        <table id="tblCalendTasksGeralVazia" name="tblCalendTasksGeralVazia" class="tabela left">
            <tr>
                <td><?php echo $lingua['TBL_VAZIA']; ?></td>
            </tr>
        </table>
    </div>
    <div class="linha left">
        <div id="frmDetalhesCalTask" name="frmDetalhesCalTask" class="width50 left">
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcGrupoEditCal" class="labelNormal left"><?php echo $lingua['GRP']; ?></label>
                    <input id="hddIdCal" name="hddIdCal" type="hidden">
                </div>
                <div class="width60 left">
                    <?php
                    $grupo12 = carregar_grupo($connection, "admin", $_SESSION['id_utilizador']);
                    ?>
                    <select id="slcGrupoEditCal" name="slcGrupoEditCal" class="chosenSelect">
                        <?php while ($linha = $grupo12->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?php echo $linha['id_grupo']; ?>"><?php echo $linha['nome']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="txtDescTasks" class="labelNormal left">Descrição</label>
                </div>
                <div class="width60 left">
                    <!-- <div class="inputarea left width100">
                        <input id="txtTaskDesc" name="txtTaskDesc" type="text" class="editableText">
                    </div> -->
                    <textarea id="txtDescTasks" name="txtDescTasks" rows="5" class="caixaTextoNormal" style="width: 98%"></textarea>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcMesIniDefCalTask" class="labelNormal left"><?php echo $lingua['MNTH'].' '.$lingua['INI'].' '.' Virtual'; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcMesIniDefCalTask" name="slcMesIniDefCalTask" class="chosenSelect" data-placeholder="<?php echo $lingua['MNTH'].' '.$lingua['INI']; ?>">
                        <option value="0" selected="selected"></option>
                        <option value="1"><?php echo $lingua['JAN']; ?></option>
                        <option value="2"><?php echo $lingua['FEV']; ?></option>
                        <option value="3"><?php echo $lingua['MAR']; ?></option>
                        <option value="4"><?php echo $lingua['ABR']; ?></option>
                        <option value="5"><?php echo $lingua['MAI']; ?></option>
                        <option value="6"><?php echo $lingua['JUN']; ?></option>
                        <option value="7"><?php echo $lingua['JUL']; ?></option>
                        <option value="8"><?php echo $lingua['AUG']; ?></option>
                        <option value="9"><?php echo $lingua['SEP']; ?></option>
                        <option value="10"><?php echo $lingua['OCT']; ?></option>
                        <option value="11"><?php echo $lingua['NOV']; ?></option>
                        <option value="12"><?php echo $lingua['DEC']; ?></option>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcDiaIniDefCalTask" class="labelNormal left"><?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['INI']).' '.' Virtual'; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcDiaIniDefCalTask" name="slcDiaIniDefCalTask" class="chosenSelect" data-placeholder="<?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['INI']); ?>">
                        <option value="0" selected="selected"></option>
                        <?php for($k=1; $k<=31; $k++) { ?>
                            <option value="<?php echo $k ?>"><?php echo $k ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcMesFimDefCalTask" class="labelNormal left"><?php echo $lingua['MNTH'].' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcMesFimDefCalTask" name="slcMesFimDefCalTask" class="chosenSelect" data-placeholder="<?php echo $lingua['MNTH'].' '.lcfirst($lingua['FIN']); ?>">
                        <option value="0" selected="selected"></option>
                        <option value="1"><?php echo $lingua['JAN']; ?></option>
                        <option value="2"><?php echo $lingua['FEV']; ?></option>
                        <option value="3"><?php echo $lingua['MAR']; ?></option>
                        <option value="4"><?php echo $lingua['ABR']; ?></option>
                        <option value="5"><?php echo $lingua['MAI']; ?></option>
                        <option value="6"><?php echo $lingua['JUN']; ?></option>
                        <option value="7"><?php echo $lingua['JUL']; ?></option>
                        <option value="8"><?php echo $lingua['AUG']; ?></option>
                        <option value="9"><?php echo $lingua['SEP']; ?></option>
                        <option value="10"><?php echo $lingua['OCT']; ?></option>
                        <option value="11"><?php echo $lingua['NOV']; ?></option>
                        <option value="12"><?php echo $lingua['DEC']; ?></option>
                    </select>
                </div>
            </div>
            <div class="linha10 left">
                <div class="width40 left">
                    <label for="slcDiaFimDefCalTask" class="labelNormal left"><?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['FIN']).' '.' Virtual'; ?></label>
                </div>
                <div class="width60 left">
                    <select id="slcDiaFimDefCalTask" name="slcDiaFimDefCalTask" class="chosenSelect" data-placeholder="<?php echo ucfirst($lingua['DAY']).' '.lcfirst($lingua['FIN']); ?>">
                        <option value="0" selected="selected"></option>
                        <?php for($k=1; $k<=31; $k++) { ?>
                            <option value="<?php echo $k ?>"><?php echo $k ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="linha">&nbsp;</div>
        
            <div class="linha left textoCentro">
                <button id="btnUpdateTaskData" name="btnUpdateTaskData" class="botao"><?php echo $lingua['SAVE']; ?></button>
                <button id="btnVoltarTaskCal" name="btnVoltarTaskCal" class="botao"><?php echo $lingua['BCK']; ?></button>
            </div>
        </div>
    </div>
</div>