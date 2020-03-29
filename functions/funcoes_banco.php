<?php

/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-01 17:02:55
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-15 19:40:12
 */

include('../conf/check_pastas.php');
include_once('functions.php');

use phpFastCache\CacheManager;
include_once('../phpfastcache/src/autoload.php');
CacheManager::setDefaultConfig([
  'path' => '/tmp',
  'securityKey' => 'SimEmp'
]);

//$pwd_se_acoes = 'T4h6m3YuniurhCDfHGE9VYBQmQMszt8x';
$pwd_se_acoes = '';

$query_moeda = $connection->prepare("SELECT mo.ISO4217, mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

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

$idioma == 'pt' ? $desc_mov = 'descricao' : $desc_mov = 'description';
/* */

/* */
if ($_POST['tipo'] == "carrega_cotacao") {
    $cache = CacheManager::getInstance('files');
	
	$nome_bolsa = $_POST['nome_bolsa'];
    $linhas = $_POST['linhas'];
    $i=0;
    
	$arr_dados = array();
	if ($linhas > 2) {
		$key = 'cotacao_acoes';
        $CachedString = $cache->getItem($key);
        $dados_cache = $CachedString->get();
		
		if ($dados_cache && $dados_cache !== null) {
            foreach ($dados_cache as $value) {
                if ($value['nome_bolsa'] == $nome_bolsa) {
                    $arr_dados[] = array('nome_acao' => $value['nome_acao'], 'nome_empresa' => $value['nome_empresa'], 'last_trade_price' => $value['last_trade_price'], 'change' => $value['change'], 'open' => $value['open'], 'days_high' => $value['days_high'], 'days_low' => $value['days_low']);
                    $i++;
                }
            }
        }

        if ($i > 0) {
            $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true);
        }
	
	} else {
		$connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
		$connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		// $query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE b.nome=:bolsa ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT DISTINCT c.id_cotacao, c.id_acao, c.last_trade_price, c.`change`, c.`open`, c.days_high, c.days_low, c.last_trade_date FROM cotacao c WHERE c.last_trade_date=CURDATE() OR DATEDIFF(CURDATE(), DATE(c.date_reg))<2 ORDER BY c.id_acao ASC) AS cotacoes ON acoes.id_acao=cotacoes.id_acao ORDER BY acoes.nome_acao ASC");
//		$query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE a.active='1' AND b.nome=:bolsa ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT c.id_acao, c.last_trade_price, c.`change`, c.`open`, c.days_high, c.days_low, c.last_trade_date FROM cotacao c ORDER BY c.id_acao ASC, c.date_reg DESC) AS cotacoes ON acoes.id_acao=cotacoes.id_acao GROUP BY cotacoes.id_acao ASC ORDER BY acoes.nome_empresa ASC");//estava esta
//                $query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE a.active='1' AND b.nome=:bolsa ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT last_cotacao.id_acao, last_cotacao.last_trade_price, last_cotacao.`change`, last_cotacao.`open`, last_cotacao.days_high, last_cotacao.days_low,last_cotacao.last_trade_date,last_cotacao.date_reg FROM cotacao last_cotacao JOIN  (SELECT id_acao, MAX(date_reg) AS max_date FROM cotacao GROUP BY id_acao )  c1 ON c1.id_acao=last_cotacao.id_acao AND c1.max_date=last_cotacao.date_reg) AS cotacoes ON acoes.id_acao=cotacoes.id_acao GROUP BY cotacoes.id_acao ASC ORDER BY acoes.nome_empresa ASC");//meti esta (removida por causa de mariadb para mysql)
                $query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE a.active='1' AND b.nome=:bolsa ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT last_cotacao.id_acao, last_cotacao.last_trade_price, last_cotacao.`change`, last_cotacao.`open`, last_cotacao.days_high, last_cotacao.days_low,last_cotacao.last_trade_date,last_cotacao.date_reg FROM cotacao last_cotacao JOIN  (SELECT id_acao, MAX(date_reg) AS max_date FROM cotacao GROUP BY id_acao )  c1 ON c1.id_acao=last_cotacao.id_acao AND c1.max_date=last_cotacao.date_reg) AS cotacoes ON acoes.id_acao=cotacoes.id_acao GROUP BY cotacoes.id_acao /*ASC*/ ORDER BY acoes.nome_empresa ASC");//(meti esta comentado o ASC https://dev.mysql.com/worklog/task/?id=8693)
		$query_cotacao->execute(array(':bolsa' => $nome_bolsa));
		$rows = $query_cotacao->rowCount();
		
		if ($rows > 0) {
			while ($linha_cotacao = $query_cotacao->fetch(PDO::FETCH_ASSOC)) {
//                            if($linha_cotacao['nome_acao']=='AC.PA'){//para testes
				$arr_dados[] = array('nome_acao' => $linha_cotacao['nome_acao'], 'nome_empresa' => $linha_cotacao['nome_empresa'], 'last_trade_price' => $linha_cotacao['last_trade_price'], 'change' => $linha_cotacao['change'], 'open' => $linha_cotacao['open'], 'days_high' => $linha_cotacao['days_high'], 'days_low' => $linha_cotacao['days_low']);
//                                }//para testes
			}
			$arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
		}
		else {
			$arr = array('sucesso' => true, 'vazio' => true);
		}
	}
} elseif ($_POST['tipo'] == "ganhoPerda") {
    /* FUNCIONAL (versão anterior)
	$nome_acao = $_POST['nome_acao'];
	$cache = phpFastCache();
	$dados_cache = $cache->get('cotacao_acoes');
	
	foreach ($dados_cache as $value) {
        if ($value['nome_acao'] == $nome_acao) {
            $arr = array('sucesso' => true, 'vazio' => false, 'LastTradePriceOnly' => $value['last_trade_price'], 'DaysHigh' => $value['days_high'], 'DaysLow' => $value['days_low'], 'Volume' => $value['volume']);
			break;
        } else {
            $arr = array('sucesso' => true, 'vazio' => true);
		}
	}
	/* */
	
	/* RECURSO
	$connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
	$connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$query_cotacao = $connection_bd_acao->prepare("SELECT c.last_trade_price FROM acao a INNER JOIN cotacao c ON a.id_acao=c.id_acao WHERE a.nome_acao=:nome_acao ORDER BY c.date_reg DESC LIMIT 1;");
	$query_cotacao->execute(array(':nome_acao' => $nome_acao));
	$linha_cotacao = $query_cotacao->fetch(PDO::FETCH_ASSOC);
	$arr = array('sucesso' => true, 'vazio' => false, 'LastTradePriceOnly' => $linha_cotacao['last_trade_price']);
	/* */
	
	/* FUNCIONAL (última versão) */
	$nome_acao = $_POST['nome_acao'];
	
	$cache = CacheManager::getInstance('files');
    $key = 'cotacao_acoes';
    $CachedString = $cache->getItem($key);
    $dados_cache = $CachedString->get();
	
	$active = false;
	if ($dados_cache && $dados_cache !== null) {
		foreach ($dados_cache as $value) {
			if ($value['nome_acao'] == $nome_acao) {
				$active = true;
				$arr = array('sucesso' => true, 'vazio' => false, 'LastTradePriceOnly' => $value['last_trade_price'], 'DaysHigh' => $value['days_high'], 'DaysLow' => $value['days_low'], 'Volume' => $value['volume']);
				break;
			}
		}
	}
	
	/* */
	if ($active == false) {
		$key1 = 'cotacao_acoes_inactive';
        $CachedString1 = $cache->getItem($key1);
        $dados_cache_inactive = $CachedString1->get();
		
		if ($dados_cache_inactive == null || $dados_cache_inactive == "") {
			$connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
			$connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$query_last_price = $connection_bd_acao->prepare("SELECT c.last_trade_price, c.days_high, c.days_low, c.volume FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao WHERE a.nome_acao=:nome_acao ORDER BY c.date_reg DESC LIMIT 1");
			$query_last_price->execute(array(':nome_acao' => $nome_acao));
			$linha_last_price = $query_last_price->fetch(PDO::FETCH_ASSOC);
			$cotacao_inactive[$nome_acao] = array('last_trade_price' => $linha_last_price['last_trade_price'], 'days_high' => $linha_last_price['days_high'], 'days_low' => $linha_last_price['days_low'], 'volume' => $linha_last_price['volume']);
				
			$CachedString1->set($cotacao_inactive)->expiresAfter(600);
			$cache->save($CachedString1);
			
			$arr = array('sucesso' => true, 'vazio' => false, 'LastTradePriceOnly' => $linha_last_price['last_trade_price'], 'DaysHigh' => $linha_last_price['days_high'], 'DaysLow' => $linha_last_price['days_low'], 'Volume' => $linha_last_price['volume']);
		}
		else {
			foreach ($dados_cache_inactive as $key => $value) {
				if ($key == $nome_acao) {
					$found = true;
					$arr = array('sucesso' => true, 'vazio' => false, 'LastTradePriceOnly' => $value['last_trade_price'], 'DaysHigh' => $value['days_high'], 'DaysLow' => $value['days_low'], 'Volume' => $value['volume']);
					break;
				}
				else {
					$found = false;
				}
			}
			
			if ($found == false) {
				$connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
				$connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$query_last_price = $connection_bd_acao->prepare("SELECT c.last_trade_price, c.days_high, c.days_low, c.volume FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao WHERE a.nome_acao=:nome_acao ORDER BY c.date_reg DESC LIMIT 1");
				$query_last_price->execute(array(':nome_acao' => $nome_acao));
				$linha_last_price = $query_last_price->fetch(PDO::FETCH_ASSOC);
				$cotacao_inactive[$nome_acao] = array('last_trade_price' => $linha_last_price['last_trade_price'], 'days_high' => $linha_last_price['days_high'], 'days_low' => $linha_last_price['days_low'], 'volume' => $linha_last_price['volume']);
					
				// $updated_archive = array_merge((array)$cache_cotacao_inactive, (array)$cotacao_inactive);
				$updated_archive = array_merge((array)$dados_cache_inactive, (array)$cotacao_inactive);
				$CachedString1->set($updated_archive)->expiresAfter(3600);
				$cache->save($CachedString1);
					
				$arr = array('sucesso' => true, 'vazio' => false, 'LastTradePriceOnly' => $linha_last_price['last_trade_price'], 'DaysHigh' => $linha_last_price['days_high'], 'DaysLow' => $linha_last_price['days_low'], 'Volume' => $linha_last_price['volume']);
			}
		}
	}
	/* */
}
/* */
elseif ($_POST['tipo'] == "acao_taxas") {
    $subtotal = $_POST['subtotal'];
	$trans = $_POST['trans'];
    
    $query_moeda_acao = $connection->prepare("SELECT p.nome_pais, m.simbolo FROM pais p INNER JOIN moeda m ON p.id_moeda=m.id WHERE p.id_pais=:id_pais LIMIT 1");
    $query_moeda_acao->execute(array(':id_pais' => $_POST['id_pais']));
    $linha_moeda_acao = $query_moeda_acao->fetch(PDO::FETCH_ASSOC);
    
    $query_encargo_min = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
    $query_encargo_min->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Encargos minimos do investidor ('. $linha_moeda_acao['nome_pais']. ')'));
    $linha_encargo_min = $query_encargo_min->fetch(PDO::FETCH_ASSOC);

    $query_encargo = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
    $query_encargo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Encargos do investidor ('. $linha_moeda_acao['nome_pais']. ')'));
    $linha_encargo = $query_encargo->fetch(PDO::FETCH_ASSOC);
    
    $query_imposto_selo = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
    $query_imposto_selo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Imposto de selo'));
    $linha_imposto_selo = $query_imposto_selo->fetch(PDO::FETCH_ASSOC);
    
    $is = $linha_imposto_selo['valor'];

    /*
    if ($subtotal < $linha_encargo_min['valor']) {
        $encargo = $linha_encargo_min['valor'];
    } else {
        $encargo = ($linha_encargo['valor'] / 100) * $subtotal;
    }
    */
    $encargo = ($linha_encargo['valor'] / 100) * $subtotal;
    if ($encargo < $linha_encargo_min['valor']) {
        $encargo = $linha_encargo_min['valor'];
    }
	
	if ($trans == 'compra') {
		$total = $subtotal + (($encargo * $is) / 100) + $encargo;
	} elseif ($trans == 'venda') {
		$total = $subtotal - (($encargo * $is) / 100) - $encargo;
	}
	
    $arr = array('sucesso' => true, 'total' => $total, 'is' => $is, 'encargo' => $encargo, 'simbolo' => $linha_moeda_acao['simbolo']);

    // logClicks($connection, "177");
    
} elseif ($_POST['tipo'] == "comprar_acoes") {
    $nome = $_POST["txtNome"];
    $id_pais = $_POST["id_pais"];
    $preco_ini = floatval($_POST["txtPreco"]);
    $data = date('Y-m-d H:i:s', strtotime($_POST["txtDataCompleta"]));
    $quantidade = intval($_POST["txtQuantidade"]);
    // $subtotal_ini = floatval($_POST["txtSubtotal"]);
    $subtotal_ini = round($preco_ini * $quantidade, 2);
    $encargo_ini = round(floatval($_POST["hddEncargo"]), 2);
    $is_ini = round(floatval($_POST["hddIS"]) / 100 * $encargo_ini, 2);
    // $total_ini = floatval($_POST["txtTotal"]);
    $total_ini = $subtotal_ini + $encargo_ini + $is_ini;
    
    if ($_POST["imediato"] == 'true') {
        $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
        $count_conta = $query_conta->rowCount();
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);

        $query_ISO_moeda = $connection->prepare("SELECT m.ISO4217 FROM moeda m INNER JOIN pais p ON p.id_moeda=m.id WHERE p.id_pais=:id_pais LIMIT 1");
        $query_ISO_moeda->execute(array(':id_pais' => $id_pais));
        $linha_ISO_moeda = $query_ISO_moeda->fetch(PDO::FETCH_ASSOC);

        if ($linha_ISO_moeda['ISO4217'] != $linha_moeda['ISO4217']) {
			$contents = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
			foreach($contents->Cube->Cube->Cube as $rates) {
				if ($rates['currency'] == $linha_ISO_moeda['ISO4217']) {
					$xchange_rate = round(1 / floatval($rates['rate']), 2);
					break;
				}
			}
		} else
			$xchange_rate = 1;
		
        /*
        $subtotal = number_format($subtotal_ini * $xchange_rate, 2, '.', '');
        $total = number_format($total_ini * $xchange_rate, 2, '.', '');
        $encargo = number_format($encargo_ini * $xchange_rate, 2, '.', '');
        $is = number_format($is_ini * $xchange_rate, 2, '.', '');
        */
        $subtotal = round($subtotal_ini * $xchange_rate, 2);
        $total = round($total_ini * $xchange_rate, 2);
        $encargo = round($encargo_ini * $xchange_rate, 2);
        $is = round($is_ini * $xchange_rate, 2);
	
		if ($xchange_rate != null && $xchange_rate > 0 && $subtotal_ini > 0 && $subtotal > 0 && $total > 0) {
			if ($total < $linha_conta['saldo_controlo']) {
				$query_idAcao = $connection->prepare("SELECT id FROM acao a WHERE a.nome=:nome AND a.id_pais=:id_pais");
				$query_idAcao->execute(array(':nome' => $nome, ':id_pais' => $id_pais));
				$linha_idAcao = $query_idAcao->fetch(PDO::FETCH_ASSOC);
				$count_idAcao = $query_idAcao->rowCount();

				if ($count_idAcao == 1) {
					$query_acao = $connection->prepare("INSERT INTO acao_trans (id_acao, id_empresa, data, preco, quantidade, subtotal, tipo) VALUES (:id_acao, :id_empresa, :data, :preco, :quantidade, :subtotal, :tipo)");
					$query_acao->execute(array(':id_acao' => $linha_idAcao['id'], ':id_empresa' => $_SESSION['id_empresa'], ':data' => $data, ':preco' => $preco_ini, ':quantidade' => $quantidade, ':subtotal' => $subtotal_ini, ':tipo' => 'C'));
					$query_num = $query_acao->rowCount();
				} else {
					$query_insert_acao = $connection->prepare("INSERT INTO acao (nome, id_pais) VALUES (:nome, :id_pais)");
					$query_insert_acao->execute(array(':nome' => $nome, ':id_pais' => $id_pais));
					$id_acao = $connection->lastInsertId();
					
					$query_acao = $connection->prepare("INSERT INTO acao_trans (id_acao, id_empresa, data, preco, quantidade, subtotal, tipo) VALUES (:id_acao, :id_empresa, :data, :preco, :quantidade, :subtotal, :tipo)");
					$query_acao->execute(array(':id_acao' => $id_acao, ':id_empresa' => $_SESSION['id_empresa'], ':data' => $data, ':preco' => $preco_ini, ':quantidade' => $quantidade, ':subtotal' => $subtotal_ini, ':tipo' => 'C'));
					$query_num = $query_acao->rowCount();
				}

				if ($query_num == 1) {
					if ($count_conta == 1) {
						try {
							$connection->beginTransaction();
							
							/*
							$saldo_controlo = $linha_conta["saldo_controlo"] - $subtotal;
							$debito_conta = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
							$debito_conta->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "TIT", ':descricao' => "Compra de $quantidade ações do/a $nome", ':debito' => $subtotal, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
							*/
							atualiza_saldo($connection, $linha_conta["id"], "TIT", "Compra de $quantidade ações do/a $nome", "Purchase of $quantidade shares from $nome", $subtotal, 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $subtotal);
							
							/*
							$saldo_control = $linha_conta["saldo_controlo"] - $subtotal - $encargo;
							$query_encargo = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
							$query_encargo->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "Encargo por compra de $quantidade ações do/a $nome", ':debito' => $encargo, ':saldo_controlo' => $saldo_control, ':saldo_contab' => $saldo_control, ':saldo_disp' => $saldo_control));
							*/
							atualiza_saldo($connection, $linha_conta["id"], "TAX", "Encargo por compra de $quantidade ações do/a $nome", "Charges from purchase of $quantidade shares from $nome", $encargo, 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $subtotal + $encargo);
							
							/*
							$saldo_controlos = $linha_conta["saldo_controlo"] - $subtotal - $encargo - $is;
							$query_is = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
							$query_is->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "IS por compra de $quantidade ações do/a $nome", ':debito' => $is, ':saldo_controlo' => $saldo_controlos, ':saldo_contab' => $saldo_controlos, ':saldo_disp' => $saldo_controlos));
							*/
							atualiza_saldo($connection, $linha_conta["id"], "TAX", "IS por compra de $quantidade ações do/a $nome", "Stamp duty from purchase of $quantidade shares from $nome", $is, 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $subtotal + $encargo + $is);

							$connection->commit();

							$query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
							$query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
							$linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);

							$arr = array('sucesso' => true, 'saldo' => $linha_conta['saldo_controlo'], 'moeda' => $linha_moeda['simbolo']);
						} catch (PDOException $e) {
							$connection->rollBack();
							$arr = array('sucesso' => false, 'mensagem' => $e->getMessage());
						}
					} else {
						$arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na compra das ações");
					}
				} else {
					$arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na compra das ações");
				}
			} else {
				$arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
			}
		} else {
			$arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas ocorreu um erro externo. Por favor, contacte o administrador");
		}
        
        logClicks($connection, "178");
    }
    else {
        $data_recebida = date("Y-m-d H:i:s", strtotime($data));
        $mes = date('m', strtotime($data_recebida));
        $ano = date('Y', strtotime($data_recebida));
        $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
        $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));

        $query_data = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano AND CURDATE() <= cal.data_fim ORDER BY cal.data_fim DESC LIMIT 1");
        $query_data->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
        $calendario = $query_data->rowCount();
        if ($calendario > 0){
            $linha_data = $query_data->fetch(PDO::FETCH_ASSOC);
            $data_inicio = $linha_data['data_inicio'] . " " . $linha_data['hora_inicio'];
            $data_fim = $linha_data['data_fim'] . " " . $linha_data['hora_fim'];
            $data_acao = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data);
        } else
            $data_acao = $data;
            
        /* Ligar à segunda BD */
        try {
            $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
            $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $query_idAcao = $connection_bd_acao->prepare("SELECT a.id_acao FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais WHERE a.nome_acao=:nome AND p.id_pais=:id_pais LIMIT 1");
            $query_idAcao->execute(array(':nome' => $nome, ':id_pais' => $id_pais));
            $count_idAcao = $query_idAcao->rowCount();
            
            if ($count_idAcao > 0) {
                $linha_idAcao = $query_idAcao->fetch(PDO::FETCH_ASSOC);
                $query_acao = $connection_bd_acao->prepare("INSERT INTO preco_alvo_empresa (id_acao, id_empresa, qtd, preco_alvo, `is`, encargos, tipo, active, data_limite_virtual, data_limite_real) VALUES (:id_acao, :id_empresa, :qtd, :preco_alvo, :is, :encargos, 'C', '1', :data_limite_virtual, :data_limite_real)");
                $query_acao->execute(array(':id_acao' => $linha_idAcao['id_acao'], ':id_empresa' => $_SESSION['id_empresa'], ':qtd' => $quantidade, ':preco_alvo' => $preco_ini, ':is' => $is_ini, ':encargos' => $encargo_ini, ':data_limite_virtual' => $data_recebida, ':data_limite_real' => $data_acao));
            }
            
            $connection_bd_acao = null;
            
            $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
            $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
            $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
            
            $arr = array('sucesso' => true, 'saldo' => $linha_conta['saldo_controlo'], 'moeda' => $linha_moeda['simbolo']);
            
        } catch (PDOException $e) {
            echo $e->getMessage();
            file_put_contents('PDOErrorsAcao.txt', $e->getMessage(), FILE_APPEND);
            $arr = array('sucesso' => false, 'mensagem' => 'Lamentamos, mas algo correu mal no agendamento da compra');
        }
		logClicks($connection, "200");
    }
    
}
elseif ($_POST['tipo'] == "acoes_especifico") {
    $query_titulos = $connection->prepare("SELECT p.id, a.nome, p.preco, c.id_pais, m.simbolo, date_format(p.`data`,'%m/%d/%Y') AS `data`, COALESCE(p.quantidade-SUM(f.quantidade), p.quantidade) AS quantidade, p.preco*COALESCE(p.quantidade-SUM(f.quantidade), p.quantidade) AS total FROM acao_trans p LEFT JOIN acao_trans f ON p.id=f.parent INNER JOIN acao a ON p.id_acao=a.id INNER JOIN pais c ON a.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id WHERE p.parent IS NULL AND p.id_acao=:id_acao AND p.id_empresa=:id_empresa GROUP BY p.id HAVING quantidade<>0");
    $query_titulos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_acao' => $_POST['id_acao']));
    $num_titulos = $query_titulos->rowCount();

    if ($num_titulos > 0) {
        while ($linha_titulos = $query_titulos->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_titulos['id'], 'nome' => $linha_titulos['nome'], 'preco' => $linha_titulos['preco'], 'id_pais' => $linha_titulos['id_pais'], 'simbolo' => $linha_titulos['simbolo'], 'data' => $linha_titulos['data'], 'quantidade' => $linha_titulos['quantidade'], 'total' => $linha_titulos['total']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Não possui ações na sua carteira de títulos");
    }
    logClicks($connection, "180");
    
}
elseif ($_POST['tipo'] == "acoes_vender") {
    $id_pais = $_POST["id_pais"];
    $id_acao = $_POST['id'];
    $nome = $_POST['nome'];
    $data = date('Y-m-d H:i:s', strtotime($_POST['data']));
    $qtd = intval($_POST['quantidade']);
    $preco = floatval($_POST['preco']);
    $encargo_ini = round(floatval(str_replace(",", ".", $_POST["hddEncargo"])), 2);
    $is_ini = round(floatval(str_replace(",", ".", $_POST["hddIS"])) / 100 * $encargo_ini, 2);
    // $total_ini = floatval($_POST["total"]);
    // $total_ini = $preco * $qtd - $encargo_ini - $is_ini;
	$total_ini = $preco * $qtd; // Valor exibido "debito ou crédito", do extrato apresentado ao utilizador

    if ($_POST["imediato"] == 'true') {
        /* Verificar se AUMENTO de cotação NÃO foi ANORMAL */
        //-- Carregar última cotação guardada em BD
        $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
        $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query_last_price = $connection_bd_acao->prepare("SELECT c.last_trade_price FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais WHERE a.nome_acao=:nome AND p.id_pais=:id_pais ORDER BY c.date_reg DESC LIMIT 1");
        $query_last_price->execute(array(':nome' => $nome, ':id_pais' => $id_pais));
        $linha_last_price = $query_last_price->fetch(PDO::FETCH_ASSOC);
        $last_price = $linha_last_price['last_trade_price'];
        
        //-- Verificar se variação, em relação a utimo preço, é maior q 10%
        if ($preco - $last_price < $last_price * 10/100) {
            $query_select = $connection->prepare("SELECT preco, quantidade, subtotal FROM acao_trans WHERE id_empresa=:id_empresa AND id=:id_acao_trans AND tipo=:tipo");
            $query_select->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_acao_trans' => $id_acao, ':tipo' => "C"));
            $linha_select = $query_select->fetch(PDO::FETCH_ASSOC);

            $query_ISO_moeda = $connection->prepare("SELECT m.ISO4217 FROM moeda m INNER JOIN pais p ON p.id_moeda=m.id WHERE p.id_pais=:id_pais LIMIT 1");
            $query_ISO_moeda->execute(array(':id_pais' => $id_pais));
            $linha_ISO_moeda = $query_ISO_moeda->fetch(PDO::FETCH_ASSOC);

            if ($linha_ISO_moeda['ISO4217'] != $linha_moeda['ISO4217']) {
                $contents = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
                foreach ($contents->Cube->Cube->Cube as $rates) {
                    if ($rates['currency'] == $linha_ISO_moeda['ISO4217']) {
                        $xchange_rate = 1 / floatval($rates['rate']);
                        break;
                    }
                }
            } else
                $xchange_rate = 1;

            $data = date("Y-m-d H:i:s", strtotime($data));
            $quantidade = $linha_select['quantidade'] - $qtd;

            $subtotal_ini = $preco * $qtd;
            $subtotal = number_format($subtotal_ini * $xchange_rate, 2, '.', '');
            $total = number_format($total_ini * $xchange_rate, 2, '.', '');
            $encargo = number_format($encargo_ini * $xchange_rate, 2, '.', '');
            $is = number_format($is_ini * $xchange_rate, 2, '.', '');

            if ($xchange_rate != null && $xchange_rate > 0 && $subtotal_ini > 0 && $subtotal > 0 && $total > 0 && $encargo > 0 && $is > 0) {
                // if ($total_ini == $subtotal_ini) {
                if ($total_ini - $subtotal_ini <= 0.1) {
                    $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
                    $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
                    $count_conta = $query_conta->rowCount();
                    $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
                    // if (($encargo + $is) < $linha_conta["saldo_controlo"]) {
                    if (($encargo + $is) < $subtotal) {
                        if ($quantidade >= 0) {
                            $query_select_acao = $connection->prepare("SELECT id_acao FROM acao_trans WHERE id=:id");
                            $query_select_acao->execute(array(':id' => $id_acao));
                            $linha_select_acao = $query_select_acao->fetch(PDO::FETCH_ASSOC);
                            $query_insert = $connection->prepare("INSERT INTO acao_trans (id_acao, id_empresa, data, preco, quantidade, subtotal, tipo, parent) VALUES (:id_acao, :id_empresa, :data, :preco, :quantidade, :subtotal, :tipo, :parent)");
                            $query_insert->execute(array('id_acao' => $linha_select_acao['id_acao'], ':id_empresa' => $_SESSION['id_empresa'], ':data' => $data, ':preco' => $preco, ':quantidade' => $qtd, ':subtotal' => $subtotal_ini, ':tipo' => 'V', ':parent' => $id_acao));
                            if ($count_conta == 1) {
                                try {
                                    $connection->beginTransaction();

                                    /*
                                      $saldo_controlo = $linha_conta["saldo_controlo"] + $subtotal;
                                      $debito_conta = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :credito, :saldo_controlo, :saldo_contab, :saldo_disp)");
                                      $debito_conta->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "TIT", ':descricao' => "Venda de $quant ações do/a $nome", ':credito' => $_POST['total'], ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
                                     */
                                    atualiza_saldo($connection, $linha_conta["id"], "TIT", "Venda de $qtd ações do/a $nome", "Sale of $qtd shares from $nome", 0, $total, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $subtotal);

                                    /*
                                      $saldo = $linha_conta["saldo_controlo"] + $subtotal - $encargo;
                                      $query_encargo = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
                                      $query_encargo->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "Encargo por venda de $quant ações do/a $nome", ':debito' => $encargo, ':saldo_controlo' => $saldo, ':saldo_contab' => $saldo, ':saldo_disp' => $saldo));
                                     */
                                    atualiza_saldo($connection, $linha_conta["id"], "TAX", "Encargo por venda de $qtd ações do/a $nome", "Charges from sale of $qtd shares from $nome", $encargo, 0, $data, $linha_conta["saldo_controlo"] + $subtotal, $linha_conta["saldo_controlo"] + $subtotal, $linha_conta["saldo_controlo"] + $subtotal, $encargo);

                                    /*
                                      $query_is = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
                                      $query_encargo->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "IS por venda de $quant ações do/a $nome", ':debito' => $is, ':saldo_controlo' => $saldo_final, ':saldo_contab' => $saldo_final, ':saldo_disp' => $saldo_final));
                                     */
                                    $saldo_final = $linha_conta["saldo_controlo"] + $subtotal - $encargo - $is;
                                    atualiza_saldo($connection, $linha_conta["id"], "TAX", "IS por venda de $qtd ações do/a $nome", "Stamp duty from sale of $qtd shares from $nome", $is, 0, $data, $saldo_final, $saldo_final, $saldo_final, 0);

                                    $connection->commit();

                                    // $query_titulos = $connection->prepare("SELECT compras.id_pais, compras.nome_abrev AS abrev_pais, compras.nome_pais, compras.nome_bolsa, ac.id, ac.nome, IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade) AS total FROM (SELECT p.id_pais, p.nome_abrev, p.nome_pais, p.nome_bolsa, ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN pais p ON ac.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_c AND act.tipo=:tipo_c GROUP BY nome) AS compras LEFT JOIN (SELECT ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_v AND act.tipo=:tipo_v GROUP BY nome) AS vendas ON compras.id=vendas.id INNER JOIN acao ac ON ac.id=compras.id OR ac.id=vendas.id WHERE IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade)<>0 ORDER BY compras.id_pais ASC;");
                                    $query_titulos = $connection->prepare("SELECT compras.id_pais, compras.nome_abrev AS abrev_pais, compras.nome_pais, compras.nome_bolsa, ac.id, ac.nome, IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade) AS total FROM (SELECT p.id_pais, p.nome_abrev, p.nome_pais, p.nome_bolsa, ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN pais p ON ac.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_c AND act.tipo=:tipo_c GROUP BY id) AS compras LEFT JOIN (SELECT ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_v AND act.tipo=:tipo_v GROUP BY id) AS vendas ON compras.id=vendas.id INNER JOIN acao ac ON ac.id=compras.id OR ac.id=vendas.id WHERE IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade)<>0 ORDER BY compras.nome_pais ASC");
                                    $query_titulos->execute(array(':id_empresa_c' => $_SESSION['id_empresa'], ':tipo_c' => 'C', ':id_empresa_v' => $_SESSION['id_empresa'], ':tipo_v' => 'V'));
                                    $num_registos = $query_titulos->rowCount();
                                    if ($num_registos > 0) {
                                        while ($linha_titulos = $query_titulos->fetch(PDO::FETCH_ASSOC)) {
                                            $arr_dados[] = array('id_pais' => $linha_titulos['id_pais'], 'abrev_pais' => $linha_titulos['abrev_pais'], 'nome_pais' => $linha_titulos['nome_pais'], 'nome_bolsa' => $linha_titulos['nome_bolsa'], 'id_acao' => $linha_titulos['id'], 'nome' => $linha_titulos['nome'], 'total' => $linha_titulos['total']);
                                        }
                                        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'saldo' => $saldo_final, 'moeda' => $linha_moeda['simbolo']);
                                    } else {
                                        $arr = array('sucesso' => true, 'vazio' => true, 'saldo' => $saldo_final, 'moeda' => $linha_moeda['simbolo'], 'mensagem' => "Não possui ações na sua carteira de títulos");
                                    }
                                } catch (PDOException $ex) {
                                    $connection->rollBack();
                                    $arr = array('sucesso' => false, 'mensagem' => $ex->getMessage());
                                }
                            }
                        } else {
                            $arr = array('sucesso' => false, 'mensagem' => "A quantidade inserida supera a quantidade detida");
                        }
                    } else {
                        // $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
                        $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente para pagar as despesas da venda");
                    }
                } else {
                    $arr = array('sucesso' => false, 'mensagem' => "Por favor, preencha o formulário novamente");
                }
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas ocorreu um erro externo. Por favor, contacte o admin");
            }
            
        } else { //-- Variação foi ANORMAL
            $arr = array('sucesso' => false, 'mensagem' => "A variação da cotação foi anormal. Por favor, contacte o admin");
        }

        logClicks($connection, "181");
    }
    else {
        $qtd_comprada = $_POST['qtd_comprada'];
        
        $data_recebida = date("Y-m-d H:i:s", strtotime($data));
        $mes = date('m', strtotime($data_recebida));
        $ano = date('Y', strtotime($data_recebida));
        $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
        $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));

        $query_data = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano AND CURDATE() <= cal.data_fim ORDER BY cal.data_fim DESC LIMIT 1");
        $query_data->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
        $calendario = $query_data->rowCount();
        if ($calendario > 0){
            $linha_data = $query_data->fetch(PDO::FETCH_ASSOC);
            $data_inicio = $linha_data['data_inicio'] . " " . $linha_data['hora_inicio'];
            $data_fim = $linha_data['data_fim'] . " " . $linha_data['hora_fim'];
            $data_acao = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data);
        } else
            $data_acao = $data;

        /* Ligar à segunda BD */
        try {
            $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
            $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query_idAcao = $connection_bd_acao->prepare("SELECT a.id_acao FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais WHERE a.nome_acao=:nome AND p.id_pais=:id_pais LIMIT 1");
            $query_idAcao->execute(array(':nome' => $nome, ':id_pais' => $id_pais));
            $count_idAcao = $query_idAcao->rowCount();

            if ($count_idAcao > 0) {
                $linha_idAcao = $query_idAcao->fetch(PDO::FETCH_ASSOC);
                
                $query_verifica_qtds = $connection_bd_acao->prepare("SELECT SUM(pae.qtd) as qtd_agendada FROM preco_alvo_empresa pae WHERE pae.id_acao=:id_acao AND pae.id_empresa=:id_empresa AND pae.parent=:parent AND pae.active=1");
                $query_verifica_qtds->execute(array(':id_acao' => $linha_idAcao['id_acao'], ':id_empresa' => $_SESSION['id_empresa'], ':parent' => $id_acao));
                $linha_verifica_qtd = $query_verifica_qtds->fetch(PDO::FETCH_ASSOC);
                
                if ($qtd + $linha_verifica_qtd['qtd_agendada'] > $qtd_comprada) {
                   $arr = array('sucesso' => false, 'mensagem' => 'O seu total de agendamentos para essa compra excedem as quantidades compradas'); 
                }
                else {
                    $query_acao = $connection_bd_acao->prepare("INSERT INTO preco_alvo_empresa (id_acao, id_empresa, qtd, preco_alvo, `is`, encargos, tipo, parent, active, data_limite_virtual, data_limite_real) VALUES (:id_acao, :id_empresa, :qtd, :preco_alvo, :is, :encargos, 'V', :parent, '1', :data_limite_virtual, :data_limite_real)");
                    $query_acao->execute(array(':id_acao' => $linha_idAcao['id_acao'], ':id_empresa' => $_SESSION['id_empresa'], ':qtd' => $qtd, ':preco_alvo' => $preco, ':is' => $is_ini, ':encargos' => $encargo_ini, ':parent' => $id_acao, ':data_limite_virtual' => $data_recebida, ':data_limite_real' => $data_acao));
                    
                    $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
                    $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
                    $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);

                    $arr = array('sucesso' => true, 'saldo' => $linha_conta['saldo_controlo'], 'moeda' => $linha_moeda['simbolo']);
                }
            }

            $connection_bd_acao = null;
            
        } catch (PDOException $e) {
            echo $e->getMessage();
            file_put_contents('PDOErrorsAcao.txt', $e->getMessage(), FILE_APPEND);
            $arr = array('sucesso' => false, 'mensagem' => 'Algo correu mal no agendamento da venda, pedimos desculpas');
        }

        logClicks($connection, "201");
    }
    
} elseif ($_POST['tipo'] == "tipo_prestacao") {
    if ($_POST['id'] == 1) {
        $query_emprestimo = $connection->prepare("SELECT em.id, em.emprest, date_format(em.data_emprestimo,'%d-%m-%Y') AS data_emprestimo, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN emprestimo em ON p.id_emprestimo=em.id INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago");
        $query_emprestimo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
        $linhas = $query_emprestimo->rowCount();
        if ($linhas > 0) {
            while ($linha_emprestimo = $query_emprestimo->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha_emprestimo['id'], 'num' => $linha_emprestimo['emprest'], 'data' => $linha_emprestimo['data_emprestimo'], 'data_limite' => $linha_emprestimo['data_limit_pag'], 'prestacao' => $linha_emprestimo['valor']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem empréstimos");
        }
    } elseif ($_POST['id'] == 2) {
        $query_leasing = $connection->prepare("SELECT le.id_leasing, le.leas, date_format(le.data_leasing,'%d-%m-%Y') AS data_leasing, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN leasing le ON p.id_leasing=le.id_leasing INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago");
        $query_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
        $linhas = $query_leasing->rowCount();
        if ($linhas > 0) {
            while ($linha_leasing = $query_leasing->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha_leasing['id_leasing'], 'num' => $linha_leasing['leas'], 'data' => $linha_leasing['data_leasing'], 'data_limite' => $linha_leasing['data_limit_pag'], 'prestacao' => $linha_leasing['valor']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem leasings");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Escolha um tipo válido");
    }
    logClicks($connection, "150");
    
} elseif ($_POST['tipo'] == "pag_prest_emp") {
    $dados = json_decode($_POST['dados'], true);
    $data = date('Y-m-d H:i:s', strtotime($_POST["data"]));
    $valido = true;

    foreach ($dados as $key => $value) {
        $id_prestacao = $dados[$key]['id'];
        $query_select_emp = $connection->prepare("SELECT p.valor, em.n_per, em.emprest FROM pagamento p INNER JOIN emprestimo em ON p.id_emprestimo=em.id WHERE em.id=:id");
        $query_select_emp->execute(array(':id' => $id_prestacao));
        $linha_select_emp = $query_select_emp->fetch(PDO::FETCH_ASSOC);
        $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
        $saldo_controlo = $linha_conta["saldo_controlo"] - $linha_select_emp["valor"];
        if ($saldo_controlo > 0) {
            $query_up_emp_pag = $connection->prepare("UPDATE pagamento p INNER JOIN emprestimo em ON p.id_emprestimo=em.id SET p.pago=:pago_1, em.pago=:pago_2, p.data_pagamento=:data WHERE em.id=:id");
            $query_up_emp_pag->execute(array(':id' => $id_prestacao, ':pago_1' => '1', ':pago_2' => '1', 'data' => $data));
            
			/*
			$debito_conta = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $debito_conta->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "PAG", ':descricao' => "Pagamento da prestação nº $linha_select_emp[n_per] do empréstimo nº $linha_select_emp[emprest]", ':debito' => $linha_select_emp["valor"], ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
            */
			atualiza_saldo($connection, $linha_conta["id"], "PAG", "Pagamento (antecipado) da prestação nº $linha_select_emp[n_per] do empréstimo nº $linha_select_emp[emprest]", "", $linha_select_emp["valor"], 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_select_emp["valor"]);
            
        } else {
            $valido = false;
        }
    }

    if ($valido == true) {
        $query_emprestimo = $connection->prepare("SELECT em.id, em.emprest, date_format(em.data_emprestimo,'%d-%m-%Y') AS data_emprestimo, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN emprestimo em ON p.id_emprestimo=em.id INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago");
        $query_emprestimo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
        $linhas = $query_emprestimo->rowCount();
        if ($linhas > 0) {
            while ($linha_emprestimo = $query_emprestimo->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha_emprestimo['id'], 'num' => $linha_emprestimo['emprest'], 'data' => $linha_emprestimo['data_emprestimo'], 'data_limite' => $linha_emprestimo['data_limit_pag'], 'prestacao' => $linha_emprestimo['valor']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'saldo' => $saldo_controlo, 'tipo' => "emprestimo", 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem empréstimos", 'saldo' => $saldo_controlo, 'moeda' => $linha_moeda['simbolo']);
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
    }
    logClicks($connection, "149");
    
} elseif ($_POST['tipo'] == "pag_prest_lea") {
    $dados = json_decode($_POST['dados'], true);
    $data = date('Y-m-d H:i:s', strtotime($_POST["data"]));
    $valido = true;

    foreach ($dados as $key => $value) {
        $id_prestacao = $dados[$key]['id'];
        $query_select_lea = $connection->prepare("SELECT p.valor, le.leas, le.n_per FROM pagamento p INNER JOIN leasing le ON p.id_leasing=le.id_leasing WHERE le.id_leasing=:id");
        $query_select_lea->execute(array(':id' => $id_prestacao));
        $linha_select_lea = $query_select_lea->fetch(PDO::FETCH_ASSOC);
        $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
        $saldo_controlo = $linha_conta["saldo_controlo"] - $linha_select_lea["valor"];
        if ($saldo_controlo > 0) {
            $query_up_leasing = $connection->prepare("UPDATE leasing le INNER JOIN pagamento p ON le.id_leasing=p.id_leasing SET p.pago=:pago_1, le.pago=:pago_2, p.data_pagamento=:data WHERE le.id_leasing=:id");
            $query_up_leasing->execute(array(':id' => $id_prestacao, ':pago_1' => '1', ':pago_2' => '1', ':data' => $data));
            
			/*
			$debito_conta = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $debito_conta->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "PAG", ':descricao' => "Pagamento da prestação de leasing nº $linha_select_lea[n_per]", ':debito' => $linha_select_lea["valor"], ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
            */
			atualiza_saldo($connection, $linha_conta["id"], "PAG", "Pagamento (antecipado) da prestação nº $linha_select_lea[n_per] do leasing nº $linha_select_lea[leas]", "", $linha_select_lea["valor"], 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_select_lea["valor"]);
        } else {
            $valido = false;
        }
    }

    if ($valido == true) {
        $query_leasing = $connection->prepare("SELECT le.id_leasing, le.leas, date_format(le.data_leasing,'%d-%m-%Y') AS data_leasing, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN leasing le ON p.id_leasing=le.id_leasing INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago");
        $query_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
        $linhas = $query_leasing->rowCount();
        if ($linhas > 0) {
            while ($linha_leasing = $query_leasing->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha_leasing['id_leasing'], 'num' => $linha_leasing['leas'], 'data' => $linha_leasing['data_leasing'], 'data_limite' => $linha_leasing['data_limit_pag'], 'prestacao' => $linha_leasing['valor']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'saldo' => $saldo_controlo, 'tipo' => "leasing", 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem leasings", 'saldo' => $saldo_controlo, 'moeda' => $linha_moeda['simbolo']);
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
    }
    logClicks($connection, "149");
    
} elseif ($_POST['tipo'] == "tipo_diversos") {
    if ($_POST['id'] == 5) {
		$query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data_lim_pag,'%d-%m-%Y') AS data, dr.residentes, dr.total FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND dr.pago=:pago AND emp.id_empresa=:id_empresa");
		$query_dec_ret->execute(array(':pago' => '0', ':id_empresa' => $_SESSION['id_empresa']));
		$linhas = $query_dec_ret->rowCount();
		if ($linhas > 0) {
			while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
				if ($linha_dec_ret['residentes'] == "0") {
					$n_res = "Não";
				} else {
					$n_res = "Sim";
				}
				$arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'n_res' => $n_res, 'valor' => $linha_dec_ret['total']);
			}
			$arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo']);
		} else {
			$arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem declarações de retenções");
		}
		logClicks($connection, "154");
	
	} else {
		$query_entregas = $connection->prepare("SELECT en.id, te.designacao, date_format(en.data,'%d-%m-%Y') AS data, en.f_prazo, en.valor, en.mes, en.ano FROM entrega en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON en.id_tipo_entrega=te.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND en.pago=:pago AND te.id=:id");
		$query_entregas->execute(array(':id' => $_POST['id'], ':pago' => '0', ':id_empresa' => $_SESSION['id_empresa']));
		// $query_entregas->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => '0', ':id' => $_POST['id']));
		$linhas = $query_entregas->rowCount();

		if ($linhas > 0) {
			while ($linha_entregas = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
				if ($linha_entregas['f_prazo'] == "N") {
					$prazo = "Não";
				} else {
					$prazo = "Sim";
				}
				$arr_dados[] = array('id' => $linha_entregas['id'], 'tipo' => $linha_entregas['designacao'], 'data' => $linha_entregas['data'], 'f_prazo' => $prazo, 'valor' => $linha_entregas['valor'], 'moeda' => $linha_moeda['simbolo'], 'mes' => conv_mes($linha_entregas['mes']), 'ano' => $linha_entregas['ano'], 'moeda' => $linha_moeda['simbolo']);
			}
			$arr = array('sucesso' => true, 'vazio' => false, 'type' => 'diversos', 'dados_in' => $arr_dados);
		} else {
			$arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem entregas");
		}
		logClicks($connection, "152");
    }
	
} elseif ($_POST['tipo'] == "tipo_faturas") {
    $query_faturas = $connection->prepare("SELECT en.id, en.ref, p.nome_pais, p.nome_abrev AS abrev_pais, f.nome_abrev, date_format(en.data,'%d-%m-%Y') AS data, en.iva, en.total, m.simbolo FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND en.pago=:pago AND emp.id_empresa=:id_empresa ORDER BY en.`data` DESC, f.nome_abrev ASC");
    $query_faturas->execute(array(':pago' => '0', ':id_empresa' => $_SESSION['id_empresa']));
    $linhas = $query_faturas->rowCount();
    if ($linhas > 0) {
        while ($linha_faturas = $query_faturas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_faturas['id'], 'ref' => $linha_faturas['ref'], 'pais' => $linha_faturas['nome_pais'], 'abrev_pais' => $linha_faturas['abrev_pais'], 'fornecedor' => $linha_faturas['nome_abrev'], 'data' => $linha_faturas['data'], 'iva' => $linha_faturas['iva'], 'total' => $linha_faturas['total'], 'moeda' => $linha_faturas['simbolo']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'type' => 'fatura', 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem faturas");
    }
    logClicks($connection, "153");
    
} elseif ($_POST['tipo'] == "pag_entrega") {
    $dados = json_decode($_POST['dados'], true);
    $data = date('Y-m-d H:i:s', strtotime($_POST["data"]));
    $valido = true;

    foreach ($dados as $key => $value) {
        $id_entrega = $dados[$key]['id'];
        $id_tipo = $dados[$key]['id_tipo'];
        $query_select_entrega = $connection->prepare("SELECT te.designacao, p.valor FROM pagamento p INNER JOIN entrega e ON p.id_entrega=e.id INNER JOIN tipo_entrega te ON te.id=e.id_tipo_entrega WHERE e.id=:id");
        $query_select_entrega->execute(array(':id' => $id_entrega));
        $linha_select_entrega = $query_select_entrega->fetch(PDO::FETCH_ASSOC);

        $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);

        $saldo_controlo = $linha_conta["saldo_controlo"] - $linha_select_entrega["valor"];
        if ($saldo_controlo > 0) {
            $query_up_entrega = $connection->prepare("UPDATE entrega en INNER JOIN pagamento p ON en.id=p.id_entrega SET en.pago=:pago_1, p.pago=:pago_2, p.data_pagamento=:data WHERE en.id=:id");
            $query_up_entrega->execute(array(':pago_1' => '1', ':pago_2' => '1', ':data' => $data, ':id' => $id_entrega));
            /*
			$debito_conta = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $debito_conta->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "PAG", ':descricao' => "Pagamento referente a $linha_select_entrega[designacao]", ':debito' => $linha_select_entrega["valor"], ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
            */
			atualiza_saldo($connection, $linha_conta["id"], "PAG", "Pagamento referente a $linha_select_entrega[designacao]", "", $linha_select_entrega["valor"], 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_select_entrega["valor"]);
            
        } else {
            $valido = false;
        }
    }

    if ($valido == true) {
        $query_entregas = $connection->prepare("SELECT en.id, te.designacao, date_format(en.data,'%d-%m-%Y') AS data, en.f_prazo, en.valor, en.mes, en.ano FROM entrega en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON en.id_tipo_entrega=te.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND en.pago=:pago AND te.id=:id");
        $query_entregas->execute(array(':id' => $id_tipo, ':pago' => '0', ':id_empresa' => $_SESSION['id_empresa']));
        $linhas = $query_entregas->rowCount();

        if ($linhas > 0) {
            while ($linha_entregas = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
                if ($linha_entregas['f_prazo'] == "N") {
                    $prazo = "Não";
                } else {
                    $prazo = "Sim";
                }
                $arr_dados[] = array('id' => $linha_entregas['id'], 'tipo' => $linha_entregas['designacao'], 'data' => $linha_entregas['data'], 'f_prazo' => $prazo, 'valor' => $linha_entregas['valor'], 'mes' => conv_mes($linha_entregas['mes']), 'ano' => $linha_entregas['ano']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'saldo' => $saldo_controlo, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem entregas", 'saldo' => $saldo_controlo, 'moeda' => $linha_moeda['simbolo']);
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
    }
    logClicks($connection, "155");
    
} elseif ($_POST['tipo'] == "pag_fatura") {
    $dados = json_decode($_POST['dados'], true);
    $data = date('Y-m-d H:i:s', strtotime($_POST["data"]));
    $valido = true;
	
    foreach ($dados as $key => $value) {
        $id_fatura = $dados[$key]['id'];
        $query_select_fatura = $connection->prepare("SELECT en.id_fornecedor, p.valor, m.ISO4217 FROM pagamento p INNER JOIN encomenda en ON p.id_encomenda=en.id INNER JOIN empresa e ON en.id_empresa=e.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais c ON pf.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id WHERE en.id=:id");
        $query_select_fatura->execute(array(':id' => $id_fatura));
        $linha_select_fatura = $query_select_fatura->fetch(PDO::FETCH_ASSOC);
        
		/* Procurar descontos */
        // $valor = $linha_select_fatura["valor"];
        $valor_s_desc = $linha_select_fatura["valor"];
        $query_fp_desc = $connection->prepare("SELECT * FROM (SELECT ut.id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS curr_user LEFT JOIN (SELECT u.id_entidade, det_enc.data_enc, det_enc.id_produto, det_enc.preco, det_enc.qtd, fpd.desconto, fpd.prazo_pag FROM (SELECT det.id_produto, det.preco, det.quantidade AS qtd, enc.`data` AS data_enc FROM encomenda enc LEFT JOIN detalhes_encomenda det ON enc.id=det.id_encomenda WHERE enc.id=:id_enc) AS det_enc INNER JOIN fp_desconto fpd ON det_enc.id_produto=fpd.id_produto INNER JOIN utilizador u ON fpd.id_utilizador=u.id WHERE fpd.id_fornecedor=:id_fornecedor AND fpd.active=:active) AS desc_enc ON curr_user.id_entidade=desc_enc.id_entidade WHERE desc_enc.id_entidade IS NOT NULL");
        $query_fp_desc->execute(array(':id_utilizador' => $_SESSION['id_utilizador'], ':id_enc' => $id_fatura, ':id_fornecedor' => $linha_select_fatura['id_fornecedor'], 'active' => '1'));
        $count_desc = $query_fp_desc->rowCount();
        
        $total_desc = 0; // Variavel que guarda somatório descontos, para poder mostrar no extrato
        if ($count_desc > 0) {
            $curr_date_virt = new DateTime($data);
            while ($linha_fp_desc = $query_fp_desc->fetch(PDO::FETCH_ASSOC)) {
                $enc_date = new DateTime($linha_fp_desc['data_enc']);
                $date_diff = $curr_date_virt->diff($enc_date)->format("%a");
                
                if ($date_diff < $linha_fp_desc['prazo_pag']) {
                    // $valor -= $linha_fp_desc['preco'] * $linha_fp_desc['desconto'] / 100;
                    $total_desc += $linha_fp_desc['preco'] * $linha_fp_desc['qtd'] * $linha_fp_desc['desconto'] / 100;
                }
            }
        }
        $valor = $valor_s_desc - $total_desc;
        /* */
        
        $query_check_nota_credito = $connection->prepare("SELECT nc.total FROM nota_credito nc INNER JOIN encomenda enc ON nc.id_encomenda=enc.id INNER JOIN empresa e ON enc.id_empresa=e.id_empresa WHERE enc.id=:id_fatura AND e.id_empresa=:id_empresa AND nc.pago='0' LIMIT 1");
        $query_check_nota_credito->execute(array(':id_fatura' => $id_fatura, ':id_empresa' => $_SESSION['id_empresa']));
        $check_nota_credito = $query_check_nota_credito->rowCount();
        
        if ($check_nota_credito > 0) {
            $linha_nota_credito = $query_check_nota_credito->fetch(PDO::FETCH_ASSOC);
            $valor -= $linha_nota_credito["total"];
            $query_updt_nc = $connection->prepare("UPDATE nota_credito nc SET nc.pago=:pago WHERE nc.id_encomenda=:id");
            $query_updt_nc->execute(array(':pago' => '1', ':id' => $id_fatura));
        }
        
		$xchange_rate = 1;
        if ($linha_select_fatura['ISO4217'] != $linha_moeda['ISO4217']) {
            $contents = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
            foreach ($contents->Cube->Cube->Cube as $rates) {
                if ($rates['currency'] == $linha_select_fatura['ISO4217']) {
                    $xchange_rate = floatval($rates['rate']);
                    break;
                }
            }
        }
        // $valor_fatura = number_format($valor * (1 / $xchange_rate), 2, '.', '');
        $valor_fatura = round($valor * (1 / $xchange_rate), 2);

		$query_check_adiant = $connection->prepare("SELECT a.id_adiantamento, a.valor FROM adiantamento a WHERE a.id_empresa=:id_empresa AND a.id_fornecedor=:id_fornecedor AND a.pago=0");
        $query_check_adiant->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_fornecedor' => $linha_select_fatura['id_fornecedor']));
        $nr_adiant = $query_check_adiant->rowCount();
        $linha_adiant = $query_check_adiant->fetchAll();
        
        $total_adiant = 0;
        if ($nr_adiant > 0) {
            foreach ($linha_adiant as $adiants)
                $total_adiant += $adiants['valor'];
        }

        $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
        
        if ($total_adiant > $valor_fatura){
            $desc_adiant = $valor_fatura * 75/100;
            $valor_fatura *= 25/100;
            $valor_adiant = $total_adiant - $desc_adiant;
            foreach ($linha_adiant as $atualiza_adiants) {
                $query_atualiza_adiant = $connection->prepare("UPDATE adiantamento a SET a.pago=1 WHERE a.id_adiantamento=:id_adiantamento");
                $query_atualiza_adiant->execute(array(':id_adiantamento' => $atualiza_adiants['id_adiantamento']));
            }
            $query_diferenca = $connection->prepare("INSERT INTO adiantamento (id_empresa, id_fornecedor, valor, pago, data_virt) VALUES (:id_empresa, :id_fornecedor, :valor, 0, :data_virt)");
            $query_diferenca->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_fornecedor' => $linha_select_fatura['id_fornecedor'], ':valor' => $valor_adiant, ':data_virt' => $data));
            $saldo_controlo = $linha_conta["saldo_controlo"];
        }
        else {
            $valor_fatura -= $total_adiant;
            $saldo_controlo = $linha_conta["saldo_controlo"] - $valor_fatura;
            foreach ($linha_adiant as $atualiza_adiants) {
                $query_atualiza_adiant = $connection->prepare("UPDATE adiantamento a SET a.pago=1 WHERE a.id_adiantamento=:id_adiantamento");
                $query_atualiza_adiant->execute(array(':id_adiantamento' => $atualiza_adiants['id_adiantamento']));
            }
        }

        if ($saldo_controlo > 0) {
            $query_up_fatura = $connection->prepare("UPDATE encomenda en INNER JOIN pagamento p ON en.id=p.id_encomenda SET en.pago=:pago_1, p.pago=:pago_2, p.data_pagamento=:data WHERE en.id=:id");
            $query_up_fatura->execute(array(':pago_1' => '1', ':pago_2' => '1', ':data' => $data, ':id' => $id_fatura));
            
            /* */
            $query_fatura = $connection->prepare("SELECT en.id AS e_id, f.id ,f.nome_abrev, m.simbolo FROM encomenda en INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE en.id=:id");
            $query_fatura->execute(array(':id' => $id_fatura));
            $linha_fatura = $query_fatura->fetch(PDO::FETCH_ASSOC);
            
//            $query_num_fat = $connection->prepare("SELECT @row_num:=@row_num+1 AS rank, T1.* FROM (SELECT e.id AS e_id FROM encomenda e INNER JOIN fornecedor f ON f.id=e.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r");//estava esta (problemas mariaDB para mysql)
            $query_num_fat = $connection->prepare("SELECT @row_num:=@row_num+1 AS 'rank', T1.* FROM (SELECT e.id AS e_id FROM encomenda e INNER JOIN fornecedor f ON f.id=e.id_fornecedor WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r"); // meti esta
            $query_num_fat->execute(array(':fornecedor' => $linha_fatura['id']));

            while ($linha_num_fat = $query_num_fat->fetch(PDO::FETCH_ASSOC)) {
                if($linha_fatura['e_id'] == $linha_num_fat['e_id']) {
                    $num_fat = $linha_num_fat['rank'];
                }
            }
            /* */
            
            // $descricao = "Pagamento referente à fatura nº $num_fat ao fornecedor $linha_fatura[nome_abrev]";
			$descricao = "P. da fatura nº $num_fat à/ao $linha_fatura[nome_abrev]";
            if ($nr_adiant > 0)
                $descricao = $descricao." (Regularização de adiantamento: ". number_format($desc_adiant, 2, ',', '.')." $linha_fatura[simbolo])";
            
			/* Desconto */
            if ($total_desc > 0)
                $descricao = $descricao." / (Desconto: ". number_format($total_desc, 2, ',', '.')." $linha_fatura[simbolo])";
            /* */
			
			/*
            $debito_conta = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $debito_conta->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "PAG", ':descricao' => $descricao, ':debito' => $valor_fatura, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
            */
			atualiza_saldo($connection, $linha_conta["id"], "PAG", $descricao, "", $valor_fatura, 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $valor_fatura);
            
        } else {
            $valido = false;
        }
    }

    if ($valido == true) {
        // $query_faturas = $connection->prepare("SELECT en.id, date_format(en.data,'%d-%m-%Y') AS data, en.iva, en.total FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND en.pago=:pago AND emp.id_empresa=:id_empresa");
        $query_faturas = $connection->prepare("SELECT en.id, en.ref, p.nome_pais, p.nome_abrev AS abrev_pais, f.nome_abrev, date_format(en.data,'%d-%m-%Y') AS data, en.iva, en.total, m.simbolo FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND en.pago=:pago AND emp.id_empresa=:id_empresa ORDER BY en.`data` DESC, f.nome_abrev ASC");
		$query_faturas->execute(array(':pago' => '0', ':id_empresa' => $_SESSION['id_empresa']));
        $linhas = $query_faturas->rowCount();
        if ($linhas > 0) {
            while ($linha_faturas = $query_faturas->fetch(PDO::FETCH_ASSOC)) {
                // $arr_dados[] = array('id' => $linha_faturas['id'], 'tipo' => "Fatura", 'data' => $linha_faturas['data'], 'iva' => $linha_faturas['iva'], 'total' => $linha_faturas['total'], 'moeda' => $linha_moeda['simbolo']);
				$arr_dados[] = array('id' => $linha_faturas['id'], 'ref' => $linha_faturas['ref'], 'pais' => $linha_faturas['nome_pais'], 'abrev_pais' => $linha_faturas['abrev_pais'], 'fornecedor' => $linha_faturas['nome_abrev'], 'data' => $linha_faturas['data'], 'iva' => $linha_faturas['iva'], 'total' => $linha_faturas['total'], 'moeda' => $linha_faturas['simbolo']);
			}
            
			$arr = array('sucesso' => true, 'vazio' => false, 'type' => 'fatura', 'saldo' => $saldo_controlo, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
			// $arr = array('sucesso' => true, 'vazio' => false, 'type' => 'fatura', 'saldo' => $saldo_controlo, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados, 'valor_fatura' => $valor, 'moeda_ini' => $linha_moeda['ISO4217'], 'moeda_fin' => $linha_select_fatura['ISO4217'], 'xchange_rate' => $xchange_rate, 'valor_final' => $valor_fatura);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem faturas", 'saldo' => $saldo_controlo, 'moeda' => $linha_moeda['simbolo']);
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
    }
    logClicks($connection, "157");
    
} elseif ($_POST['tipo'] == "factoring_dados") {
    if (!isset($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal com o formulário");
    } elseif (empty($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Tem de fornecer um id");
    } else {
        $id = $_POST['id'];
        $query_dados_fatura = $connection->prepare("SELECT cliente, valor, date_format(data_virtual,'%d-%m-%Y') AS data FROM fatura WHERE id_fatura=:id LIMIT 1");
        $query_dados_fatura->execute(array(':id' => $id));
        $num_dados = $query_dados_fatura->rowCount();

        if ($num_dados == 1) {
            $linha_dados = $query_dados_fatura->fetch(PDO::FETCH_ASSOC);
            $arr = array('sucesso' => true, 'nome' => $linha_dados['cliente'], 'valor' => $linha_dados['valor'], 'data' => $linha_dados['data']);
        }
    }
    logClicks($connection, "172");
    
} elseif ($_POST['tipo'] == "pedir_factoring") {
    $query_plafond_factoring = $connection->prepare("SELECT re.id_regra, re.id_empresa, re.valor, re.simbolo, re.id_banco FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
    $query_plafond_factoring->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (factoring)"));
    $linha_plafond_factoring = $query_plafond_factoring->fetch(PDO::FETCH_ASSOC);
    $valor = floatval(str_replace(",", ".", str_replace(".", "", $_POST['txtTotalValor'])));
    if ($valor <= $linha_plafond_factoring['valor']) {
        $conta = $_SESSION['id_conta'];
        $dados = json_decode($_POST['factoring'], true);
        $data = date('Y-m-d', strtotime($_POST['txtData']));
        $data_completa = date('Y-m-d H:i:s', strtotime($_POST['txtDataHdd']));
        $tempo = $_POST['txtTotalTempo'];
        $comissao_valor = floatval(str_replace(",", ".", str_replace(".", "", $_POST['txtTotalComissao'])));
        $juros_valor = floatval(str_replace(",", ".", str_replace(".", "", $_POST['txtTotalJuro'])));
        $seguro_valor = floatval(str_replace(",", ".", str_replace(".", "", $_POST['txtTotalSeguro'])));
        $valor_recebido = $valor - ($comissao_valor + $juros_valor + $seguro_valor);
        // if ($_POST["recurso"] == false) {
		if ($_POST["recurso"] == "false") {
            $recurso = '0';
        } else {
            $recurso = '1';
        }
        $query_factoring = $connection->prepare("INSERT INTO factoring (id_conta, data, valor, tempo, recurso, comissao_valor, seguro_valor, juros_valor, valor_recebido) VALUES (:id_conta, :data, :valor, :tempo, :recurso, :comissao_valor, :seguro_valor, :juros_valor, :valor_recebido)");
        $query_factoring->execute(array(':id_conta' => $conta, ':data' => $data, ':valor' => $valor, ':tempo' => $tempo, ':recurso' => $recurso, ':comissao_valor' => $comissao_valor, ':seguro_valor' => $seguro_valor, ':juros_valor' => $juros_valor, ':valor_recebido' => $valor_recebido));
        $num_factoring = $query_factoring->rowCount();
        $query_re_plafond = $connection->prepare("INSERT INTO regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:id_regra, :id_empresa, :data, :valor, :simbolo, :id_banco)");
        $query_re_plafond->execute(array(':id_regra' => $linha_plafond_factoring['id_regra'], ':id_empresa' => $linha_plafond_factoring['id_empresa'], ':data' => date("Y-m-d H:i:s"), ':valor' => $linha_plafond_factoring['valor'] - $valor, ':simbolo' => $linha_plafond_factoring['simbolo'], ':id_banco' => $linha_plafond_factoring['id_banco']));
        if ($num_factoring == 1) {
            $query_id_fact = $connection->prepare("SELECT f.id_factoring FROM factoring f INNER JOIN conta c ON f.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY f.id_factoring DESC LIMIT 1");
            $query_id_fact->execute(array(':id_empresa' => $_SESSION['id_empresa']));
            $linha_dados = $query_id_fact->fetch(PDO::FETCH_ASSOC);
            $id = $linha_dados['id_factoring'];
            $query_saldo = $connection->prepare("SELECT m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
            $query_saldo->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
            $linha_saldo = $query_saldo->fetch(PDO::FETCH_ASSOC);
            $saldo = $linha_saldo['saldo_controlo'] + $valor;
            /*
			$query_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:conta, :data, :tipo, :desc, :cred, :s_cont, :s_contab, :s_disp)");
            $query_mov->execute(array(':conta' => $conta, ':data' => $data_completa, ':tipo' => "CRE", ':desc' => "Factoring efetuado", ':cred' => $valor, ':s_cont' => $saldo, ':s_contab' => $saldo, ':s_disp' => $saldo));
            */
			atualiza_saldo($connection, $conta, "CRE", "Factoring efetuado", "", 0, $valor, $data_completa, $linha_saldo['saldo_controlo'], $linha_saldo['saldo_controlo'], $linha_saldo['saldo_controlo'], $valor);
            
			/*
			$query_comissao_fact = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:conta, :data, :tipo, :desc, :deb, :s_cont, :s_contab, :s_disp)");
            $query_comissao_fact->execute(array(':conta' => $conta, ':data' => $data_completa, ':tipo' => "DEB", ':desc' => "Comissão por factoring", ':deb' => $comissao_valor, ':s_cont' => $saldo - $comissao_valor, ':s_contab' => $saldo - $comissao_valor, ':s_disp' => $saldo - $comissao_valor));
            */
			atualiza_saldo($connection, $conta, "DEB", "Comissão por factoring", "", $comissao_valor, 0, $data_completa, $saldo, $saldo, $saldo, $comissao_valor);
            
			/*
			$query_juros_fact = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:conta, :data, :tipo, :desc, :deb, :s_cont, :s_contab, :s_disp)");
            $query_juros_fact->execute(array(':conta' => $conta, ':data' => $data_completa, ':tipo' => "DEB", ':desc' => "Juros por factoring", ':deb' => $juros_valor, ':s_cont' => $saldo - $comissao_valor - $juros_valor, ':s_contab' => $saldo - $comissao_valor - $juros_valor, ':s_disp' => $saldo - $comissao_valor - $juros_valor));
            */
			atualiza_saldo($connection, $conta, "DEB", "Juros por factoring", "", $juros_valor, 0, $data_completa, $saldo, $saldo, $saldo, $comissao_valor + $juros_valor);
            
			if ($recurso == '0') {
                /*
				$query_seguro_fact = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:conta, :data, :tipo, :desc, :deb, :s_cont, :s_contab, :s_disp)");
                $query_seguro_fact->execute(array(':conta' => $conta, ':data' => $data_completa, ':tipo' => "DEB", ':desc' => "Seguro por factoring", ':deb' => $seguro_valor, ':s_cont' => $saldo - $comissao_valor - $juros_valor - $seguro_valor, ':s_contab' => $saldo - $comissao_valor - $juros_valor - $seguro_valor, ':s_disp' => $saldo - $comissao_valor - $juros_valor - $seguro_valor));
                */
				atualiza_saldo($connection, $conta, "DEB", "Seguro por factoring", "", $seguro_valor, 0, $data_completa, $saldo, $saldo, $saldo, $comissao_valor + $juros_valor + $seguro_valor);
            }
			
            foreach ($dados as $key => $value) {
                $id_fatura = $dados[$key]["hddIdFatura"];
                $query_update = $connection->prepare("UPDATE fatura SET id_factoring=:id, pago=:pago WHERE id_fatura=:id_fatura");
                $query_update->execute(array(':id' => $id, ':id_fatura' => $id_fatura, ':pago' => 1));
                $num_update = $query_update->rowCount();
            }
            if ($num_update > 0) {
                $query_fatura = $connection->prepare("SELECT f.id_fatura, f.num_fatura FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND f.id_empresa=:id_empresa AND id_factoring IS NULL AND pago=:pago");
                $query_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => 0));
                $num_rows = $query_fatura->rowCount();
                if ($num_rows > 0) {
                    while ($linha_fatura = $query_fatura->fetch(PDO::FETCH_ASSOC)) {
                        $arr_dados[] = array('id_fatura' => $linha_fatura['id_fatura'], 'num_fatura' => $linha_fatura['num_fatura']);
                    }
                    $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
                } else {
                    $arr = array('sucesso' => true);
                }
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal no update do factoring");
            }
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na inserção do factoring");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Não tem plafond suficiente");
    }
    logClicks($connection, "173");
    
} elseif ($_POST['tipo'] == "leasing") {
    /* */
	$decimals = 2;
    
    $valor = floatval($_POST["valor_leasing"]);
    $prazo = $_POST["prazo_leasing"];
    $taxa = floatval($_POST["taxa_leasing"]);
    $valor_res = floatval($_POST["valor_res"]);
    $carencia = $_POST["carencia_leasing"];
    $per_paga = $_POST["per_paga_leasing"];
    $taxa_iva = $_POST["taxa_iva"];

    $total_periodos = $prazo + $carencia;
    $taxa_per = $taxa / 100;
    $taxa_mensal = round((pow((1 + $taxa_per), (1 / 12))) - 1, 4);
    $coeficiente = round((1 - (pow((1 + $taxa_mensal), (-$prazo)))) / $taxa_mensal, $decimals);
    $capital_divida = $valor - $valor_res;

    $amortizacao = 0;
    $juros_total = 0;
    $amortizacao_total = 0;
    $prestacao_total = 0;
    $iva_total = 0;
    $pag_iva_total = 0;
    for ($i = 1; $i <= $total_periodos; $i++) {
        $capital_divida = $capital_divida - $amortizacao;
        $juros = round($capital_divida * $taxa_mensal, $decimals);

        if ($carencia >= $i) {
            $prestacao = round($juros * ($per_paga / 100), $decimals);
        } else {
            if (($carencia + 1) == $i)
                $prestacao = round($capital_divida / $coeficiente, $decimals);
        }
        
        //-- PRESTAÇÃO é AUTOMATICAMENTE IGUALADA ao valor do CAPITAL EM DÍVIDA na última
		//  prestação, para garantir que não fica nada por pagar. E juros são "corrigidos".
		if ($i != $total_periodos) { $amortizacao = $prestacao - $juros; }
		else {
			$amortizacao = $capital_divida;
			$juros = $prestacao - $amortizacao;
		}
		
        $iva = round($prestacao * $taxa_iva / 100, $decimals);
        $pag_iva = $prestacao + $iva;

        $juros_total += $juros;
        $amortizacao_total += $amortizacao>0 ? $amortizacao : $amortizacao+$juros;
        $prestacao_total += $prestacao;
        $iva_total += $iva;
        $pag_iva_total += $pag_iva;

        $juros_residual = round($valor_res * $taxa_mensal, $decimals);
        $prestacao_residual = $juros_residual + $valor_res;
        $iva_residual = round($prestacao_residual * $taxa_iva / 100, $decimals);
        $prestacao_iva_residual = $prestacao_residual + $iva_residual;

        $arr_dados[] = array('id' => $i, 'capital_pendente' => $capital_divida, 'juros' => $juros, 'amortizacao' => $amortizacao, 'prestacao_s_iva' => $prestacao, 'iva' => $iva, 'prestacao_c_iva' => $pag_iva);
    }
    $arr = array('sucesso' => true, 'juros_total' => $juros_total, 'amort_total' => $amortizacao_total, 'prestacao_total_s_iva' => $prestacao_total, 'iva_total' => $iva_total, 'prestacao_total_c_iva' => $pag_iva_total, 'juros_residual' => $juros_residual, 'prestacao_residual' => $prestacao_residual, 'iva_residual' => $iva_residual, 'prestacao_iva_residual' => $prestacao_iva_residual, 'valor_residual' => $valor_res, 'dados_in' => $arr_dados);
    logClicks($connection, "167");
	/* */
	
} elseif ($_POST['tipo'] == "simulador_emprestimo") {
    if (!isset($_POST["montante"], $_POST["prazo"], $_POST["taxa"], $_POST["carencia"], $_POST["per_paga"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal, tente efetuar a simulação de novo");
    } elseif (empty($_POST["montante"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira o montante do empréstimo");
    } /* elseif (!preg_match('/^([1-9]{1}[0-9]{3,4}([,.][0-9]{0,2})?|(100000))$/', $_POST["montante"])) {
        $arr = array('sucesso' => false, 'mensagem' => "O montante introduzido não é válido [1000 - 100.000]");
    } */ elseif (empty($_POST["prazo"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira o prazo de financiamento");
    } /* elseif (!preg_match('/^([1-9]|[1-7][0-9]|(8[0-4]))$/', $_POST["prazo"])) {
        $arr = array('sucesso' => false, 'mensagem' => "O prazo de financiamento não é válido [1 - 84]");
    } */ elseif ($_POST["carencia"] == 0 && $_POST["per_paga"] > 0) {
        $arr = array('sucesso' => false, 'mensagem' => "Escolha um período de carência superior a zero");
    } else {
        $montante = floatval($_POST["montante"]);
        $prazo = $_POST["prazo"];
        $taxa = floatval(str_replace(",", ".", str_replace(".", "", $_POST["taxa"])));
        $carencia = $_POST["carencia"];
        $per_paga = $_POST["per_paga"];
        $total_periodos = $prazo + $carencia;
        $taxa_per = $taxa / 100;
        $taxa_mensal = (pow((1 + $taxa_per), (1 / 12))) - 1;
        $coeficiente = (1 - (pow((1 + $taxa_mensal), (-$prazo)))) / $taxa_mensal;
        $capital_divida = $montante;
        $amortizacao = 0;
        $juros_total = 0;
        $amortizacao_total = 0;
        $prestacao_total = 0;
        for ($i = 1; $i <= $total_periodos; $i++) {
            $capital_divida = $capital_divida - $amortizacao;
            $juros = $capital_divida * $taxa_mensal;
            if (($carencia >= $i)) {
                $prestacao = $juros * ($per_paga / 100);
            } else {
                if (($carencia + 1) == $i) {
                    $prestacao = $capital_divida / $coeficiente;
                }
            }
            $amortizacao = $prestacao - $juros;
            $juros_total += $juros;
            $amortizacao_total += $amortizacao;
            $prestacao_total += $prestacao;
            $arr_dados[] = array('id' => $i, 'capital_divida' => $capital_divida, 'juros' => $juros, 'amortizacao' => $amortizacao, 'prestacao' => $prestacao);
        }
        $arr = array('sucesso' => true, 'juros_total' => $juros_total, 'amort_total' => $amortizacao_total, 'prestacao_total' => $prestacao_total, 'dados_in' => $arr_dados);
    }
    logClicks($connection, "161");
    
} elseif ($_POST['tipo'] == "procurar_mov") {
    $data_i_tmp = explode(" ", date('Y-m-d H:i:s', strtotime($_POST['data_i'])));
    $data_inicial = date('Y-m-d H:i:s', strtotime($data_i_tmp[0] . " " . "00:00:00"));
    $data_f_tmp = explode(" ", date('Y-m-d H:i:s', strtotime($_POST['data_f'])));
    $data_final = date('Y-m-d H:i:s', strtotime($data_f_tmp[0] . " " . "23:59:59"));
    $query_movimentos = $connection->prepare("SELECT m.id, date_format(m.data_op,'%d-%m-%Y') as data_op, m.tipo, IF(m.$desc_mov='', '(Description not available in this language)', m.$desc_mov) AS descricao, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' AND m.data_op BETWEEN :data_inicial AND :data_final ORDER BY m.id DESC, m.date_reg DESC");
    $query_movimentos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':data_inicial' => $data_inicial, ':data_final' => $data_final));
    $num_linhas = $query_movimentos->rowCount();

    if ($num_linhas > 0) {
        while ($linha_mov = $query_movimentos->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_mov['id'], 'data' => $linha_mov['data_op'], 'tipo' => $linha_mov['tipo'], 'descricao' => $linha_mov['descricao'], 'debito' => $linha_mov['debito'], 'credito' => $linha_mov['credito'], 'saldo_controlo' => $linha_mov['saldo_controlo'], 'moeda' => $linha_moeda['simbolo']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "141");
    
} elseif ($_POST['tipo'] == "ver_emprestimo") {
    $query_emprest = $connection->prepare("SELECT em.n_per, em.capital_pendente, em.juros, em.amortizacao, em.prestacao, em.pago FROM emprestimo em INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND em.emprest=:id AND emp.id_empresa=:id_empresa");
    $query_emprest->execute(array(':id' => $_POST['id_emprest'], ':id_empresa' => $_SESSION['id_empresa']));
    while ($linha_emp = $query_emprest->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('n_per' => $linha_emp['n_per'], 'cap' => number_format($linha_emp['capital_pendente'], 2, ',', '.'), 'juros' => number_format($linha_emp['juros'], 2, ',', '.'), 'amort' => number_format($linha_emp['amortizacao'], 2, ',', '.'), 'prest' => number_format($linha_emp['prestacao'], 2, ',', '.'), 'pago' => $linha_emp['pago']);
    }
    $arr = array('sucesso' => true, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    logClicks($connection, "164");
    
} elseif ($_POST['tipo'] == "ver_leasing") {
    $query_leas = $connection->prepare("SELECT l.n_per, l.capital_pendente, l.juros, l.amortizacao, l.prestacao_s_iva, l.iva, l.prestacao_c_iva, l.pago FROM leasing l INNER JOIN conta c ON l.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND l.leas=:id AND emp.id_empresa=:id_empresa");
    $query_leas->execute(array(':id' => $_POST['id_leas'], ':id_empresa' => $_SESSION['id_empresa']));
    while ($linha_leas = $query_leas->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('n_per' => $linha_leas['n_per'], 'cap' => number_format($linha_leas['capital_pendente'], 2, ',', '.'), 'juros' => number_format($linha_leas['juros'], 2, ',', '.'), 'amort' => number_format($linha_leas['amortizacao'], 2, ',', '.'), 'prests' => number_format($linha_leas['prestacao_s_iva'], 2, ',', '.'), 'iva' => number_format($linha_leas['iva'], 2, ',', '.'), 'prestc' => number_format($linha_leas['prestacao_c_iva'], 2, ',', '.'), 'pago' => $linha_leas['pago']);
    }
    $arr = array('sucesso' => true, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    logClicks($connection, "170");
    
} /* */ elseif ($_POST['tipo'] == "tipo_dec_ret") {
    $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data_lim_pag,'%d-%m-%Y') AS data, dr.residentes, dr.total FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND dr.pago=:pago AND emp.id_empresa=:id_empresa");
    $query_dec_ret->execute(array(':pago' => '0', ':id_empresa' => $_SESSION['id_empresa']));
    $linhas = $query_dec_ret->rowCount();
    if ($linhas > 0) {
        while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
            if ($linha_dec_ret['residentes'] == "0") {
                $n_res = "Não";
            } else {
                $n_res = "Sim";
            }
            $arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'n_res' => $n_res, 'total' => $linha_dec_ret['total']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem declarações de retenções");
    }
    logClicks($connection, "154");
    
} /* */ elseif ($_POST['tipo'] == "pag_dec_ret") {
    $dados = json_decode($_POST['dados'], true);
    $data = date('Y-m-d H:i:s', strtotime($_POST["data"]));
    $valido = true;

    foreach ($dados as $key => $value) {
        $id_dec_ret = $dados[$key]['id'];
        $query_select_dec_ret = $connection->prepare("SELECT p.valor FROM pagamento p INNER JOIN dec_retencao dr ON p.id_dec_retencao=dr.id WHERE dr.id=:id");
        $query_select_dec_ret->execute(array(':id' => $id_dec_ret));
        $linha_select_dec_ret = $query_select_dec_ret->fetch(PDO::FETCH_ASSOC);

        $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
        
		$saldo_controlo = floatval($linha_conta['saldo_controlo']) - floatval($linha_select_dec_ret['valor']);
		if ($saldo_controlo > 0) {
			$query_up_dec_ret = $connection->prepare("UPDATE dec_retencao dr INNER JOIN pagamento p ON dr.id=p.id_dec_retencao SET dr.pago=:pago_1, p.pago=:pago_2, p.data_pagamento=:data WHERE dr.id=:id");
            $query_up_dec_ret->execute(array(':pago_1' => '1', ':pago_2' => '1', ':data' => $data, ':id' => $id_dec_ret));
            
			/*
			$debito_conta = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $debito_conta->execute(array(':id_conta' => $linha_conta["id"], ':data_op' => $data, ':tipo' => "PAG", ':descricao' => "Pagamento referente a declaração de retenções", ':debito' => $linha_select_dec_ret["valor"], ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
            */
			atualiza_saldo($connection, $linha_conta["id"], "PAG", "Pagamento referente a declaração de retenções", "", $linha_select_dec_ret["valor"], 0, $data, $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_conta["saldo_controlo"], $linha_select_dec_ret["valor"]);
            
        } else {
            $valido = false;
        }
    }

    if ($valido == true) {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data_lim_pag,'%d-%m-%Y') AS data, dr.residentes, dr.total FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND dr.pago=:pago AND emp.id_empresa=:id_empresa");
        $query_dec_ret->execute(array(':pago' => '0', ':id_empresa' => $_SESSION['id_empresa']));
        $linhas = $query_dec_ret->rowCount();

        if ($linhas > 0) {
            while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
                if ($linha_dec_ret['residentes'] == "0") {
                    $n_res = "Não";
                } else {
                    $n_res = "Sim";
                }
                $arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'n_res' => $n_res, 'total' => $linha_dec_ret['total']);
            }
            // $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo']);
			$arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'saldo' => $saldo_controlo, 'moeda' => $linha_moeda['simbolo']);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem declarações de retenções");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
    }
    logClicks($connection, "156");
    
} elseif ($_POST['tipo'] == "pedido_leasing") {
    $dados = json_decode($_POST['leasing'], true);
    $valido = true;
    $data = date('Y-m-d H:i:s', strtotime($_POST["txtData"]));
    $valorLea = $_POST['valorLea'];
    $descricao = $_POST['descricao'];
    $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
    $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
    $query_id_leasing = $connection->prepare("SELECT DISTINCT l.leas FROM leasing l INNER JOIN conta c ON l.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY l.leas DESC");
    $query_id_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $linha_id_leasing = $query_id_leasing->fetch(PDO::FETCH_ASSOC);
    $num_contas = $query_conta->rowCount();
    $query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
    $query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);
    $query_iva_comissao = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
    $query_iva_comissao->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Taxa de IVA normal'));
    $linha_iva_comissao = $query_iva_comissao->fetch(PDO::FETCH_ASSOC);
    $query_com_abert_emp = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
    $query_com_abert_emp->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Comissão de estudo e montagem (leasing mobiliário)'));
    $linha_com_abert_emp = $query_com_abert_emp->fetch(PDO::FETCH_ASSOC);
    $valor_comissao = $linha_com_abert_emp['valor'];
    $valor_iva_comissao = $linha_com_abert_emp['valor'] * ($linha_iva_comissao['valor'] / 100);
    if ($num_contas == 1) {
        $linha_contas = $query_conta->fetch(PDO::FETCH_ASSOC);
        $conta = $linha_contas['id'];
        
		/*
		$query_comissao = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
        $query_comissao->execute(array(':id_conta' => $conta, ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "Comissão estudo e montagem por leasing: $valorLea $linha_moeda[simbolo]", ':debito' => $valor_comissao, ':saldo_controlo' => $linha_contas['saldo_controlo'] - $valor_comissao, ':saldo_contab' => $linha_contas['saldo_controlo'] - $valor_comissao, ':saldo_disp' => $linha_contas['saldo_controlo'] - $valor_comissao));
        */
		atualiza_saldo($connection, $conta, "TAX", "Comissão estudo e montagem por leasing: $valorLea $linha_moeda[simbolo]", "", $valor_comissao, 0, $data, $linha_contas['saldo_controlo'], $linha_contas['saldo_controlo'], $linha_contas['saldo_controlo'], $valor_comissao);
        
		/*
		$query_iva = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
        $query_iva->execute(array(':id_conta' => $conta, ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "IVA por leasing efetuado no valor de $valorLea $linha_moeda[simbolo]", ':debito' => $valor_iva_comissao, ':saldo_controlo' => $linha_contas['saldo_controlo'] - $valor_comissao - $valor_iva_comissao, ':saldo_contab' => $linha_contas['saldo_controlo'] - $valor_comissao - $valor_iva_comissao, ':saldo_disp' => $linha_contas['saldo_controlo'] - $valor_comissao - $valor_iva_comissao));
        */
		atualiza_saldo($connection, $conta, "TAX", "IVA por leasing efetuado no valor de $valorLea $linha_moeda[simbolo]", "", $valor_iva_comissao, 0, $data, $linha_contas['saldo_controlo'], $linha_contas['saldo_controlo'], $linha_contas['saldo_controlo'], $valor_comissao + $valor_iva_comissao);

        foreach ($dados as $key => $value) {
            $n_per = $dados[$key]["txtNPer"];
            $capital = floatval($dados[$key]["txtCapPendente"]);
            $juro = floatval($dados[$key]["txtJurosT"]);
            $amort = floatval($dados[$key]["txtAmortizacao"]);
            $prestacao_s_iva = floatval($dados[$key]["txtPSIVA"]);
            $iva = floatval($dados[$key]["txtIVA"]);
            $prestacao_c_iva = floatval($dados[$key]["txtPCIVA"]);
            if ($prestacao_c_iva == 0) {
                $pago = "1";
            } else {
                $pago = "0";
            }
            $query_leasing = $connection->prepare("INSERT INTO leasing (leas, data_leasing, id_conta, n_per, capital_pendente, juros, amortizacao, prestacao_s_iva, iva, prestacao_c_iva, pago, descricao_bem) VALUES (:leasing, :data, :conta, :n_per, :capital, :juro, :amort, :prestacao_s_iva, :iva, :prestacao_c_iva, :pago, :desc)");
            $query_leasing->execute(array(':leasing' => $linha_id_leasing['leas'] + 1, ':data' => $data, ':conta' => $conta, ':n_per' => $n_per, ':capital' => $capital, ':juro' => $juro, ':amort' => $amort, ':prestacao_s_iva' => $prestacao_s_iva, ':iva' => $iva, ':prestacao_c_iva' => $prestacao_c_iva, ':pago' => $pago, ':desc' => $descricao));
            $linhas = $query_leasing->rowCount();
            $query_select_leas = $connection->prepare("SELECT le.id_leasing FROM leasing le INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY le.id_leasing DESC LIMIT 1");
            $query_select_leas->execute(array(':id_empresa' => $_SESSION['id_empresa']));
            $linha_dados = $query_select_leas->fetch(PDO::FETCH_ASSOC);
            
            $date = new DateTime($data);
            
            $intervalo = new DateInterval('P' . $n_per . 'MT1S');
            $date->add($intervalo);
            $data_lim = $date->format('Y-m-d H:i:s');
            $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim));
            $mes = date('m', strtotime($data_recebida));
            $ano = date('Y', strtotime($data_recebida));
            $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
            $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
            $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano");
            $query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
                $linha = $query->fetch(PDO::FETCH_ASSOC);
                $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
                $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];

                $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim);
            } else {
                $date = new DateTime();
                $intervalo = new DateInterval('P' . $n_per . 'MT1S');
                $date->add($intervalo);
                $data_lim = $date->format('Y-m-d H:i:s');

                $data_lim_r = date("Y-m-d H:i:s", strtotime($data_lim));
            }
            if ($pago == "1") {
                $query_pagamento = $connection->prepare("INSERT INTO pagamento (id_leasing, tipo, data_pagamento, data_limit_pag, data_lim_pag_r, valor, pago) VALUES (:id_lea, :tipo, :data_pag, :data_lim, :data_lim_r, :val, :pago)");
                $query_pagamento->execute(array(':id_lea' => $linha_dados['id_leasing'], ':tipo' => 'L', ':data_pag' => $data, ':data_lim' => $data_lim, ':data_lim_r' => $data_lim, ':val' => $prestacao_c_iva, ':pago' => $pago));
                $linhas_pagamento = $query_pagamento->rowCount();
            } else {
                $query_pagamento = $connection->prepare("INSERT INTO pagamento (id_leasing, tipo, data_limit_pag, data_lim_pag_r, valor, pago) VALUES (:id_lea, :tipo, :data_lim, :data_lim_r, :val, :pago)");
                $query_pagamento->execute(array(':id_lea' => $linha_dados['id_leasing'], ':tipo' => 'L', ':data_lim' => $data_lim, ':data_lim_r' => $data_lim_r, ':val' => $prestacao_c_iva, ':pago' => $pago));
                $linhas_pagamento = $query_pagamento->rowCount();
            }
            if ($linhas != 1 && $linhas_pagamento != 1) {
                $valido = false;
            }
        }
        if ($valido == true) {
            $arr = array('sucesso' => true, 'leas' => $linha_id_leasing['leas'] + 1);
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na inserção das linhas do leasing");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Não se conseguiu encontrar a conta");
    }
    logClicks($connection, "168");
    
} elseif ($_POST['tipo'] == "emprestimo") {
    $query_plafond_credito = $connection->prepare("SELECT re.id_regra, re.id_empresa, re.valor, re.simbolo, re.id_banco FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
    $query_plafond_credito->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (empréstimo)"));
    $linha_plafond_credito = $query_plafond_credito->fetch(PDO::FETCH_ASSOC);
    $valorCre = floatval($_POST['valorCre']);
    $valorCreS = number_format($valorCre, 2, ',', '.');
    if ($valorCre <= $linha_plafond_credito['valor']) {
        $dados = json_decode($_POST['emprestimo'], true);
        $valido = true;
        $data = date('Y-m-d H:i:s', strtotime($_POST["txtData"]));
        $query_conta = $connection->prepare("SELECT c.id FROM conta c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:empresa AND c.tipo_conta='ordem' LIMIT 1");
        $query_conta->execute(array(':empresa' => $_SESSION['id_empresa']));
        $query_emprest = $connection->prepare("SELECT em.emprest FROM emprestimo em INNER JOIN conta c ON c.id=em.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY emprest DESC LIMIT 1");
        $query_emprest->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $linha_emprest = $query_emprest->fetch(PDO::FETCH_ASSOC);
        $num_contas = $query_conta->rowCount();
        if ($num_contas == 1) {
            $linha_contas = $query_conta->fetch(PDO::FETCH_ASSOC);
            $conta = $linha_contas['id'];
            $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
            $query_conta->execute(array(':id_conta_empresa' => $_SESSION['id_empresa']));
            $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
            $query_imposto_selo = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_imposto_selo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Imposto de selo'));
            $linha_imposto_selo = $query_imposto_selo->fetch(PDO::FETCH_ASSOC);
            $query_com_abert_emp = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_com_abert_emp->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => 'Comissão de estudo e montagem (empréstimo)'));
            $linha_com_abert_emp = $query_com_abert_emp->fetch(PDO::FETCH_ASSOC);
            $valor_comissao = $valorCre * ($linha_com_abert_emp['valor'] / 100);
            if ($valor_comissao < 250) {
                $valor_comissao = 250;
            }
            $is = $valor_comissao * ($linha_imposto_selo['valor'] / 100);
            $query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
            $query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
            $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);
            
			/*
			$query_comissao = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $query_comissao->execute(array(':id_conta' => $conta, ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "Comissão estudo e montagem por crédito: $valorCreS $linha_moeda[simbolo]", ':debito' => $valor_comissao, ':saldo_controlo' => $linha_conta['saldo_controlo'] - $valor_comissao, ':saldo_contab' => $linha_conta['saldo_controlo'] - $valor_comissao, ':saldo_disp' => $linha_conta['saldo_controlo'] - $valor_comissao));
            */
			atualiza_saldo($connection, $conta, "TAX", "Comissão estudo e montagem por crédito: $valorCreS $linha_moeda[simbolo]", "", $valor_comissao, 0, $data, $linha_conta['saldo_controlo'], $linha_conta['saldo_controlo'], $linha_conta['saldo_controlo'], $valor_comissao);
            
			/*
			$query_is = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $query_is->execute(array(':id_conta' => $conta, ':data_op' => $data, ':tipo' => "TAX", ':descricao' => "IS por empréstimo: $valorCreS $linha_moeda[simbolo]", ':debito' => $is, ':saldo_controlo' => $linha_conta['saldo_controlo'] - $valor_comissao - $is, ':saldo_contab' => $linha_conta['saldo_controlo'] - $valor_comissao - $is, ':saldo_disp' => $linha_conta['saldo_controlo'] - $valor_comissao - $is));
            */
			atualiza_saldo($connection, $conta, "TAX", "IS por empréstimo: $valorCreS $linha_moeda[simbolo]", "", $is, 0, $data, $linha_conta['saldo_controlo'], $linha_conta['saldo_controlo'], $linha_conta['saldo_controlo'], $valor_comissao + $is);
            
			/*
			$saldo_controlo = $linha_conta['saldo_controlo'] + $valorCre - $valor_comissao - $is;
            $valor_liquido = $valorCre - $valor_comissao - $is;
            $query_emp_cred = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, :tipo, :descricao, :credito, :saldo_controlo, :saldo_contab, :saldo_disp)");
            $query_emp_cred->execute(array(':id_conta' => $conta, ':data_op' => $data, ':tipo' => "CRE", ':descricao' => "Empréstimo efetuado no valor de $valorCreS $linha_moeda[simbolo]", ':credito' => $valorCre, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_controlo, ':saldo_disp' => $saldo_controlo));
            */
			atualiza_saldo($connection, $conta, "CRE", "Empréstimo efetuado no valor de $valorCreS $linha_moeda[simbolo]", "", 0, $valorCre, $data, $linha_conta['saldo_controlo'], $linha_conta['saldo_controlo'], $linha_conta['saldo_controlo'], $valorCre - $valor_comissao - $is);
            
            $query_re_plafond = $connection->prepare("INSERT INTO regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:id_regra, :id_empresa, :data, :valor, :simbolo, :id_banco)");
            $query_re_plafond->execute(array(':id_regra' => $linha_plafond_credito['id_regra'], ':id_empresa' => $linha_plafond_credito['id_empresa'], ':data' => date("Y-m-d H:i:s"), ':valor' => $linha_plafond_credito['valor'] - $valorCre, ':simbolo' => $linha_plafond_credito['simbolo'], ':id_banco' => $linha_plafond_credito['id_banco']));
            foreach ($dados as $key => $value) {
                $n_per = $dados[$key]["txtNPer"];
                $capital = floatval($dados[$key]["txtCapP"]);
                $juro = floatval($dados[$key]["txtJuros"]);
                $amort = floatval($dados[$key]["txtAmort"]);
                $prestacao = floatval($dados[$key]["txtPrestacao"]);
                if ($prestacao == 0) {
                    $pago = "1";
                } else {
                    $pago = "0";
                }
                $query_emprestimo = $connection->prepare("INSERT INTO emprestimo (emprest, data_emprestimo, id_conta, n_per, capital_pendente, juros, amortizacao, prestacao, pago) VALUES (:emprest, :data_emprestimo, :conta, :n_per, :capital, :juro, :amort, :prestacao, :pago)");
                $query_emprestimo->execute(array(':emprest' => $linha_emprest['emprest'] + 1, 'data_emprestimo' => $data, ':conta' => $conta, ':n_per' => $n_per, ':capital' => $capital, ':juro' => $juro, ':amort' => $amort, ':prestacao' => $prestacao, ':pago' => $pago));
                $linhas = $query_emprestimo->rowCount();
                $query_select_emp = $connection->prepare("SELECT em.id FROM emprestimo em INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY em.id DESC LIMIT 1");
                $query_select_emp->execute(array(':id_empresa' => $_SESSION['id_empresa']));
                $linha_dados = $query_select_emp->fetch(PDO::FETCH_ASSOC);
                
                $date = new DateTime($data);
                
                $intervalo = new DateInterval('P' . $n_per . 'MT1S');
                $date->add($intervalo);
                $data_lim = $date->format('Y-m-d H:i:s');
                $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim));
                $mes = date('m', strtotime($data_recebida));
                $ano = date('Y', strtotime($data_recebida));
                $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
                $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
                $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano");
                $query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
                $rowCount = $query->rowCount();
                if ($rowCount > 0) {
                    $linha = $query->fetch(PDO::FETCH_ASSOC);
                    $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
                    $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
                    $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim);
                } else {
                    $date = new DateTime();
                    $intervalo = new DateInterval('P' . $n_per . 'MT1S');
                    $date->add($intervalo);
                    $data_lim = $date->format('Y-m-d H:i:s');
                    $data_lim_r = date("Y-m-d H:i:s", strtotime($data_lim));
                }
                if ($pago == "1") {
                    $query_pagamento = $connection->prepare("INSERT INTO pagamento (id_emprestimo, tipo, data_limit_pag, data_lim_pag_r, valor, pago) VALUES (:id_emp, :tipo, :data_lim, :data_lim_r, :val, :pago)");
                    $query_pagamento->execute(array(':id_emp' => $linha_dados['id'], ':tipo' => 'E', ':data_lim' => $data_lim, ':data_lim_r' => $data_lim, ':val' => $prestacao, ':pago' => $pago));
                    $linhas_pagamento = $query_pagamento->rowCount();
                } else {
                    $query_pagamento = $connection->prepare("INSERT INTO pagamento (id_emprestimo, tipo, data_limit_pag, data_lim_pag_r, valor, pago) VALUES (:id_emp, :tipo, :data_lim, :data_lim_r, :val, :pago)");
                    $query_pagamento->execute(array(':id_emp' => $linha_dados['id'], ':tipo' => 'E', ':data_lim' => $data_lim, ':data_lim_r' => $data_lim_r, ':val' => $prestacao, ':pago' => $pago));
                    $linhas_pagamento = $query_pagamento->rowCount();
                }
                if ($linhas != 1 && $linhas_pagamento != 1) {
                    $valido = false;
                }
            }
            if ($valido == true) {
                $query_plafond_credito = $connection->prepare("SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
                $query_plafond_credito->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (empréstimo)"));
                $linha_plafond_credito = $query_plafond_credito->fetch(PDO::FETCH_ASSOC);
                $arr = array('sucesso' => true, 'plafond' => $linha_plafond_credito['valor']);
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na inserção das linhas do empréstimo");
            }
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Não se conseguiu encontrar a conta");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Não tem plafond suficiente");
    }
    logClicks($connection, "162");
    
} elseif ($_POST['tipo'] == "transf_banc") {
    $id_conta_empresa = $_SESSION['id_empresa'];
    $valido = true;
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $conta = $dados[$key]["conta_destino"];
        $montante = floatval($dados[$key]["montante"]);
        $descricao = $dados[$key]["descricao"];
        $date = date('Y-m-d H:i:s', strtotime($dados[$key]["data_op"]));
        $empresa_destino = $dados[$key]["empresahidden"];
        $query_conta_origem = $connection->prepare("SELECT c.id, m.saldo_controlo, emp.nome FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta = 'ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta_origem->execute(array(':id_conta_empresa' => $id_conta_empresa));
        $count_conta_origem = $query_conta_origem->rowCount();
        if ($count_conta_origem == 1) {
            $linha_conta_origem = $query_conta_origem->fetch(PDO::FETCH_ASSOC);
            if ($linha_conta_origem['saldo_controlo'] > $montante) {
                $id_conta_origem = $linha_conta_origem['id'];
                $nome_empresa = $linha_conta_origem['nome'];
                if ($descricao == "") {
                    $descricao = "Transferência para $empresa_destino";
                }
                $query_conta_destino = $connection->prepare("SELECT c.id, c.id_empresa, saldo_controlo FROM conta c LEFT JOIN movimento m ON c.id=m.id_conta WHERE num_conta=:conta ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
                $query_conta_destino->execute(array(':conta' => $conta));
                $count_conta_destino = $query_conta_destino->rowCount();
                if ($count_conta_destino == 1) {
                    $linha_conta_destino = $query_conta_destino->fetch(PDO::FETCH_ASSOC);
                    $id_conta_destino = $linha_conta_destino['id'];
                    if ($id_conta_destino != 1) {
                        try {
                            $connection->beginTransaction();
                            
							/*
							$query_movimento1 = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta_origem, :date, 'DEB', :descricao, :montante, :saldo_controlo, :saldo_contab, :saldo_disp)");
                            $query_movimento1->execute(array(':id_conta_origem' => $id_conta_origem, ':date' => $date, ':descricao' => $descricao, ':montante' => $montante, ':saldo_controlo' => $saldo_controlo_origem, ':saldo_contab' => $saldo_controlo_origem, ':saldo_disp' => $saldo_controlo_origem));
                            */
							atualiza_saldo($connection, $id_conta_origem, "DEB", $descricao, "", $montante, 0, $date, $linha_conta_origem['saldo_controlo'], $linha_conta_origem['saldo_controlo'], $linha_conta_origem['saldo_controlo'], $montante);
                            
                            $descricao_de = "Transferência de $nome_empresa";
                            
							/*
							$query_movimento2 = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp, ordenante) VALUES (:id_conta_destino, :date, 'CRE', :descricao_de, :montante, :saldo_controlo, :saldo_contab, :saldo_disp, :id_conta_empresa)");
                            $query_movimento2->execute(array(':id_conta_destino' => $id_conta_destino, ':date' => $date, ':descricao_de' => $descricao_de, ':montante' => $montante, ':saldo_controlo' => $saldo_controlo_destino, ':saldo_contab' => $saldo_controlo_destino, ':saldo_disp' => $saldo_controlo_destino, ':id_conta_empresa' => $id_conta_empresa));
                            */
							atualiza_saldo($connection, $id_conta_destino, "CRE", $descricao_de, "", 0, $montante, $date, $linha_conta_destino['saldo_controlo'], $linha_conta_destino['saldo_controlo'], $linha_conta_destino['saldo_controlo'], $montante);
                            
                            /* Atualizar valor juros a prazo */
                            if ($id_conta_empresa == $linha_conta_destino['id_empresa']) {
                                $query_dep = $connection->prepare("SELECT j.id_juro, j.montante, j.tx_juro, j.tx_irc FROM juros_dp j WHERE j.id_conta=:id_conta_destino AND pago='0' AND j.data_lim_r>NOW()");
                                $query_dep->execute(array('id_conta_destino' => $id_conta_destino));
                                while ($linha_dep = $query_dep->fetch(PDO::FETCH_ASSOC)) {
                                    $saldo = str_replace(",", ".", $linha_dep['montante']);
                                    $tx_juro_a = str_replace(",", ".", $linha_dep['tx_juro']);
                                    $tx_juro_m = pow((1 + $tx_juro_a / 100), (1/12)) - 1;
                                    $tx_irc = str_replace(",", ".", $linha_dep['tx_irc']);
                                    
                                    $total_dep = $montante + $saldo;
                                    $juros_dep = floatval($total_dep * $tx_juro_m);
                                    $irc_dep = floatval($juros_dep * $tx_irc) / 100;
                                    $juros_liq = floatval($juros_dep - $irc_dep);
                                    
                                    $query_update = $connection->prepare("UPDATE juros_dp j SET j.montante = :total_dep, valor = :juros_dep, irc = :irc_dep WHERE j.id_juro = :id_juro");
                                    $query_update->execute(array(':total_dep' => $total_dep, ':juros_dep' => $juros_liq, 'irc_dep' => $irc_dep, ':id_juro' => $linha_dep['id_juro']));
                                }
                            }
                            $connection->commit();
                            
                        } catch (PDOException $e) {
                            $connection->rollBack();
                            $valido = false;
                            $arr = array('sucesso' => false, 'mensagem' => $e->getMessage());
                        }
                    } else {
                        /*
						$query_movimento1 = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta_origem, :date, 'DEB', :descricao, :montante, :saldo_controlo, :saldo_contab, :saldo_disp)");
                        $query_movimento1->execute(array(':id_conta_origem' => $id_conta_origem, ':date' => $date, ':descricao' => $descricao, ':montante' => $montante, ':saldo_controlo' => $saldo_controlo_origem, ':saldo_contab' => $saldo_controlo_origem, ':saldo_disp' => $saldo_controlo_origem));
                        */
						atualiza_saldo($connection, $id_conta_origem, "DEB", $descricao, "", $montante, 0, $date, $linha_conta_origem['saldo_controlo'], $linha_conta_origem['saldo_controlo'], $linha_conta_origem['saldo_controlo'], $montante);
                    }
                } else {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "A conta de destino não foi encontrada");
                }
            } else {
                $valido = false;
                $arr = array('sucesso' => false, 'mensagem' => "Não tem saldo suficente");
            }
        } else {
            $valido = false;
            $arr = array('sucesso' => false, 'mensagem' => "A conta de origem não foi encontrada");
        }
    }
    if ($valido == true) {
        $query_movimento_saldo = $connection->prepare("SELECT m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta = 'ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_movimento_saldo->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $linha_movimento_saldo = $query_movimento_saldo->fetch(PDO::FETCH_ASSOC);
        $saldo = $linha_movimento_saldo['saldo_controlo'];
        $query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
        $query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
        $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);
        $arr = array('sucesso' => true, 'saldo' => $saldo, 'moeda' => $linha_moeda['simbolo']);
    }
    logClicks($connection, "145");
    
} elseif ($_POST['tipo'] == "ordenar_prestacoes") {
    if ($_POST['tipo_prestacao'] == 1) {
        if ($_POST['id'] == 0) {
            $query_emprestimo = $connection->prepare("SELECT em.id, em.emprest, date_format(em.data_emprestimo,'%d-%m-%Y') AS data_emprestimo, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN emprestimo em ON p.id_emprestimo=em.id INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago");
            $query_emprestimo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
            $linhas = $query_emprestimo->rowCount();
            if ($linhas > 0) {
                while ($linha_emprestimo = $query_emprestimo->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_emprestimo['id'], 'num' => $linha_emprestimo['emprest'], 'data' => $linha_emprestimo['data_emprestimo'], 'data_limite' => $linha_emprestimo['data_limit_pag'], 'prestacao' => $linha_emprestimo['valor']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem empréstimos");
            }
        } elseif ($_POST['id'] == 1) {
            $query_emprestimo = $connection->prepare("SELECT em.id, em.emprest, date_format(em.data_emprestimo,'%d-%m-%Y') AS data_emprestimo, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN emprestimo em ON p.id_emprestimo=em.id INNER JOIN conta c ON em.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago ORDER BY date(p.data_limit_pag) ASC, time(p.data_limit_pag) DESC");
            $query_emprestimo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
            $linhas = $query_emprestimo->rowCount();
            if ($linhas > 0) {
                while ($linha_emprestimo = $query_emprestimo->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_emprestimo['id'], 'num' => $linha_emprestimo['emprest'], 'data' => $linha_emprestimo['data_emprestimo'], 'data_limite' => $linha_emprestimo['data_limit_pag'], 'prestacao' => $linha_emprestimo['valor']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem empréstimos");
            }
        }
    } elseif ($_POST['tipo_prestacao'] == 2) {
        if ($_POST['id'] == 0) {
            $query_leasing = $connection->prepare("SELECT le.id_leasing, le.leas, date_format(le.data_leasing,'%d-%m-%Y') AS data_leasing, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN leasing le ON p.id_leasing=le.id_leasing INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago");
            $query_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
            $linhas = $query_leasing->rowCount();
            if ($linhas > 0) {
                while ($linha_leasing = $query_leasing->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_leasing['id_leasing'], 'num' => $linha_leasing['leas'], 'data' => $linha_leasing['data_leasing'], 'data_limite' => $linha_leasing['data_limit_pag'], 'prestacao' => $linha_leasing['valor']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem leasings");
            }
        } elseif ($_POST['id'] == 1) {
            $query_leasing = $connection->prepare("SELECT le.id_leasing, le.leas, date_format(le.data_leasing,'%d-%m-%Y') AS data_leasing, date_format(p.data_limit_pag,'%d-%m-%Y') AS data_limit_pag, p.valor FROM pagamento p INNER JOIN leasing le ON p.id_leasing=le.id_leasing INNER JOIN conta c ON le.id_conta=c.id INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND p.pago=:pago ORDER BY date(p.data_limit_pag) ASC, time(p.data_limit_pag) DESC");
            $query_leasing->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':pago' => "0"));
            $linhas = $query_leasing->rowCount();
            if ($linhas > 0) {
                while ($linha_leasing = $query_leasing->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_leasing['id_leasing'], 'num' => $linha_leasing['leas'], 'data' => $linha_leasing['data_leasing'], 'data_limite' => $linha_leasing['data_limit_pag'], 'prestacao' => $linha_leasing['valor']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem leasings");
            }
        }
    }
    logClicks($connection, "148");
    
} elseif ($_POST['tipo'] == "movimentos") {
    $query_total_movimentos = $connection->prepare("SELECT m.id, date_format(m.data_op,'%d-%m-%Y') as data_op, m.tipo, IF(m.$desc_mov='', '(Description not available in this language)', m.$desc_mov) AS descricao, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC");
    $query_total_movimentos->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $num_linhas = $query_total_movimentos->rowCount();
    $paginas = ceil($num_linhas / 5);
    $query_movimentos = $connection->prepare("SELECT m.id, date_format(m.data_op,'%d-%m-%Y') as data_op, m.tipo, IF(m.$desc_mov='', '(Description not available in this language)', m.$desc_mov) AS descricao, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 5");
    $query_movimentos->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    if ($num_linhas > 0) {
        while ($linha = $query_movimentos->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'data_op' => $linha['data_op'], 'tipo' => $linha['tipo'], 'descricao' => $linha['descricao'], 'debito' => $linha['debito'], 'credito' => $linha['credito'], 'saldo_controlo' => $linha['saldo_controlo'], 'saldo_contab' => $linha['saldo_contab'], 'saldo_disp' => $linha['saldo_disp']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'linhas' => $num_linhas, 'paginas' => $paginas, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    
} elseif ($_POST['tipo'] == "movimentos_limite") {
    $query_total_movimentos = $connection->prepare("SELECT m.id, date_format(m.data_op,'%d-%m-%Y') as data_op, m.tipo, IF(m.$desc_mov='', '(Description not available in this language)', m.$desc_mov) AS descricao, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC");
    $query_total_movimentos->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $num_linhas = $query_total_movimentos->rowCount();
    $paginas = ceil($num_linhas / $_POST['limite']);
    $query_movimentos = $connection->prepare("SELECT m.id, date_format(m.data_op,'%d-%m-%Y') as data_op, m.tipo, IF(m.$desc_mov='', '(Description not available in this language)', m.$desc_mov) AS descricao, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT :start, :limite");
    $query_movimentos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':start' => ($_POST['pagina_atual'] - 1) * $_POST['limite'], ':limite' => $_POST['limite']));
    if ($num_linhas > 0) {
        while ($linha = $query_movimentos->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'data_op' => $linha['data_op'], 'tipo' => $linha['tipo'], 'descricao' => $linha['descricao'], 'debito' => $linha['debito'], 'credito' => $linha['credito'], 'saldo_controlo' => $linha['saldo_controlo'], 'saldo_contab' => $linha['saldo_contab'], 'saldo_disp' => $linha['saldo_disp']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'linhas' => $num_linhas, 'paginas' => $paginas, 'pag_inicial' => $_POST['pagina_atual'], 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    
} elseif ($_POST['tipo'] == "gera_iban") {
    $query_banco = $connection->prepare("SELECT b.id, b.cod_banco FROM banco b INNER JOIN entidade_banco eb ON b.id=eb.id_banco INNER JOIN utilizador u ON eb.id_entidade=u.id_entidade WHERE u.id = :id_utilizador LIMIT 1");
    $query_banco->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    $row_banco = $query_banco->fetch(PDO::FETCH_ASSOC);
    $num_linhas_banco = $query_banco->rowCount();
    $pais = "PT50";
    $cod_banco = $row_banco['cod_banco'];
    $cod_balcao = "2040";
    $num = gerarIBAN($pais, $cod_banco, $cod_balcao);
    $num_array = explode("_", $num);
    $num_conta = $num_array[2];
    $query_num_conta = $connection->prepare("SELECT num_conta FROM conta");
    $query_num_conta->execute();
    $linha_num_conta = $query_num_conta->fetch(PDO::FETCH_ASSOC);
    $count = $query_num_conta->rowCount();
    for ($i = 0; $i < $count; $i++) {
        if ($num_conta == $linha_num_conta['num_conta']) {
            $num = gerarIBAN($pais, $cod_banco, $cod_balcao);
            $num_array = explode("_", $num);
            $num_conta = $num_array[2];
            $i = 0;
        }
    }
    $num_conta12 = '0' . $num_conta;
    $nib = $num_array[1] . $num_array[2] . $num_array[3];
    $iban = $num_array[0] . $num_array[1] . $num_array[2] . $num_array[3];
    
    if($num_linhas_banco > 0) {
        $arr = array('sucesso' => true, 'iban' => $iban, 'nib' => $nib, 'num_conta' => $num_conta12);
    }
    else
        $arr = array('sucesso' => false, 'mensagem' => "O seu banco não foi encontrado.");
}

elseif ($_POST['tipo'] == "criar_CP"){
    if (!isset($_POST["id_emp"], $_POST["id_banco"], $_POST["iban"], $_POST["nib"], $_POST["num_conta"], $_POST["montante"]))
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal, não foi possivel criar a conta a prazo.");
    
    else{
        if ($_POST["iban"] == "" || $_POST["nib"] == "" || $_POST["num_conta"] == "")
            $arr = array('sucesso' => false, 'mensagem' => "Por favor, gere um IBAN");
        
        elseif ($_POST["montante"] == "")
            $arr = array('sucesso' => false, 'mensagem' => "Por favor, insira o montante do Depósito de Entrada");
        
        else{
            $valido = true;
            $id_conta_empresa = $_SESSION['id_empresa'];
            $id_empresa = $_POST["id_emp"];
            $id_banco = $_POST["id_banco"];
            $iban = $_POST["iban"];
            $nib = $_POST["nib"];
            $num_conta = $_POST["num_conta"];
            $montante = floatval(str_replace(",", ".", str_replace(".", "", $_POST["montante"])));
            
            $tx_juro_a = $_POST['tx_juros'];
            $tx_juro_m = pow((1 + $tx_juro_a / 100), (1/12)) - 1;
            $tx_irc = $_POST['tx_irc'];
            
            $n_per = $_POST["prazo"];
            $data_virt = date('Y-m-d H:i:s', strtotime($_POST["datavirt"]));
            
            $date = new DateTime($data_virt);
            $intervalo = new DateInterval('P' . $n_per . 'MT1S');
            $date->add($intervalo);
            $data_lim_v = $date->format('Y-m-d H:i:s');
            
            $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim_v));
            $mes = date('m', strtotime($data_recebida));
            $ano = date('Y', strtotime($data_recebida));
            $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
            $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));

            $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano AND CURDATE() <= cal.data_fim ORDER BY cal.data_fim DESC LIMIT 1");
            $query->execute(array(':id_empresa' => $id_conta_empresa, ':mes' => $mes, ':ano' => $ano));
            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
                $linha = $query->fetch(PDO::FETCH_ASSOC);
                $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
                $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
                $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim_v);
            }
            else
                $data_lim_r = $data_lim_v;
            
            $query_conta_origem = $connection->prepare("SELECT c.id, m.saldo_controlo, m.saldo_contab, m.saldo_disp, emp.nome, c.id_banco FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_conta_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
            $query_conta_origem->execute(array(':id_conta_empresa' => $id_conta_empresa));
            $count_conta_origem = $query_conta_origem->rowCount();
            if ($count_conta_origem == 1) {
                $linha_conta_origem = $query_conta_origem->fetch(PDO::FETCH_ASSOC);
                if ($linha_conta_origem['saldo_controlo'] > $montante) {
                    $id_conta_origem = $linha_conta_origem['id'];
                    $nome_empresa = $linha_conta_origem['nome'];
                    $descricao = "Transferência para depósito a prazo";
                    
					try {
                        $connection->beginTransaction();
                        $query_conta = $connection->prepare("INSERT INTO conta (num_conta, nib, iban, tipo_conta, id_banco, id_empresa, date, data_lim_v, data_lim_r) VALUES (:num_conta, :nib, :iban, 'prazo', :id_banco, :id_empresa, :data, :data_lim_v, :data_lim_r)");
                        $query_conta->execute(array(':num_conta' => $num_conta, ':nib' => $nib, ':iban' => $iban, ':id_banco' => $id_banco, ':id_empresa' => $id_empresa, ':data' => $data_virt, ':data_lim_v' => $data_lim_v, ':data_lim_r' => $data_lim_r));
                        $id_conta_destino = $connection->lastInsertId();
                            
                        /*
						$saldo_controlo_destino = $montante;
                        $query_movimento1 = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta_origem, :date, 'DEB', :descricao, :montante, :saldo_controlo, :saldo_contab, :saldo_disp)");
                        $query_movimento1->execute(array(':id_conta_origem' => $id_conta_origem, ':date' => $data_virt, ':descricao' => $descricao, ':montante' => $montante, ':saldo_controlo' => $saldo_controlo_origem, ':saldo_contab' => $saldo_controlo_origem, ':saldo_disp' => $saldo_controlo_origem));
                        */
						atualiza_saldo($connection, $id_conta_origem, "DEB", $descricao, "", $montante, 0, $data_virt, $linha_conta_origem['saldo_controlo'], $linha_conta_origem['saldo_contab'], $linha_conta_origem['saldo_disp'], $montante);
                        
                        $descricao_de = "Depósito a Prazo";
                        /*
						$query_movimento2 = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp, ordenante) VALUES (:id_conta_destino, :date, 'CRE', :descricao_de, :montante, :saldo_controlo, :saldo_contab, :saldo_disp, :id_conta_empresa)");
                        $query_movimento2->execute(array(':id_conta_destino' => $id_conta_destino, ':date' => $data_virt, ':descricao_de' => $descricao_de, ':montante' => $montante, ':saldo_controlo' => $saldo_controlo_destino, ':saldo_contab' => $saldo_controlo_destino, ':saldo_disp' => $saldo_controlo_destino, ':id_conta_empresa' => $id_conta_empresa));
                        */
						atualiza_saldo($connection, $id_conta_destino, "CRE", $descricao_de, "", 0, $montante, $data_virt, $saldo_controlo_destino, $saldo_controlo_destino, $saldo_controlo_destino, $montante);
                        
						$valor = floatval($montante * $tx_juro_m);
                        $irc = floatval($valor * $tx_irc) / 100;
                        $juroliq = floatval($valor-$irc);

                        $query_dep = $connection->prepare("SELECT j.deposito FROM juros_dp j INNER JOIN conta c ON j.id_conta=c.id WHERE c.id_empresa=:id_empresa AND c.tipo_conta='prazo' ORDER BY j.deposito DESC LIMIT 1;");
                        $query_dep->execute(array(':id_empresa' => $id_conta_empresa));
                        $count_dep = $query_dep->rowCount();

                        if ($count_dep == 0)
                            $deposito = 1;
                        else {
                            $valdep = $query_dep->fetch(PDO::FETCH_ASSOC);
                            $deposito = $valdep['deposito'] + 1;
                        }

                        for ($i = 1; $i <= $n_per; $i++) {
                            $date_juros = new DateTime($data_virt);
                            $prestacao = new DateInterval('P' . $i . 'MT1S');
                            $date_juros->add($prestacao);
                            $data_juros_v = $date_juros->format('Y-m-d H:i:s');

                            $data_recebida_j = date("Y-m-d H:i:s", strtotime($data_juros_v));
                            $mes_j = date('m', strtotime($data_recebida_j));
                            $ano_j = date('Y', strtotime($data_recebida_j));
                            $primeiro_dia_j = date('d', mktime(0, 0, 0, $mes_j, 1, $ano_j));
                            $ultimo_dia_j = date('t', mktime(0, 0, 0, $mes_j, 1, $ano_j));

                            $query_data_juros = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano AND CURDATE() <= cal.data_fim ORDER BY cal.data_fim DESC LIMIT 1");
                            $query_data_juros->execute(array(':id_empresa' => $id_conta_empresa, ':mes' => $mes_j, ':ano' => $ano_j));
                            $calendario = $query_data_juros->rowCount();
                            if ($calendario > 0){
                                $linha_data_juros = $query_data_juros->fetch(PDO::FETCH_ASSOC);
                                $data_inicio_j = $linha_data_juros['data_inicio'] . " " . $linha_data_juros['hora_inicio'];
                                $data_fim_j = $linha_data_juros['data_fim'] . " " . $linha_data_juros['hora_fim'];
                                $data_juros_r = dataReal($primeiro_dia_j, $ultimo_dia_j, $mes_j, $ano_j, $data_inicio_j, $data_fim_j, $data_juros_v);
                            }
                            else
                                $data_juros_r = $data_juros_v;
                            
                            $query_juros = $connection->prepare("INSERT INTO juros_dp (id_conta, deposito, prestacao, montante, tx_juro, valor, tx_irc, irc, pago, data_virtual, data_lim_v, data_lim_r) VALUES (:id_conta, :deposito, :prestacao, :montante, :tx_juros, :valor, :tx_irc, :irc, '0', :date_virt, :data_juros_v, :data_juros_r)");
                            $query_juros->execute(array(':id_conta' => $id_conta_destino, ':deposito' => $deposito, ':prestacao' => $i, ':montante' => $montante, ':tx_juros' => $tx_juro_a, ':valor' => $juroliq, ':tx_irc'=>$tx_irc, ':irc' => $irc, ':date_virt' => $data_virt, ':data_juros_v' => $data_juros_v, ':data_juros_r' => $data_juros_r));
                        }
                        $connection->commit();
                        
                    } catch (PDOException $e) {
                        $connection->rollBack();
                        $valido = false;
                        $arr = array('sucesso' => false, 'mensagem' => $e->getMessage());
                    }
                }
                else {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Não tem saldo suficente.");
                }
            } 
            else {
                $valido = false;
                $arr = array('sucesso' => false, 'mensagem' => "A sua conta a ordem não foi encontrada.");
            }
        
            if ($valido == true) {
                logClicks($connection, "182");
                
				$query_movimento_saldo = $connection->prepare("SELECT m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
                $query_movimento_saldo->execute(array(':id_empresa' => $_SESSION['id_empresa']));
                $linha_movimento_saldo = $query_movimento_saldo->fetch(PDO::FETCH_ASSOC);
                $saldo = $linha_movimento_saldo['saldo_controlo'];
                $query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
                $query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
                $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

                $arr = array('sucesso' => true, 'saldo' => $saldo, 'moeda' => $linha_moeda['simbolo'], 'mensagem' => "Conta a Prazo criada com sucesso.");
            }
        }
    }
}

elseif ($_POST['tipo'] == "ren_CP"){
    $valido = true;
    $id_conta_empresa = $_SESSION['id_empresa'];
    $id_conta = $_POST['id_conta'];
    $montante = floatval(str_replace(",", ".", str_replace(".", "", $_POST['montante'])));
    $tx_juro_a = $_POST['tx_juros'];
    $tx_juro_m = pow((1 + $tx_juro_a / 100), (1/12)) - 1;
    $tx_irc = $_POST['tx_irc'];
    $n_per = $_POST['prazo'];
    $data_virt = date('Y-m-d H:i:s', strtotime($_POST['datavirt']));

    $date = new DateTime($data_virt);
    $intervalo = new DateInterval('P' . $n_per . 'MT1S');
    $date->add($intervalo);
    $data_lim_v = $date->format('Y-m-d H:i:s');

    $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim_v));
    $mes = date('m', strtotime($data_recebida));
    $ano = date('Y', strtotime($data_recebida));
    $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
    $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));

    $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano AND CURDATE() <= cal.data_fim ORDER BY cal.data_fim DESC LIMIT 1");
    $query->execute(array(':id_empresa' => $id_conta_empresa, ':mes' => $mes, ':ano' => $ano));
    $rowCount = $query->rowCount();
    if ($rowCount > 0) {
        $linha = $query->fetch(PDO::FETCH_ASSOC);
        $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
        $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
        $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim_v);
    }
    else
        $data_lim_r = $data_lim_v;

    try {
        $connection->beginTransaction();
        $query_conta = $connection->prepare("UPDATE conta c SET data_lim_v = :data_lim_v, data_lim_r = :data_lim_r WHERE c.id = :id_conta");
        $query_conta->execute(array(':data_lim_v' => $data_lim_v, ':data_lim_r' => $data_lim_r, ':id_conta' => $id_conta));
        
        $valor = floatval($montante * $tx_juro_m);
        $irc = floatval($valor * $tx_irc) / 100;
        $juroliq = floatval($valor-$irc);

        $query_dep = $connection->prepare("SELECT j.deposito, j.prestacao FROM juros_dp j INNER JOIN conta c ON j.id_conta=c.id WHERE c.id_empresa=:id_empresa AND c.tipo_conta='prazo' ORDER BY j.deposito DESC, j.prestacao DESC LIMIT 1");
        $query_dep->execute(array(':id_empresa' => $id_conta_empresa));
        $deposito = $query_dep->fetch(PDO::FETCH_ASSOC);

        for ($i = $deposito['prestacao'] + 1; $i <= $n_per + $deposito['prestacao']; $i++) {
            $date_juros = new DateTime($data_virt);
            $j = $i - $deposito['prestacao'];
            $prestacao = new DateInterval('P' . $j. 'MT1S');
            $date_juros->add($prestacao);
            $data_juros_v = $date_juros->format('Y-m-d H:i:s');

            $data_recebida_j = date("Y-m-d H:i:s", strtotime($data_juros_v));
            $mes_j = date('m', strtotime($data_recebida_j));
            $ano_j = date('Y', strtotime($data_recebida_j));
            $primeiro_dia_j = date('d', mktime(0, 0, 0, $mes_j, 1, $ano_j));
            $ultimo_dia_j = date('t', mktime(0, 0, 0, $mes_j, 1, $ano_j));

            $query_data_juros = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano AND CURDATE() <= cal.data_fim ORDER BY cal.data_fim DESC LIMIT 1");
            $query_data_juros->execute(array(':id_empresa' => $id_conta_empresa, ':mes' => $mes_j, ':ano' => $ano_j));
            $calendario = $query_data_juros->rowCount();
            if ($calendario > 0){
                $linha_data_juros = $query_data_juros->fetch(PDO::FETCH_ASSOC);
                $data_inicio_j = $linha_data_juros['data_inicio'] . " " . $linha_data_juros['hora_inicio'];
                $data_fim_j = $linha_data_juros['data_fim'] . " " . $linha_data_juros['hora_fim'];
                $data_juros_r = dataReal($primeiro_dia_j, $ultimo_dia_j, $mes_j, $ano_j, $data_inicio_j, $data_fim_j, $data_juros_v);
            }
            else
                $data_juros_r = $data_juros_v;

            $query_juros = $connection->prepare("INSERT INTO juros_dp (id_conta, deposito, prestacao, montante, tx_juro, valor, tx_irc, irc, pago, data_virtual, data_lim_v, data_lim_r) VALUES (:id_conta, :deposito, :prestacao, :montante, :tx_juros, :valor, :tx_irc, :irc, '0', :date_virt, :data_juros_v, :data_juros_r)");
            $query_juros->execute(array(':id_conta' => $id_conta, ':deposito' => $deposito['deposito'], ':prestacao' => $i, ':montante' => $montante, ':tx_juros' => $tx_juro_a, ':valor' => $juroliq, ':tx_irc'=>$tx_irc, ':irc' => $irc, ':date_virt' => $data_virt, ':data_juros_v' => $data_juros_v, ':data_juros_r' => $data_juros_r));

        }
		$connection->commit();

    } catch (PDOException $e) {
        $connection->rollBack();
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => $e->getMessage());
    }
    
    if($valido == true){
        $arr = array('sucesso' => true, 'mensagem' => "Conta a Prazo renovada com sucesso.");
        logClicks($connection, "189");
    }
}

elseif ($_POST['tipo'] == "term_CP"){
    $valido = true;
    $montante = floatval(str_replace(",", ".", str_replace(".", "", $_POST['montante'])));
    $data_virt = date('Y-m-d H:i:s', strtotime($_POST['datavirt']));
    
    $query_conta_ordem = $connection->prepare("SELECT m.id_conta, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id = m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
    $query_conta_ordem->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $count_conta_ordem = $query_conta_ordem->rowCount();
    
    if($count_conta_ordem == 1){
        $linha_conta_ordem = $query_conta_ordem->fetch(PDO::FETCH_ASSOC);
        
		$query_dep = $connection->prepare("SELECT j.deposito FROM juros_dp j INNER JOIN conta c ON j.id_conta=c.id WHERE c.id_empresa=:id_empresa AND c.tipo_conta='prazo' ORDER BY j.deposito DESC, j.prestacao DESC LIMIT 1");
        $query_dep->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $deposito = $query_dep->fetch(PDO::FETCH_ASSOC);
        
		/*
        $query_atualiza_ordem = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_op, 'CRE', CONCAT('Transferência de Depósito a prazo nº ', :last_dp), '0.000', :credito, :saldo_controlo, :saldo_contab, :saldo_disp)");
        $query_atualiza_ordem->execute(array(':id_conta' => $linha_conta_ordem['id_conta'], ':data_op' => $data_virt, ':last_dp' => $deposito['deposito'], ':credito' => $montante, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_contab, ':saldo_disp' => $saldo_disp));
        */
		atualiza_saldo($connection, $linha_conta_ordem['id_conta'], "CRE", "Transferência de Depósito a prazo nº $deposito[deposito]", "", 0, $montante, $data_virt, $linha_conta_ordem['saldo_controlo'], $linha_conta_ordem['saldo_contab'], $linha_conta_ordem['saldo_disp'], $montante);
    }
    else {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "A sua conta a ordem não foi encontrada.");
    }
        
    if ($valido == true) {
        $query_movimento_saldo = $connection->prepare("SELECT m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_movimento_saldo->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $linha_movimento_saldo = $query_movimento_saldo->fetch(PDO::FETCH_ASSOC);
        $saldo = $linha_movimento_saldo['saldo_controlo'];
        $query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
        $query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
        $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

        $arr = array('sucesso' => true, 'saldo' => $saldo, 'moeda' => $linha_moeda['simbolo'], 'mensagem' => "Depósito a Prazo terminado com sucesso.");
        logClicks($connection, "190");
    }
}

elseif ($_POST['tipo'] == "letra_dados") {
    if (!isset($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal com o formulário");
    }
    elseif (empty($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Tem de fornecer um id");
    }
    
    else {
        $id = $_POST['id'];
        $query_dados_fatura = $connection->prepare("SELECT cliente, valor, data_lim_v AS data FROM fatura WHERE id_fatura=:id LIMIT 1");
        $query_dados_fatura->execute(array(':id' => $id));
        $num_dados = $query_dados_fatura->rowCount();
        
        if ($num_dados == 1) {
            $linha_dados = $query_dados_fatura->fetch(PDO::FETCH_ASSOC);
            $data_lim_v = date("d-m-Y", strtotime($linha_dados['data']));
            
            $now = new DateTime(date('Y-m-d H:i:s', strtotime($_POST['data_virt'])));
            $limite = new DateTime(date('Y-m-d H:i:s', strtotime($linha_dados['data'])));
            $diff = ($limite->diff($now)->format('%a')) - 2;
            
            $arr = array('sucesso' => true, 'nome' => $linha_dados['cliente'], 'valor' => $linha_dados['valor'], 'data' => $data_lim_v, 'prazomax' => $diff);
        }
    }
    // logClicks($connection, "1xx");
}

elseif ($_POST['tipo'] == "pedido_letra") {
    $valido = true;
    
    if (!isset($_POST['tipocli'])) {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal. Por favor, tente novamente.");
    }
    elseif ($_POST['tipocli'] == "interno") {
        if (!isset($_POST["id_emp"], $_POST["txis"], $_POST["txcomissao"], $_POST["txjuro"], $_POST["data_virt"]))
            $valido = false;
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal. Por favor, tente novamente.");
    }
    elseif ($_POST['tipocli'] == "externo") {    
        if (!isset($_POST["id_fat"], $_POST["valor_total"], $_POST["txis"], $_POST["txcomissao"], $_POST["txjuro"], $_POST["data_virt"]))
            $valido = false;
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal. Por favor, tente novamente.");
    }
    elseif (empty($_POST["valor"])) {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, indique o valor da letra.");
    }
    elseif (empty($_POST["prazo"])) {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, indique o prazo da letra.");
    }
    elseif ($_POST["tipocli"] == "interno" && !isset($_POST["id_emp"])) {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, escolha o sacado.");
    }
    elseif ($_POST["tipocli"] == "externo" && empty($_POST["valor_total"])) {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, escolha uma fatura.");
    }
    elseif ($_POST["tipocli"] == "externo" && (floatval(str_replace(",", ".", str_replace(".", "", $_POST["valor"])))) > (floatval(str_replace(",", ".", str_replace(".", "", $_POST["valor_total"]))))) {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, insira um valor menor do que o total da fatura.");
    }
    elseif ($_POST["tipocli"] == "externo" && $_POST["prazo"] > $_POST["prazo_max"]) {
        $valido = false;
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, insira um prazo menor que o prazo máximo.");
    }

    if ($valido == true) {
        $id_empresa = $_SESSION['id_empresa'];
        $valor_letra = floatval(str_replace(",", ".", str_replace(".", "", $_POST['valor'])));
        
        $tx_juro_a = $_POST['txjuro'];
        $tx_juro_d = pow((1 + $tx_juro_a / 100), (1/365)) - 1;
        
        $tx_c = $_POST['txcomissao'] / 100;
        $com = $valor_letra * $tx_c;
        
        $tx_is = $_POST['txis'] / 100;
        $is = ($valor_letra + $com) * $tx_is;
        
        $n_dias = $_POST['prazo'] + 2;
        $juro_tot = $valor_letra * $tx_juro_d * $n_dias;
        $valor = $valor_letra - $com - $is - $juro_tot;
        
        $data_virt = date('Y-m-d H:i:s', strtotime($_POST['data_virt']));
        
        $date = new DateTime($data_virt);
        $intervalo = new DateInterval('P' . $n_dias . 'D');
        $date->add($intervalo);
        $data_lim_v = $date->format('Y-m-d H:i:s');

        $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim_v));
        $mes = date('m', strtotime($data_recebida));
        $ano = date('Y', strtotime($data_recebida));
        $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
        $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));

        $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano AND CURDATE() <= cal.data_fim ORDER BY cal.data_fim DESC LIMIT 1");
        $query->execute(array(':id_empresa' => $id_empresa, ':mes' => $mes, ':ano' => $ano));
        $rowCount = $query->rowCount();
        if ($rowCount > 0) {
            $linha = $query->fetch(PDO::FETCH_ASSOC);
            $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
            $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
            $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim_v);
        }
        else
            $data_lim_r = $data_lim_v;
        
        try {
            $connection->beginTransaction();
            
            $query_conta = $connection->prepare("SELECT c.id, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN empresa emp ON emp.id_empresa=c.id_empresa WHERE emp.ativo='1' AND c.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
            $query_conta->execute(array(':id_empresa' => $id_empresa));
            $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
            $id_conta = $linha_conta['id'];
            
			if ($_POST["tipocli"] == "interno") {
                if ($_POST["id_emp"] == $_SESSION['id_empresa']) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Não pode descontar uma letra sobre a sua própria empresa");
                }
                else {
                    $id_sacado = $_POST['id_emp'];
                    $query_conta_sacado = $connection->prepare("SELECT c.id FROM conta c INNER JOIN empresa e ON c.id_empresa=e.id_empresa WHERE e.id_empresa = :id_sacado AND c.tipo_conta='ordem' LIMIT 1");
                    $query_conta_sacado->execute(array(':id_sacado' => $id_sacado));
                    $linha_conta_sacado = $query_conta_sacado->fetch(PDO::FETCH_ASSOC);
                    $id_conta_sacado = $linha_conta_sacado['id'];
                    $nome_sacado = $_POST['nome_sacado'];
                    $descricao = "Desconto de letra sobre a empresa ". $nome_sacado ."";
                    
					/*
					$query_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_virt, 'CRE', :descricao, :valor, :saldo_controlo, :saldo_contab, :saldo_disp)");
                    $query_mov->execute(array(':id_conta' => $id_conta, ':data_virt' => $data_virt, ':descricao' => $descricao, ':valor' => $valor, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_contab, ':saldo_disp' => $saldo_disp));
                    */
					atualiza_saldo($connection, $id_conta, "CRE", $descricao, "", 0, $valor, $data_virt, $linha_conta['saldo_controlo'], $linha_conta['saldo_contab'], $linha_conta['saldo_disp'], $valor);
                    
                    $query_let = $connection->prepare("INSERT INTO letra (id_conta_empresa, id_conta_sacado, imp_s, com, juro, valor, aceite, pago, data_virt, data_lim_r, data_lim_v) VALUES (:id_empresa, :id_sacado, :is, :com, :juro, :valor, '0', '0', :data_virt, :data_lim_r, :data_lim_v)");
                    $query_let->execute(array(':id_empresa' => $id_conta, ':id_sacado' => $id_conta_sacado, ':is' => $is, ':com' => $com, ':juro' => $juro_tot, ':valor' => $valor_letra, ':data_virt' => $data_virt, ':data_lim_r' => $data_lim_r, ':data_lim_v' => $data_lim_v));
                }
            }
			elseif ($_POST["tipocli"] == "externo") {
                $id_fat = $_POST["id_fat"];

                $query_dados_fat = $connection->prepare("SELECT f.num_fatura, f.cliente, f.valor FROM fatura f WHERE f.id_fatura = :id_fat LIMIT 1");
                $query_dados_fat->execute(array(':id_fat' => $id_fat));
                $count_fat = $query_dados_fat->rowCount();

                if ($count_fat == 1) {
                    $linha_fat = $query_dados_fat->fetch(PDO::FETCH_ASSOC);

                    $num_fatura = $linha_fat['num_fatura'];
                    $nome_cli = $linha_fat['cliente'];
                    $valor_fat = $linha_fat['valor'];
                    $descricao = "Desconto de letra sobre a fatura Nº ". $num_fatura .", do cliente ". $nome_cli ."";
					
					/*
					$query_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_virt, 'CRE', :descricao, :valor, :saldo_controlo, :saldo_contab, :saldo_disp)");
                    $query_mov->execute(array(':id_conta' => $id_conta, ':data_virt' => $data_virt, ':descricao' => $descricao, ':valor' => $valor, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_contab, ':saldo_disp' => $saldo_disp));
                    */
					atualiza_saldo($connection, $id_conta, "CRE", $descricao, "", 0, $valor, $data_virt, $linha_conta['saldo_controlo'], $linha_conta['saldo_contab'], $linha_conta['saldo_disp'], $valor);
                    
                    $query_let = $connection->prepare("INSERT INTO letra (id_conta_empresa, id_fatura, imp_s, com, juro, valor, aceite, pago, data_virt) VALUES (:id_empresa, :id_fatura, :is, :com, :juro, :valor, '1', '1', :data_virt)");
                    $query_let->execute(array(':id_empresa' => $id_conta, ':id_fatura' => $id_fat, ':is' => $is, ':com' => $com, ':juro' => $juro_tot, ':valor' => $valor_letra, ':data_virt' => $data_virt));

                    if ($valor_letra < $valor_fat) {
                        $rest_fat = $valor_fat - $valor_letra;
                        $query_fat = $connection->prepare("UPDATE fatura SET valor = :valor_rest WHERE id_fatura = :id_fat");
                        $query_fat->execute(array(':valor_rest' => $rest_fat, ':id_fat' => $id_fat));
                    }

                    else {
                        $query_fat = $connection->prepare("UPDATE fatura SET pago = 1 WHERE id_fatura = :id_fat");
                        $query_fat->execute(array(':id_fat' => $id_fat));
                    }
                }
                else {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Não foi possivel encontrar a fatura");
                }
            }
			$connection->commit();
        }
        catch (PDOException $e) {
            $connection->rollBack();
            $valido = false;
            $arr = array('sucesso' => false, 'mensagem' => $e->getMessage());
        }
    }
    
    if ($valido == true) {
        $query_movimento_saldo = $connection->prepare("SELECT m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta = 'ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_movimento_saldo->execute(array(':id_empresa' => $id_empresa));
        $linha_movimento_saldo = $query_movimento_saldo->fetch(PDO::FETCH_ASSOC);
        $saldo = $linha_movimento_saldo['saldo_controlo'];
        $query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
        $query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
        $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);
        $arr = array('sucesso' => true, 'saldo' => $saldo, 'moeda' => $linha_moeda['simbolo']);

        if ($_POST["tipocli"] == "interno")
            logClicks($connection, "185");
        else
            logClicks($connection, "186");
    }   
}

elseif ($_POST['tipo'] == "aceita_letra") {
    $dados = json_decode($_POST['dados'], true);
    $update = 0;
    $valido = false;
    
    foreach ($dados as $key => $value) {
        $id_letra = $dados[$key]["id_letra"];
        $aceite = floatval($dados[$key]["aceite"]);
        $query_update_letra = $connection->prepare("UPDATE letra SET aceite = :aceite WHERE id_letra = :id_letra");
        if ($query_update_letra->execute(array(':aceite' => $aceite, ':id_letra' => $id_letra))) {
            $count_update = $query_update_letra->rowCount();
            $update += $count_update;
            $valido = true;
        }
    }
    
    if ($valido == true && $update > 0) {
        $arr = array('sucesso' => true, 'mensagem' => "Alterações efetuadas com sucesso.");
        logClicks($connection, "187");
    } elseif ($valido == true && $update == 0)
        $arr = array('sucesso' => false, 'mensagem' => "Não foi efetuada qualquer alteração.");
    elseif ($valido == false)
        $arr = array('sucesso' => false, 'mensagem' => "Ocorreu um erro. Por favor, tente novamente.");
}

elseif ($_POST['tipo'] == "adiantamento") {
    /* */
	$id_banco = $_POST['id_banco'];
    $id_empresa = $_SESSION['id_empresa'];
    $id_conta = $_POST['id_conta'];
    $id_regra = $_POST['id_regra']; 
    $plafond = floatval(str_replace(",", ".", str_replace(".", "", $_POST['plafond'])));
    $descricao = $_POST['descricao'];
	$valor = floatval(str_replace(",", ".", str_replace(".", "", $_POST['valor'])));
    $simbolo = $_POST['moeda'];
    $adiant_cliente = $_POST['adiantamento'];
    $data_virt = date('Y-m-d H:i:s', strtotime($_POST['data_virt']));
    
    try {
        $connection->beginTransaction();
        
        $query_conta = $connection->prepare("SELECT m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM movimento m WHERE m.id_conta=:id_conta ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_conta' => $id_conta));
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
        
		if ($adiant_cliente == "true") {
            $cliente = $_POST['cliente'];
			$descricao = $descricao." ". $cliente;
			
			$query_adiant = $connection->prepare("INSERT INTO adiantamento (id_empresa, nome_cliente, valor, pago, data_virt) VALUES (:id_empresa, :cliente, :valor, '0', :data_virt)");
			$query_adiant->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':cliente' => $cliente, ':valor' => $valor, ':data_virt' => $data_virt));
		}
		
		$plafond_restante = $plafond - $valor;
		$query_atualiza_plafond_fatura = $connection->prepare("INSERT INTO regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:id_regra, :id_empresa, :data_virt, :valor, :simbolo, :id_banco)");
		$query_atualiza_plafond_fatura->execute(array(':id_regra' => $id_regra, ':id_empresa' => $id_empresa, ':data_virt' => $data_virt, ':valor' => $plafond_restante, ':simbolo' => $simbolo, ':id_banco' => $id_banco));
			
		/*
		$query_insert_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_virt, 'CRE', :descricao, :credito, :saldo_controlo, :saldo_contab, :saldo_disp)");
        $query_insert_mov->execute(array(':id_conta' => $id_conta, ':data_virt' => $data_virt, ':descricao' => $descricao, ':credito' => $valor, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_contab, ':saldo_disp' => $saldo_disp));
        //*/
		atualiza_saldo($connection, $id_conta, "CRE", $descricao, "", 0, $valor, $data_virt, $linha_conta['saldo_controlo'], $linha_conta['saldo_contab'], $linha_conta['saldo_disp'], $valor);
        
		$connection->commit();
        $arr = array('sucesso' => true);
		
		if ($adiant_cliente == "true")
			logClicks($connection, "197");
		else
			logClicks($connection, "196");
    }
    catch (PDOException $e) {
        $connection->rollBack();
        $arr = array('sucesso' => false, 'mensagem' => $e->getMessage());
    }
	/* */
	// $arr = array('sucesso' => false, 'mensagem' => 'Funcionalidade indisponivel de momento');
}

/* elseif ($_POST['tipo'] == "moeda_fornecedor") {
    $query_moeda = $connection->prepare("SELECT m.simbolo, m.ISO4217 FROM fornecedor f INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE f.id=:id_fornecedor LIMIT 1");
    $query_moeda->execute(array(':id_fornecedor' => $_POST['id']));
    $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);
    
    $arr = array('sucesso' => true, 'moeda' => $linha_moeda['simbolo'], 'isomoeda' => $linha_moeda['ISO4217']);
} */

elseif ($_POST['tipo'] == "adiant_fornec") {
    $id_fornecedor = $_POST['id_fornecedor'];
    $valor_ini = floatval(str_replace(",", ".", str_replace(".", "", $_POST['valor'])));
    $data_virt = date('Y-m-d H:i:s', strtotime($_POST['data_virt']));
    
    $query_conta = $connection->prepare("SELECT m.id_conta, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM movimento m INNER JOIN conta c ON m.id_conta=c.id INNER JOIN empresa e ON c.id_empresa=e.id_empresa WHERE c.tipo_conta='ordem' AND e.id_empresa=:id_empresa ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
    $query_conta->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
    
	$xchange_rate = 1;
    if ($_POST['ISO4217'] != $linha_moeda['ISO4217']) {
        $contents = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        foreach ($contents->Cube->Cube->Cube as $rates) {
            if ($rates['currency'] == $linha_ISO_moeda['ISO4217']) {
                $xchange_rate = floatval(1 / $rates['rate']);
                break;
            }
        }
    }	
	$valor = number_format($valor_ini * $xchange_rate, 2, '.', '');
    
    if ($id_fornecedor == 0)
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, escolha um fornecedor");
    elseif ($valor == '' || $valor == 0)
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, defina o valor");
    elseif ($valor > $linha_conta['saldo_disp'])
        $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas não tem saldo suficiente");
    else{
		/*
		$saldo_controlo = $linha_conta['saldo_controlo'] - $valor;
        $saldo_contab = $linha_conta['saldo_contab'] - $valor;
        $saldo_disp = $linha_conta['saldo_disp'] - $valor;
        $query_insert_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_virt, 'DEB', :descricao, :debito, :saldo_controlo, :saldo_contab, :saldo_disp)");
        $query_insert_mov->execute(array(':id_conta' => $linha_conta['id_conta'], ':data_virt' => $data_virt, ':descricao' => "Adiantamento a fornecedor $_POST[fornecedor]", ':debito' => $valor, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_contab, ':saldo_disp' => $saldo_disp));
        */
		atualiza_saldo($connection, $linha_conta['id_conta'], "DEB", "Adiantamento a fornecedor $_POST[fornecedor]", "", $valor, 0, $data_virt, $linha_conta['saldo_controlo'], $linha_conta['saldo_contab'], $linha_conta['saldo_disp'], $valor);
        
        $tx_iva = $_POST['tx_iva'] != 0 ? floatval($_POST['tx_iva']) : null;
        $iva = $_POST['tx_iva'] != 0 ? round($valor * $tx_iva / 100, 2) : null;
        
        $query_adiant = $connection->prepare("INSERT INTO adiantamento (id_empresa, id_fornecedor, tx_iva, iva, valor, pago, data_virt) VALUES (:id_empresa, :id_fornecedor, :tx_iva, :iva, :valor, 0, :data_virt)");
        $query_adiant->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_fornecedor' => $id_fornecedor, ':tx_iva' => $tx_iva, ':iva' => $iva, ':valor' => $valor, ':data_virt' => $data_virt));
        $id_last_adiant = $connection->lastInsertId();
        
        $arr = array('sucesso' => true, 'id_last_adiant' => $id_last_adiant);
		logClicks($connection, "194");
    }
	
} elseif ($_POST['tipo'] == "dados_grafico") {    
    try {
		$connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
		$connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		/* ULTIMA COTAÇÃO DE CADA HORA */
		/* $query_dados_acao = $connection_bd_acao->prepare("SELECT * FROM (SELECT c.last_trade_price, c.date_reg AS data_alteracao FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais WHERE p.id_pais=:id_pais AND b.nome=:nome_bolsa AND a.nome_acao LIKE CONCAT(:nome_acao, '%') ORDER BY data_alteracao ASC) AS Q1 GROUP BY YEAR(data_alteracao) ASC, MONTH(data_alteracao) ASC, DAY(data_alteracao) ASC, HOUR(data_alteracao) ASC");
		$query_dados_acao->execute(array(':id_pais' => $_POST["id_pais"], ':nome_bolsa' => $_POST["nome_bolsa"], ':nome_acao' => $_POST["nome_acao"])); */

		// $query_dados_acao = $connection_bd_acao->prepare("SELECT * FROM (SELECT c.last_trade_price, c.date_reg AS data_alteracao FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais WHERE p.id_pais=:id_pais AND a.nome_acao LIKE CONCAT(:nome_acao, '%') ORDER BY data_alteracao ASC) AS Q1 GROUP BY YEAR(data_alteracao) ASC, MONTH(data_alteracao) ASC, DAY(data_alteracao) ASC, HOUR(data_alteracao) ASC");
//		$query_dados_acao = $connection_bd_acao->prepare("SELECT * FROM (SELECT c.last_trade_price, c.date_reg AS data_alteracao FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais WHERE p.id_pais=:id_pais AND a.nome_acao=:nome_acao ORDER BY data_alteracao DESC) AS Q1 GROUP BY YEAR(data_alteracao) ASC, MONTH(data_alteracao) ASC, DAY(data_alteracao) ASC, HOUR(data_alteracao) ASC");//estava esta(problemas depois de passar mariaDB para mysql)
                $query_dados_acao = $connection_bd_acao->prepare("SELECT * FROM (SELECT c.last_trade_price, c.date_reg AS data_alteracao FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais WHERE p.id_pais=:id_pais AND a.nome_acao=:nome_acao ORDER BY data_alteracao DESC) AS Q1 GROUP BY YEAR(data_alteracao) /*ASC*/, MONTH(data_alteracao) /*ASC*/, DAY(data_alteracao) /*ASC*/, HOUR(data_alteracao) /*ASC*/");//Comentei os ASC dos group by (esta query traz todos os dados de uma determinada acao ESTA QUERY ESTÁ CORRETA)
		$query_dados_acao->execute(array(':id_pais' => $_POST["id_pais"], ':nome_acao' => $_POST["nome_acao"]));
		
		$num_linhas = $query_dados_acao->rowCount();
		if($num_linhas > 0) {
			while($linha = $query_dados_acao->fetch(PDO::FETCH_ASSOC)){
				$arr_dados[] = array('cotacao' => $linha['last_trade_price'], 'data_alteracao' => $linha['data_alteracao']);
			}
			$arr = array('sucesso' => true, 'dados_in' => $arr_dados);
		} else {
			$arr = array('sucesso' => true, 'dados_in' => "Não foi possivel obter dados da ação selecionada");
		}
	} catch (PDOException $e) {
		echo $e->getMessage();
		file_put_contents('PDOErrorsAcao.txt', $e->getMessage(), FILE_APPEND);
		$arr = array('sucesso' => false, 'mensagem' => 'Algo correu mal no agendamento da compra, pedimos desculpas');
	}
} elseif ($_POST['tipo'] == "rem_linha") {
    try {
        $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
        $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query_rem = $connection_bd_acao->prepare("DELETE FROM preco_alvo_empresa WHERE id_preco_alvo=:id");
        $query_rem->execute(array(':id' => $_POST['id_pae']));
        
        $query_trans_agend = $connection_bd_acao->prepare("SELECT pae.id_preco_alvo, a.nome_acao, p.nome_pais, b.nome, pae.qtd, pae.preco_alvo, m.simbolo, pae.tipo, pae.data_limite_virtual FROM preco_alvo_empresa pae INNER JOIN acao a ON pae.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id_moeda WHERE pae.id_empresa=:id_empresa AND pae.tipo=:tipo_trans AND pae.active='1' AND (pae.data_limite_real>NOW() OR DATE(pae.data_limite_real)=CURDATE()) ORDER BY pae.tipo ASC, p.nome_pais ASC, a.nome_acao ASC");
        $query_trans_agend->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':tipo_trans' => $_POST['tipo_trans']));
        $num_linhas_trans = $query_trans_agend->rowCount();
        if ($num_linhas_trans == 0) {
            $arr = array('sucesso' => true, 'vazio' => true);
        } else {
            while ($trans_agend = $query_trans_agend->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id_preco_alvo' => $trans_agend['id_preco_alvo'], 'nome_acao' => $trans_agend['nome_acao'], 'nome_pais' => $trans_agend['nome_pais'], 'nome_bolsa' => $trans_agend['nome'], 'qtd' => $trans_agend['qtd'], 'preco_alvo' => $trans_agend['preco_alvo'], 'simbolo' => $trans_agend['simbolo'], 'data_limite' => date("d-m-Y", strtotime($trans_agend['data_limite_virtual'])));
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
        }
        logClicks($connection, "122");
        
    } catch (PDOException $e) {
        echo $e->getMessage();
        file_put_contents('PDOErrorsAcao.txt', $e->getMessage(), FILE_APPEND);
        $arr = array('sucesso' => false, 'mensagem' => 'Algo correu, pedimos desculpas');
    }
}

elseif ($_POST['tipo'] == "chk_plfnd_adiant") {
	$id_empresa = $_SESSION['id_empresa'];
	$limt_adiant = $_POST['limt_adiant'];
	
	if ($_POST['tipo_adiant'] == 'cli') {
		$query_plafond_aum = $connection->prepare("SELECT r.id_regra, re.valor FROM regra_empresa re INNER JOIN empresa e ON re.id_empresa=e.id_empresa INNER JOIN regra r ON re.id_regra=r.id_regra WHERE e.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY re.date_reg DESC LIMIT 1");
		$query_plafond_aum->execute(array(':id_empresa' => $id_empresa, ':nome_regra' => 'Plafond (faturas)'));
	}
	else {
		$query_plafond_aum = $connection->prepare("SELECT r.id_regra, re.valor FROM regra_empresa re INNER JOIN empresa e ON re.id_empresa=e.id_empresa INNER JOIN regra r ON re.id_regra=r.id_regra WHERE e.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY re.date_reg DESC LIMIT 1");
		$query_plafond_aum->execute(array(':id_empresa' => $id_empresa, ':nome_regra' => 'Plafond de aumento de capital'));
	}
	
	$linha_plafond = $query_plafond_aum->fetch(PDO::FETCH_ASSOC);
	$plafond = $linha_plafond['valor'];
	$max_aum = $plafond * $limt_adiant / 100;
	
	if ($max_aum > 0)
		$arr = array('sucesso' => true, 'id_regra' => $linha_plafond['id_regra'], 'plafond' => $plafond, 'max_aum' => $max_aum);
	else
		$arr = array('sucesso' => false, 'mensagem' => 'Já esgotou o seu plafond');
}
//Alertas de acoes 
elseif ($_POST['tipo'] == "acoes_alerta") {
//	$id_acao = $_POST['id_acao'];
        $id_acao_trans= $_POST['id_acao_trans'];
        $utilizador = $_SESSION['id_utilizador'];
	$nome = $_POST['nome'];
        $preco_compra=$_POST['precoCompra'];
        $quantidade=$_POST['quantidade'];
        $preco_atual=$_POST['precoAtual'];

        $query_add_alert = $connection->prepare("INSERT INTO alerta (id_utilizador,id_acao_trans,nome,preco_compra,quantidade,preco_atual) VALUES (:utilizador,:id_acao_trans,:nome,:preco_compra,:quantidade,:preco_atual)");
            $query_add_alert->execute(array(':utilizador' => $utilizador,':id_acao_trans'=>$id_acao_trans, ':nome' => $nome,':preco_compra' => $preco_compra,':quantidade' => $quantidade,':preco_atual' => $preco_atual));
		//$arr = array('sucesso' => true, 'mensagem' => 'Já esgotou o seu plafond');
}

/* Para periodo de atualizações
elseif ($_POST['tipo'] == "em_update") {
    if ($_SESSION['id_utilizador'] == 177 || $_SESSION['id_utilizador'] == 100)
        $arr = array('permitido' => true);
    else
        $arr = array('permitido' => false);
}
/* */

$connection = null;
$connection_bd_acao = null;
echo json_encode($arr);
