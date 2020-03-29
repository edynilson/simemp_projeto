<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-05 11:12:34
*/

include('./conf/check.php');
include_once('./conf/common.php');

use phpFastCache\CacheManager;
include_once('./phpfastcache/src/autoload.php');
CacheManager::setDefaultConfig([
  'path' => '/tmp',
  'securityKey' => 'SimEmp'
]);

$query_dados_empresa = $connection->prepare("SELECT emp.niss, emp.nipc, emp.nome, te.tipo, a.designacao, emp.morada, emp.cod_postal, emp.localidade, emp.pais, emp.email, cs.capital_social_monetario, cs.capital_social_especie FROM empresa emp INNER JOIN atividade a ON emp.`atividade`=a.id INNER JOIN tipo_empresa te ON emp.tipo=te.id INNER JOIN capital_social cs ON emp.tipo=te.id AND emp.id_empresa=cs.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id LIMIT 1");
$query_dados_empresa->execute(array(':id' => $_SESSION['id_empresa']));
$linha_dados = $query_dados_empresa->fetch(PDO::FETCH_ASSOC);

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_email_recebido = $connection->prepare("SELECT co.id FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.para=:para AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa");
$query_email_recebido->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':para' => $_SESSION['id_empresa'], ':lixo' => 0, ':elim' => 0));
$num_emails_receb = $query_email_recebido->rowCount();

$query_email_enviado = $connection->prepare("SELECT co.id FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.de=:de AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa");
$query_email_enviado->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':de' => $_SESSION['id_empresa'], ':lixo' => 0, ':elim' => 0));
$num_emails_env = $query_email_enviado->rowCount();

$query_email_novo = $connection->prepare("SELECT co.id FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.para=:para AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa AND co.lido=:lido");
$query_email_novo->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':para' => $_SESSION['id_empresa'], ':lixo' => 0, ':elim' => 0, ':lido' => 0));
$num_emails_nov = $query_email_novo->rowCount();

function carrega_img($connection, $tipo_img) {
    $query_img_ent = $connection->prepare("SELECT e.nome AS nome_ent, ei.localizacao, ei.hiperligacao FROM utilizador u INNER JOIN entidade e ON u.id_entidade=e.id INNER JOIN entidade_img ei ON e.id=ei.id_entidade WHERE u.id=:id_utilizador AND ei.nome=:tipo_img LIMIT 1");
    $query_img_ent->execute(array(':id_utilizador' => $_SESSION['id_utilizador'], ':tipo_img' => $tipo_img));
    $linha_img = $query_img_ent->fetch(PDO::FETCH_ASSOC);
    return $linha_img;
}

function draw_chart_data($connection, $id_empresa) {
	//$pwd_se_acoes = 'T4h6m3YuniurhCDfHGE9VYBQmQMszt8x'; //pass do site
        $pwd_se_acoes = '';
	
    //-- Carregar saldo da empresa
    $query_movimento_saldo = $connection->prepare("SELECT m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id = m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
    $query_movimento_saldo->execute(array(':id_empresa' => $id_empresa));
    $linha_movimento_saldo = $query_movimento_saldo->fetch(PDO::FETCH_ASSOC);
    
    //-- Carregar moeda da empresa
    $query_moeda = $connection->prepare("SELECT mo.simbolo, mo.ISO4217 FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id_empresa=:id_empresa LIMIT 1");
    $query_moeda->execute(array(':id_empresa' => $id_empresa));
    $linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);
    
    //-- Carregar todas as AÇÕES na carteira da EMPRESA, e respetivas 
    $query_titulos = $connection->prepare("SELECT ac.id, /* p.id_pais, p.nome_abrev, p.nome_abrev AS nome_pais, p.nome_bolsa, */ ac.nome AS nome_acao, compras.preco, IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade) AS quantidade, m.ISO4217 FROM (SELECT ac.id, ac.nome, act.preco, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_c AND act.tipo='C' GROUP BY id) AS compras LEFT JOIN (SELECT ac.id, ac.nome, SUM(quantidade) AS quantidade FROM acao ac INNER JOIN acao_trans act ON ac.id=act.id_acao INNER JOIN empresa emp ON emp.id_empresa=act.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa_v AND act.tipo='V' GROUP BY id) AS vendas ON compras.id=vendas.id INNER JOIN acao ac ON ac.id=compras.id OR ac.id=vendas.id INNER JOIN pais p ON ac.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE IF (vendas.quantidade IS NULL, compras.quantidade, compras.quantidade-vendas.quantidade)<>0 ORDER BY p.nome_pais ASC");
    $query_titulos->execute(array(':id_empresa_c' => $id_empresa, ':id_empresa_v' => $id_empresa));
    
    //-- Carregar taxas de cambio
    $contents = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
    
    //-- Obter dados da cache
    $cache = CacheManager::getInstance('files');
    $key = 'cotacao_acoes';
    $CachedString = $cache->getItem($key);
    $dados_cache = $CachedString->get();
    
    //-- Caso cache estiver vazia, carregar dados da BD
    if (!$dados_cache || $dados_cache == null || $dados_cache == '') {
        $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
        $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//        $query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE a.active='1' ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT c.id_acao, c.last_trade_price, c.`change`, c.`open`, c.days_high, c.days_low, c.last_trade_date FROM cotacao c ORDER BY c.id_acao ASC, c.date_reg DESC) AS cotacoes ON acoes.id_acao=cotacoes.id_acao GROUP BY cotacoes.id_acao ASC ORDER BY acoes.nome_acao ASC"); // estava esta (problemas ao passar do mariaDB para o mysql)
        $query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE a.active='1' ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT last_cotacao.id_acao, last_cotacao.last_trade_price, last_cotacao.`change`, last_cotacao.`open`, last_cotacao.days_high, last_cotacao.days_low,last_cotacao.last_trade_date FROM cotacao last_cotacao JOIN  (SELECT id_acao, MAX(date_reg) AS max_date FROM cotacao GROUP BY id_acao )  c1 ON c1.id_acao=last_cotacao.id_acao AND c1.max_date=last_cotacao.date_reg) AS cotacoes ON acoes.id_acao=cotacoes.id_acao GROUP BY cotacoes.id_acao /*ASC*/ ORDER BY acoes.nome_acao ASC"); //meti esta
        $query_cotacao->execute();
        
        $dados_cache = [];
        while ($linha_cotacao = $query_cotacao->fetch(PDO::FETCH_ASSOC)) {
            $dados_cache[] = array('nome_acao' => $linha_cotacao['nome_acao'], 'last_trade_price' => $linha_cotacao['last_trade_price']);
        }
    }
    
    $resultado = 0;
    //-- Percorrer array de cotações e calcular "ganho/perda"
    while ($reg = $query_titulos->fetch(PDO::FETCH_ASSOC)) {
        //-- Percorrer cache e obter ultima cotação de ação indicada
        foreach ($dados_cache AS $value) {
            if ($reg['nome_acao'] == $value['nome_acao']) {
                $xchange_rate = 1;
                if ($reg['ISO4217'] != $linha_moeda['ISO4217']) {
                    foreach ($contents->Cube->Cube->Cube as $rates) {
                        if ($rates['currency'] == $reg['ISO4217']) {
                            $xchange_rate = 1 / floatval($rates['rate']);
                            break;
                        }
                    }
                }

                $resultado += ($value['last_trade_price']*$reg['quantidade'] - $reg['preco']*$reg['quantidade']) * $xchange_rate;
            }
        }
    }
    
    CacheManager::clearInstances();
    $arr = ['sucesso' => true, 'saldo_val' => $linha_movimento_saldo['saldo_disp'], 'valor' => $resultado];
    return json_encode($arr);
}

$dash_data = json_decode(draw_chart_data($connection, $_SESSION['id_empresa']));
$dash_balnc_val = $dash_data->saldo_val;
$dash_gains_val = $dash_data->valor;

$datetime = new DateTime();
?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0">
        <title>SimEmp <?php echo $datetime->format('Y'); ?></title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/jquery.multilevelpushmenu.css">
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700&subset=latin,cyrillic-ext,latin-ext,cyrillic">
        <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="css/calend.css">
        <link rel="stylesheet" href="fullcalendar_1.6.4_yearview/fullcalendar.css">
        <link rel="stylesheet" href="fullcalendar_1.6.4_yearview/fullcalendar.print.css" media="print">
		<!-- DatetimePicker (Added 11-04-2016) --> <link rel="stylesheet" href="css/jquery.datetimepicker.2016.css">
        <link rel="icon" href="favicon.ico">

        <!-- <script src="http://modernizr.com/downloads/modernizr-latest.js"></script> -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="js/jquery.multilevelpushmenu.min.js"></script>
		<!-- DatetimePicker (Added 11-04-2016) --> <script src="js/jquery.datetimepicker.2016.min.js"></script>
        <script src="js/functions.js"></script>
        <script src="fullcalendar_1.6.4_yearview/fullcalendar.min.js"></script>
        <script src="js/jquery.quicksearch.js"></script>
        <script src="js/jquery.windowmsg-1.0.js"></script>
        <script src="ckeditor/ckeditor.js"></script>
        <script src="ckeditor/adapters/jquery.js"></script>
        <script src="js/jquery.inputmask-3.x/jquery.inputmask.js"></script>
        <script src="js/jquery.inputmask-3.x/jquery.inputmask.numeric.extensions.js"></script>
		<!-- Google dashboard library (Added 22-02-2018) -->
        <script src="http://www.gstatic.com/charts/loader.js"></script>
        <style>
            /* for Labels */
            svg:first-child > g > text[text-anchor="middle"] {
                font-size: 8pt;
            }
            
            /* for indicators text * /
            svg g text {
                font-size: 18px;
            }
            
            /* for middle text */
            svg g g text {
                font-size: 8pt;
            }
        </style>
        <!-- -->
        <script src="js/user.js"></script>
        <script src="js/functions/funcoesMail.js"></script>
    </head>
    <body id="normal">
        <!--<div id="header">
            <a href="?lingua=pt" id="pt"><img src="images/pt.gif" alt="" width="35" height="25"></a>
            <a href="?lingua=en" id="en"><img src="images/en.jpg" alt="" width="35" height="25"></a>
        </div>-->
        <div class="content">
            <header>
                <div id="header_1">
                    <section class="left-column">
                        <div id="panel_home" class="panel">
                            <a href="pag_user.php">
                                <div class="icon-home"></div>
                            </a>
                        </div>
                    </section>
                    <section class="center-column">
                        <div class="esq40" style="margin-top: 1.5%; margin-bottom: 1.5%;">
                            <img id="logo_simemp" src="images/logo_med.png" style="margin-top: 0;">
                        </div>
                        <div class="dir60" style="margin-top: 1.5%; margin-bottom: 1.5%;">
                            <label id="txtDataVirtual" class="labelNormal" style="float: none; font-family: helvetica-light; font-size: 100%; color: #fff; font-weight: bold;"></label>
                        </div>
                    </section>
                    <section class="right-column">
                        <div id="panel_logout" class="panel">
                            <a href='terminar_sessao.php'><div class="icon-off"></div></a>
                        </div>
                    </section>
                </div>
            </header>
            <section id="conteudo">
                <div id="pag_raw">
                    <div id="div_esq_cal">
                        <div id="calend_in">
                            <!-- -->
                            <div id="dashboards" style="float: left; width: 200px; padding: 50px 0 0 20px;">
                                <input id="dash_balnc_val" value="<?php echo $dash_balnc_val ?>" hidden>
                                <input id="dash_gains_val" value="<?php echo $dash_gains_val ?>" hidden>
                                <div id="dash_balnc"></div>
                                <div id="dash_gains"></div>
                            </div>
                            <!-- -->
                            <div id="calend_inner" style="margin-left: 220px;">
                                <div id="calendario_inicial"></div>
                            </div>
                        </div>
                        <div id="divDadosEmpresa" name="divDadosEmpresa" style="font-size: 85%;">
                            <div id="dados_emp_inner">
                                <div class="linha10" style="margin-bottom: 5px; position: relative; margin-top: 15px;">
                                    <div class="esq30" style="height: 20px; position: absolute; bottom: 0; margin-bottom: 0; left: 0;">
                                        <label for="txtNome" class="labelNormal" style="height: 20px; line-height: 1.5em;">Empresa</label>
                                    </div>
                                    <div class="dir70" style="height: 20px; width: 40%; position: absolute; bottom: 0; margin-bottom: 0; left: 30%;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtNome" readonly="readonly" value="<?php echo $linha_dados['nome']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                    <button id="btnCalend" name="btnCalend" class="btnNoIco" style="float: right; margin-right: 5%;">Voltar</button>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtNipc" class="labelNormal" style="height: 20px; line-height: 1.5em;">NIPC</label>
                                    </div>
                                    <div class="dir70" style="height: 20px; width: 40%">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtNipc" readonly="readonly" value="<?php echo $linha_dados['nipc']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtNiss" class="labelNormal" style="height: 20px; line-height: 1.5em;">NISS</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtNiss" readonly="readonly" value="<?php echo $linha_dados['niss']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtTipo" class="labelNormal" style="height: 20px; line-height: 1.5em;">Tipo</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtTipo" readonly="readonly" value="<?php echo $linha_dados['tipo']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtDesig" class="labelNormal" style="height: 20px; line-height: 1.5em;">Atividade</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_assunto" style="height: 20px;">
                                            <input type="text" name="txtDesig" readonly="readonly" value="<?php echo $linha_dados['designacao']; ?>" style="line-height: 1em; margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtMorada" class="labelNormal" style="height: 20px; line-height: 1.5em;">Morada</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_assunto" style="height: 20px;">
                                            <input type="text" name="txtMorada" readonly="readonly" value="<?php echo $linha_dados['morada']; ?>" style="line-height: 1em; margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtCodPostal" class="labelNormal" style="height: 20px; line-height: 1.5em;">Código postal</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtCodPostal" readonly="readonly" value="<?php echo $linha_dados['cod_postal']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtLocalidade" class="labelNormal" style="height: 20px; line-height: 1.5em;">Localidade</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtLocalidade" readonly="readonly" value="<?php echo $linha_dados['localidade']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtPais" class="labelNormal" style="height: 20px; line-height: 1.5em;">País</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtPais" readonly="readonly" value="<?php echo $linha_dados['pais']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtEmail" class="labelNormal" style="height: 20px; line-height: 1.5em;">Correio eletrónico</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtEmail" readonly="readonly" value="<?php echo $linha_dados['email']; ?>" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtCapSocMon" class="labelNormal" style="height: 20px; line-height: 1.5em;">Capital social monetário</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="moneyarea_col1" style="height: 20px;">
                                            <input type="text" name="txtCapSocMon" readonly="readonly" value="<?php echo number_format($linha_dados['capital_social_monetario'], 2, ',', '.'); ?>" style="margin-top: 0; font-size: 95%;">
                                            <div class="mnyLabel">
                                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtCapSocEsp" class="labelNormal" style="height: 20px; line-height: 1.5em;">Capital social espécie</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="moneyarea_col1" style="height: 20px;">
                                            <input type="text" name="txtCapSocEsp" readonly="readonly" value="<?php echo number_format($linha_dados['capital_social_especie'], 2, ',', '.'); ?>" style="margin-top: 0; font-size: 95%;">
                                            <div class="mnyLabel">
                                                <input name="txtMoeda" type="text" readonly="readonly" value="<?php echo $linha_moeda['simbolo'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
						
						<!-- <div> q exibe tarefas a serem realizadas na data indicada -->
                        <!-- Styles copiados de "styles.css" / "#divDadosEmpresa" -->
                        <div id="divDateTasks" name="divDateTasks" style="font-size: 85%;float: left;width: 98%;height: 440px;background-color: #fff;">
                            <div id="dados_tasks_inner" style="margin: 0 auto;width: 85%;">
                                <div class="linha10" style="margin-bottom: 5px; position: relative; margin-top: 15px;">
<!--                                    Comentei os campos todos das relacionados com as tarefas menos o campo "tarefas"-->
<!--                                    <div class="esq30" style="height: 20px; position: absolute; bottom: 0; margin-bottom: 0; left: 0;">
                                        <label for="txtDataRealTask" class="labelNormal" style="height: 20px; line-height: 1.5em;">Data real</label>
                                    </div>
                                    <div class="dir70" style="height: 20px; width: 40%; position: absolute; bottom: 0; margin-bottom: 0; left: 30%;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" id="txtDataRealTask" readonly="readonly" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>-->
                                    <button id="btnCalend" name="btnCalend" class="btnNoIco" style="float: right; margin-right: 5%;">Voltar</button>
                                </div>
<!--                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtDataVirtTask" class="labelNormal" style="height: 20px; line-height: 1.5em;">Data virtual</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" id="txtDataVirtTask" readonly="readonly" style="margin-top: 0; color: #757575;">
                                        </div>
                                    </div>
                                </div>
                                <div class="linha10" style="margin-bottom: 5px;">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtGrupoTask" class="labelNormal" style="height: 20px; line-height: 1.5em;">Grupo</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <div class="inputarea_col1" style="height: 20px;">
                                            <input type="text" name="txtGrupoTask" readonly="readonly" value="<?php // echo $linha_dados['nome_grupo']; ?>" style="margin-top: 0; color: #757575;"> estava esta depois meter esta para funcionar com nome do grupo
                                            <input type="text" name="txtGrupoTask" readonly="readonly" value="<?php // echo 'em testes...'; ?>" style="margin-top: 0; color: #757575;"> meti esta temporariamente
                                        </div>
                                    </div>
                                </div>-->
                                <div class="linha">
                                    <div class="esq30" style="height: 20px;">
                                        <label for="txtTasks" class="labelNormal" style="height: 20px; line-height: 1.5em;">Tarefas</label>
                                    </div>
                                    <div class="dir70" style="height: 20px;">
                                        <!-- <div class="inputarea_col1" style="height: 20px;"> -->
                                            <textarea id="txtTasks" readonly="readonly" name="txtTasks" rows="20" class="caixaTextoNormal" style="width: 100%"></textarea>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Fim de <div> "tarefas" -->
                    </div>
                    <div id="div_dir_cal">
                        <div id="top_div">
                            <div id="dados_emp">
                                <div class="linha" style="margin-bottom: 10px;">
                                    <h3 style="font-size: 1.5em; font-weight: bold;">Dados da empresa</h3>
                                </div>
                                <div class="linha" style="margin-bottom: 2px;">
                                    <p style="margin: 0; font-size: 85%;"><?php echo $linha_dados['nome']; ?></p>
                                    <p style="margin: 0; font-size: 80%; height: 21px;"><?php echo $linha_dados['morada']; ?> - <?php echo $linha_dados['cod_postal']; ?></p>
                                    <p style="margin: 0; font-size: 80%;">NIPC: <?php echo $linha_dados['nipc']; ?> | NISS: <?php echo $linha_dados['niss']; ?></p>
                                </div>
                                <div class="linha" style="margin: 0; text-align: right;">
                                    <p style="margin: 0; font-size: 80%;"><label id="lblVerMais" style="cursor: pointer;">Ver mais [+]</label></p>
                                </div>
                            </div>
                        </div>
                        <div id="center_div">
                            <div id="dados_mails">
                                <div class="linha" style="margin-bottom: 10px;">
                                    <h3 style="font-size: 1.5em; font-weight: bold;">Último correio</h3>
                                </div>
                                <div class="linha" style="margin-bottom: 2px;">
                                    <div class="linha" style="margin-bottom: 5px;">
                                        <a href="#" onclick="carregaMail();" style="text-decoration: none; color: black;"><span style="cursor: pointer;">Novas mensagens recebidas (<?php echo $num_emails_nov; ?>)</span></a>
                                    </div>
                                    <div class="linha" style="margin-bottom: 5px;">
                                        <a href="#" onclick="carregaMail();" style="text-decoration: none; color: black;"><span style="cursor: pointer;">Total mensagens recebidas (<?php echo $num_emails_receb; ?>)</span></a>
                                    </div>
                                    <div class="linha" style="margin-bottom: 16px;">
                                        <a href="#" onclick="carregaMail();" style="text-decoration: none; color: black;"><span style="cursor: pointer;">Total mensagens enviadas (<?php echo $num_emails_env; ?>)</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="bottom_div">
                            <?php $home = carrega_img($connection, "home"); ?>
                            <div id="div_in_esq">
                                <!-- IPB para ativar ligação -->
								<span class="helper"></span><?php if ($home['nome_ent'] == "GuacUnable" && $_SESSION['ldap'] == "1") { ?><a href="<?php echo $home['hiperligacao'];?>" target='_blank'><img src="<?php echo $home['localizacao'];?>" style="width: 100%;"></a><?php } else { ?> <!-- <a href="<?php echo $home['hiperligacao'];?>" target='_blank'> --> <img src="<?php echo $home['localizacao'];?>" style="width: 97%;"> <!-- </a> --> <?php } ?>
							</div>
                            
                            <?php $virtual = carrega_img($connection, "other"); ?>
                            <div id="div_in_dir">
                                <span class="helper"></span><a href="<?php echo $virtual['hiperligacao'];?>" target="_blank"><img src="<?php echo $virtual['localizacao'];?>" style="width: 100%;"></a>
                            </div>
                        </div>
                    </div>
                    <div id="div_fundo_cal">
                        <div id="div_patrocinios">
                            <div class="linha" style="margin-bottom: 10px; width: 10%; padding-left: 2%; padding-top: 1%;">
                                <h3 style="font-size: 1.5em; font-weight: bold;"><i>Apoios:</i></h3>
                            </div>
                            <div id="logos_apoios" style="float: left; width: 88%;">
                                <div class="left-column">
                                    <div class="apoio">
                                        <span class="helper"></span><a href="http://www.ipb.pt/" target="_blank"><img src="images/logo_ipb.png" style="width: 100%;"></a>
                                    </div>
                                </div>
                                <div class="center-column">
                                    <div class="apoio">
                                        <span class="helper"></span><a href="http://www.creditoagricola.pt/CAI/" target="_blank"><img src="imagens_banco/logo_ca_print.jpg" style="width: 100%;"></a>
                                    </div>
                                </div>
                                <div class="right-column">
                                    <div class="apoio">
                                        <span class="helper"></span><img src="images/logo_transtec.png" style="height: 80%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="var_content"></div>
            </section>
        </div>
        <div id="menu">
            <nav>
                <h2><i class="fa fa-reorder"></i>Menu</h2>
                <ul>
                    <li>
                        <a href="#"><?php echo $lingua['ORDER']; ?></a>
                        <h2><?php echo $lingua['ORDER']; ?></h2>
                        <ul>
                            <li>
                                <a href="#"><?php echo $lingua['ORDERS']; ?></a>
                                <h2><?php echo $lingua['ORDERS']; ?></h2>
                                <ul>
								
								<?php // if ($_SESSION['id_utilizador'] == "3" || $_SESSION['id_utilizador'] == "100" || $_SESSION['id_utilizador'] == "115") { ?>
									<li><a href="pag_encprod_estr.php"><?php echo $lingua['M_ORDER']; ?></a></li>
								<?php // } ?>
								
                                    <!-- <li><a href="pag_encprod.php"><?php echo $lingua['M_ORDER']; ?></a></li> -->
                                    <li><a href="ver_encomendas"><?php echo $lingua['V_ORDER']; ?></a></li>
									
									<li><a href="ver_desconto"><?php echo "Consultar descontos"; //$lingua['V_ORDER']; ?></a></li>
                                </ul>
                            </li>
                            
                            <?php // if ($_SESSION['id_utilizador'] == "3" || $_SESSION['id_utilizador'] == "100" || $_SESSION['id_utilizador'] == "115") { ?>
                                <li>
                                    <a href="#"><?php echo $lingua['C_NOTE']; ?></a>
                                    <h2><?php echo $lingua['C_NOTE']; ?></h2>
                                    <ul>
                                        <li><a href="nota_credito"><?php echo $lingua['M_C_NOTE']; ?></a></li>
                                        <li><a href="ver_notas_credito"><?php echo $lingua['V_C_NOTE']; ?></a></li>
                                    </ul>
                                </li>
                            <?php // } ?>
                            
							<li>
                                <a href="#"><?php echo $lingua['ADVANCE']; echo " "; echo $lingua['TO']; echo " "; echo $lingua['SUPPLIER']; ?></a>
                                <h2><?php echo $lingua['ADVANCE']; echo " "; echo $lingua['TO']; echo " "; echo $lingua['SUPPLIER']; ?></h2>
                                <ul>
                                    <li><a href="adiant_fornec"><?php echo $lingua['MAKE']; echo " "; echo lcfirst($lingua['ADVANCE']); ?></a></li>
                                    <li><a href="adiant_efet"><?php echo $lingua['ADVANCE']; echo " "; echo $lingua['MADE']; ?></a></li>
                                </ul>
                            </li>
							
                            <li><a href="ins_fat"><?php echo $lingua['BILL']; ?></a></li>
                            <!-- <li>
                                <a href="#"><?php echo $lingua['O_OPERA']; ?></a>
                                <h2><?php echo $lingua['O_OPERA']; ?></h2>
                                <ul>
                                    <li><a href="#"><?php echo $lingua['ADVANCE']; ?></a></li>
                                </ul>
                            </li> -->
                        </ul>
                    </li>
                    <li>
                        <a href="#"><?php echo $lingua['FINANCIAL_CORE']; ?></a>
                        <h2><?php echo $lingua['FINANCIAL_CORE']; ?></h2>
                        <ul>
                            <li><a href="pag_banco.php"><?php echo $lingua['BANK']; ?></a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><?php echo $lingua['PUBLIC_CENTRAL']; ?></a>
                        <h2><?php echo $lingua['PUBLIC_CENTRAL']; ?></h2>
                        <ul>
                            <li><a href="ver_entregas"><?php echo $lingua['V_DELIVERY']; ?></a></li>
                            <li><a href="f_entregas"><?php echo $lingua['DELIVERY']; ?></a></li>
							<li><a href="down_docs">Documentos para baixar</a></li>
                        </ul>
                    </li>
                    <li><a href="calend"><?php echo $lingua['CALENDAR']; ?></a></li>
                    <li><a href="email"><?php echo $lingua['EMAIL']; ?></a></li>
                    <?php if ($_SESSION['ldap'] == "0") { ?>
                        <li><a href="mod_pass"><?php echo $lingua['CH_PASS'] ?></a></li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
    </body>
</html>